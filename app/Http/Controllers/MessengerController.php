<?php

namespace App\Http\Controllers;

use App\Events\ChatEvent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessengerController extends Controller
{
    //
    public function index()
    {
        return inertia('Dashboard');
    }

    public function getUsers(Request $request)
    {
        $users = User::all();

        $users = $users->map(function ($user) use ($request) {
            $lastMessage = Message::where(function ($query) use ($request, $user) {
                $query->where('sender_id', $request->user()->id)
                    ->where('recipient', $user->id);
            })->orWhere(function ($query) use ($request, $user) {
                $query->where('sender_id', $user->id)
                    ->where('recipient', $request->user()->id);
            })
                ->orderByDesc('id')
                ->first();

            $user->countMess = Message::where('sender_id', $user->id)
                ->where('recipient', $request->user()->id)
                ->where('seen', 0)
                ->count();

            $user->lastMessage = $lastMessage->message ?? '';
            $user->lastMessageTime = $lastMessage->created_at ?? null;

            return $user;
        });

        // Sort users by the timestamp of their last message, latest first
        $users = $users->sortByDesc('lastMessageTime')->values();

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

        $user = User::select('id', 'name', 'email', 'gender', 'image')->where('id', $request->id)->first();

        if ($user) {
            $user->isOnline = $request->online;
        }

        $result = Message::where(function ($query) use ($request) {
            $query->where('sender_id', $request->user()->id)
                ->where('recipient', intval($request->id));
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', intval($request->id))
                ->where('recipient', $request->user()->id);
        })->get();

        $latestMessage = $result->sortByDesc('created_at')->first();

        $result->transform(function ($item) use ($request, $latestMessage) {

            // dd($item->toArray());

            $item->avatar =  User::where('id', $item->sender_id)->value('image') ?? null;

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
        $path = '';
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $path = $file->store('uploads', 'public');
        }
        $sent = Message::create([
            'recipient' => $request->id,
            'sender_id' => $request->user()->id,
            'message' => !$path ? $request->message : 'sent a photo',
            'attachment' => $path,
            'seen' => 0,
            'status' => 0,
        ]);

        $sent->sent = 1;

        broadcast(new ChatEvent($request->id, $sent))->toOthers();

        return response()->json([
            'data' => $sent,
        ]);
    }

    public function searchUser(Request $request)
    {
        $user = User::whereAny(['name', 'email'], 'like', '%' . $request->search . '%')->get();
        return response()->json(['data' => $user]);
    }

    public function uploadImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('avatars', 'public');
            User::where('id', $request->id)->update([
                'image' => $path
            ]);
        }
        return redirect()->back();
    }
}
