<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;
use App\Models\UserLog;

class LogAuthenticationActivity
{
    public function handle($event)
    {
        $user = $event->user ?? null;
        $eventName = $event instanceof Login ? 'login' : ($event instanceof Logout ? 'logout' : 'auth');

        UserLog::create([
            'user_id' => $user->id ?? null,
            'event' => $eventName,
            'ip_address' => Request::ip(),
            'meta' => ['guard' => $event->guard ?? null],
        ]);
    }
}
