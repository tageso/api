<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Events\UserRegisterEvent;
use App\Jobs\MailerJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Queue;

class UserRegistertSendValidationMail
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;


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
    public function handle(UserRegisterEvent $event)
    {
        $twig = app(\Twig_Environment::class);

        $username = $event->getUser()->getProfile()->username;
        $url = "http://www.google.de";

        $mailer = new MailerJob();
        $mailer->subject = "Tageso Account Validation";
        $mailer->toName = $username;
        $mailer->toMail = $event->getUser()->email;
        $mailer->bodyHTML = $twig->render('emailValidation/html.twig', ["username" => $username, "url" => $url]);
        $mailer->bodyPlain = $twig->render('emailValidation/plainText.twig', ["username" => $username, "url" => $url]);

        Queue::push($mailer);
    }
}
