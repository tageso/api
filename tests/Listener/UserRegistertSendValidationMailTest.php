<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UserRegistertSendValidationMailTest extends TestCase
{
    public function testJobCreat() {
        Queue::fake();

        $listener = new \App\Listeners\UserRegistertSendValidationMail();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $event = new \App\Events\UserRegisterEvent($user);
        $listener->handle($event);

        Queue::assertPushed(\App\Jobs\MailerJob::class, 1);
    }

    public function testMailSubject() {
        Queue::fake();

        $listener = new \App\Listeners\UserRegistertSendValidationMail();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $event = new \App\Events\UserRegisterEvent($user);
        $listener->handle($event);

        Queue::assertPushed(\App\Jobs\MailerJob::class, function ($job) {
            return $job->subject === "Tageso Account Validation";
        });
    }

    public function testPlainTextMail() {
        Queue::fake();

        $listener = new \App\Listeners\UserRegistertSendValidationMail();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $event = new \App\Events\UserRegisterEvent($user);
        $listener->handle($event);

        Queue::assertPushed(\App\Jobs\MailerJob::class, function ($job) {
            #file_put_contents(__DIR__."/files/plainTextMailAccountValiation.txt", $job->bodyPlain);
            return $job->bodyPlain === file_get_contents(__DIR__."/files/plainTextMailAccountValiation.txt");
        });
    }
    public function testHTMLMail() {
        Queue::fake();

        $listener = new \App\Listeners\UserRegistertSendValidationMail();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $event = new \App\Events\UserRegisterEvent($user);
        $listener->handle($event);

        Queue::assertPushed(\App\Jobs\MailerJob::class, function ($job) {
            #file_put_contents(__DIR__."/files/htmlMailAccountValidation.txt", $job->bodyHTML);
            return $job->bodyHTML === file_get_contents(__DIR__."/files/htmlMailAccountValidation.txt");
        });
    }
    public function testToMail() {
        Queue::fake();

        $listener = new \App\Listeners\UserRegistertSendValidationMail();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $event = new \App\Events\UserRegisterEvent($user);
        $listener->handle($event);

        Queue::assertPushed(\App\Jobs\MailerJob::class, function ($job) use ($user) {
            return $job->toMail == $user->email;
        });
    }

   public function testToName() {
        Queue::fake();

        $listener = new \App\Listeners\UserRegistertSendValidationMail();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $event = new \App\Events\UserRegisterEvent($user);
        $listener->handle($event);

        Queue::assertPushed(\App\Jobs\MailerJob::class, function ($job) use ($user) {
            return $job->toName == $user->getProfile()->username;
        });
    }


}
