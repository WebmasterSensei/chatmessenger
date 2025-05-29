<?php

namespace App\Http\Controllers;

use App\Events\ChatEvent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessengerController extends Controller
{
    //
    public function index()
    {
        return inertia('Dashboard');
    }

    public function getUsers(Request $request)
    {
        $users = User::all(); // get all users
        $onlineCollected = collect($request->data);

        $users->transform(function ($user) use ($onlineCollected, $request) {

            $mess = Message::where(function ($query) use ($request, $user) {
                $query->where('sender_id', $request->user()->id)
                    ->where('recipient', $user->id);
            })->orWhere(function ($query) use ($request, $user) {
                $query->where('sender_id', $user->id)
                    ->where('recipient', $request->user()->id);
            })->orderByDesc('id')->first();

            $isOnline = $onlineCollected->where('id', $user->id)->isNotEmpty();
            $user->lastMessage =  $mess->message ?? '';
            $user->online = $isOnline;
            return $user;
        });

        return response()->json([
            'data' => $users,
        ]);
    }

    public function getMessage(Request $request)
    {
        $message =  Message::where([
            'sender_id' => $request->id,
            'recipient' => $request->user()->id,
            'seen' => 0,
            'status' => 0,
        ])->update([
            'seen' => 1,
            'status' => 1,
        ]);

        $user = User::select('id', 'name', 'email')->where('id', $request->id)->first();

        $result = Message::where(function ($query) use ($request) {
            $query->where('sender_id', $request->user()->id)
                ->where('recipient', intval($request->id));
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', intval($request->id))
                ->where('recipient', $request->user()->id);
        })->get();

        $latestMessage = $result->sortByDesc('created_at')->first();

        $result->transform(function ($item) use ($request, $latestMessage) {

            if ($item->id === $latestMessage->id) {
                $item->isSeen = $item->seen;
            }

            return $item;
        });
        if ($message) {
            broadcast(new ChatEvent($request->id, $result->where('sender_id', $request->id)->sortByDesc('id')->first()))->toOthers();
        }
        return response()->json([
            'data' => $result,
            'user' => $user,
        ]);
    }
    public function sendMessage(Request $request)
    {
        $sent = Message::create([
            'recipient' => $request->id,
            'sender_id' => $request->user()->id,
            'message' => $request->message,
            'attachment' => '',
            'seen' => 0,
            'status' => 0,
        ]);

        $sent->sent = 1;

        broadcast(new ChatEvent($request->id, $sent))->toOthers();

        return response()->json([
            'data' => $sent,
        ]);
    }
}
