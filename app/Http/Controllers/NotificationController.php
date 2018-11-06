<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;
use TaGeSo\APIResponse\Response;

class NotificationController extends BaseController
{
    public function listNotificationDeprecated(Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $events = Event::query()
            ->where("eventObjectId", "=", Auth::user()->id)
            ->where("eventType", "=", "App\Events\NewsEvent")
            ->paginate(20);

        $eventList = [];

        foreach ($events as $event) {
            $data = \GuzzleHttp\json_decode($event->payload);

            if (isset($data->link_organisation)) {
                $event->agendaName = Organisations::query()->where("id", "=", $data->link_organisation)->first()->name;
            }

            if (isset($data->link_item)) {
                $event->itemName = Item::query()->where("id", "=", $data->link_item)->first()->name;
            }

            if (isset($data->user)) {
                $event->username = User::getById($data->user)->getProfile()->username;
            }

            $eventList[] = $event;
        }


        $response->setPagination(
            $events->currentPage(),
            $events->lastPage(),
            $events->perPage()
        );

        return $response->withData(\App\Http\Resources\Event::collection(collect($eventList)));
    }
}
