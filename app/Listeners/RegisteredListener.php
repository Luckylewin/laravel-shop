<?php

namespace App\Listeners;

use App\Notifications\EmailVerifyNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegisteredListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        // 获取到刚刚注册的用户
        $user = $event->user;

        // 调用 notify 发送通知
        $user->notify(new EmailVerifyNotification());
    }
}
