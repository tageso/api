<?php

namespace App\Listeners;

use App\Events\Event;
use App\Events\ExampleEvent;
use Illuminate\Support\Facades\Log;

class EventLogListener
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
    public function handle(Event $event)
    {
        Log::info("Log Event to MYSQL");
        $eventModel = new \App\Models\Event();
        $eventModel->eventType = get_class($event);

        // @todo if just for migration from live
        if ($eventModel->eventType == "App\Events\NewsEvent") {
            $eventModel->setCreatedAt(date("Y-m-d H:i:s", $event->getTimestamp()));
        }

        if (method_exists($event, "getPayload")) {
            $eventModel->payload = \GuzzleHttp\json_encode($event->getPayload());
        } else {
            $eventModel->payload = \GuzzleHttp\json_encode($event);
        }
        if (method_exists($event, "getObjectId")) {
            $eventModel->eventObjectId = \GuzzleHttp\json_encode($event->getObjectId());
        }
        $eventModel->saveOrFail();
    }
}
