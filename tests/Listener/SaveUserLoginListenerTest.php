<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class SaveUserLoginListenerTest extends TestCase
{
    public function testUserLogin() {

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();

        $adminLogins = \App\Models\UserLogin::query()->where("user_id", "=", $user->id)->count();
        $this->assertEquals(0, $adminLogins);

        $listener = new \App\Listeners\SaveUserLoginListener();
        $event = new \App\Events\UserLoggedInEvent($user);
        $listener->handle($event);

        $adminLogins = \App\Models\UserLogin::query()->where("user_id", "=", $user->id)->count();
        $this->assertEquals(1, $adminLogins);
    }


}
