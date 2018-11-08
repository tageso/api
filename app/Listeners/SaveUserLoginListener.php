<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Events\UserLoggedInEvent;
use App\Models\UserLogin;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SaveUserLoginListener
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
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(UserLoggedInEvent $event)
    {
        $userLogin = new UserLogin();
        $userLogin->user_id = $event->getUser()->id;
        $userLogin->login = date("Y-m-d H:i:s");
        $userLogin->save();
    }
}
