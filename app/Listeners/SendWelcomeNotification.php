<?php

namespace App\Listeners;

use App\Events\UserAccountActivated;
use App\Notifications\WelcomeMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendWelcomeNotification
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
     * @param  UserAccountActivated  $event
     * @return void
     */
    public function handle(UserAccountActivated $event)
    {
        $event->getUser()->notify(new WelcomeMessage());
    }
}
