<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Support\Facades\Http;
class OnlineUsers
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user)
    {

        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

}
