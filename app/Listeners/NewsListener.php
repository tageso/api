<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Events\NewsEvent;
use App\Models\News;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewsListener
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
    public function handle(NewsEvent $event)
    {
        $news = new News();
        $news->title = $event->getTitle();
        $news->description = $event->getText();
        $news->link = $event->getLink();
        $news->setCreatedAt(date("Y-m-d H:i:s", $event->getTimestamp()));
        echo date("Y-m-d H:i:s", $event->getTimestamp());
        exit();
        $news->user_id = $event->getUser()->id;
        $news->saveOrFail();
    }
}
