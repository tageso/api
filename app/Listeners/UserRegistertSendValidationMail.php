<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Events\UserRegisterEvent;
use App\Jobs\MailerJob;
use App\Models\EmailValidation;
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
     * @param  UserRegisterEvent  $event
     * @return void
     */
    public function handle(UserRegisterEvent $event)
    {
        $twig = app(\Twig_Environment::class);

        $username = $event->getUser()->getProfile()->username;

        $emailValidation = EmailValidation::query()
            ->where("status", "=", "validationSend")
            ->where("user_id", "=", $event->getUser()->id)
            ->first();

        if ($emailValidation == null) {
            throw new \Exception("No open E-Mail Validation for Registration");
        }
        $url = getenv("FRONTEND_URL")."#/activate/".$emailValidation->id."/".$emailValidation->token;

        $mailer = new MailerJob();
        $mailer->subject = "Tageso Account Validation";
        $mailer->toName = $username;
        $mailer->toMail = $event->getUser()->email;
        $mailer->bodyHTML = $twig->render('emailValidation/html.twig', ["username" => $username, "url" => $url]);
        $mailer->bodyPlain = $twig->render('emailValidation/plainText.twig', ["username" => $username, "url" => $url]);

        Queue::push($mailer);
    }
}
