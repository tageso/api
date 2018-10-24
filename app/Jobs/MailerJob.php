<?php

namespace App\Jobs;

use PHPMailer\PHPMailer\PHPMailer;

class MailerJob extends Job
{

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;


    public $subject = "";
    public $bodyHTML = "";
    public $bodyPlain = "";
    public $toMail = "";
    public $toName = "";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PHPMailer $mailer)
    {
        $mailer->isSMTP();
        #$mailer->SMTPDebug = 2;
        $mailer->Host = getenv("SMTP_HOST");
        $mailer->SMTPAuth = true;
        $mailer->Username = getenv("SMTP_USER");
        $mailer->Password = getenv("SMTP_PASS");
        $mailer->setFrom(getenv("SMTP_FROM"), getenv("SMTP_FROM_NAME"));
        $mailer->SMTPSecure = getenv("SMTP_SECURE");
        $mailer->Port = getenv("SMTP_PORT");
        $mailer->CharSet = 'UTF-8';
        $mailer->addBCC(getenv("SMTP_BCC"));

        $mailer->addAddress($this->toMail, $this->toName);
        $mailer->Subject = $this->subject;


        if(!empty($this->bodyHTML)) {
            $mailer->isHTML(true);
            $mailer->Body = $this->bodyHTML;
            $mailer->AltBody = $this->bodyPlain;
        } else {
            $mailer->Body = $this->bodyPlain;
        }

        if(getenv("SEND_MAILS"))
        {
            $r = $mailer->send();
        } else {
            throw new \Exception("Mails disabled");
        }


        if($r !== true) {
            throw new \Exception("Mail not send");
        }
    }
}
