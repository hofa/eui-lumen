<?php

namespace App\Events;

use App\Models\User;

class LoginEvent extends Event
{
    protected $user;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
