<?php

namespace App\Listeners;

use App\Events\UserLoginEvent;
use App\Models\LoginLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserLogListener implements ShouldQueue
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

//    public $queue = 'login_log';
//    public $delay = 2;

    /**
     * Handle the event.
     *
     * @param  \App\Events\UserLoginEvent  $event
     * @return void
     */
    public function handle(UserLoginEvent $event)
    {
        $data = $event->getData();
        $log = new LoginLog();
        $log->create($data);
    }
}
