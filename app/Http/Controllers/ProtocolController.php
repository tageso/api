<?php

namespace App\Http\Controllers;

use App\Events\ProtocolClosed;
use App\Exceptions\HTTPException;
use App\Exceptions\NotLoggedInException;
use App\Http\Resources\ProtocolItem;
use App\Models\Categories;
use App\Models\Item;
use App\Models\Organisations;
use App\Models\Protocol;
use App\Models\ProtocolItems;
use App\Models\User;
use App\Models\UserOrganisations;
use App\Models\UserProfile;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller as BaseController;
use TaGeSo\APIResponse\Response;

class ProtocolController extends BaseController
{
    public function listProtocols($organisation_id, Response $response)
    {
        $organisation = Organisations::getById($organisation_id);

        if ($organisation->public == false) {
            if (!Auth::check()) {
                throw new NotLoggedInException();
            }

            $organisationAuth = UserOrganisations::query()
                ->where("user_id", "=", Auth::user()->id)
                ->where("organisation_id", "=", $id)
                ->orderBy("id", "DESCp")
                ->first();

            if ($organisationAuth == null || $organisationAuth->access == false || $organisationAuth->read == false) {
                throw new HTTPException("You don't have permission to see this Page", 403);
            }
        }

        $protocols = Protocol::query()
            ->where("organisation_id", "=", $organisation_id)
            ->whereIn("status", ["closed", "open"])
            ->paginate(100);


        $response->setPagination($protocols->currentPage(), $protocols->lastPage(), $protocols->perPage());
        return $response->withData(\App\Http\Resources\Protocol::collection($protocols));
    }

    public function createProtocol($organisation_id, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        #$organisation = Organisations::getById($organisation_id);
        $organisationAccess = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);

        if (!$organisationAccess->protocol) {
            throw new HTTPException("You don't have permission to create a Protocol for this organisation", 403);
        }

        $protocol = Protocol::query()
            ->where("organisation_id", "=", $organisation_id)
            ->where("status", "=", "open")
            ->first();

        if ($protocol != null) {
            return $response->withData(new \App\Http\Resources\Protocol($protocol))->setStatusCode(200);
        }

        $protocol = new Protocol();
        $protocol->organisation_id = $organisation_id;
        $protocol->user_id = Auth::user()->id;
        $protocol->user_closed = null;
        $protocol->status = "open";
        $protocol->start = date("Y-m-d H:i:s");
        $protocol->ende = null;
        $protocol->saveOrFail();

        return $response->withData(new \App\Http\Resources\Protocol($protocol))->setStatusCode(201);
    }

    public function getProtocolDetails($protocol_id)
    {
        $protocol = Protocol::query()->where("id", "=", $protocol_id)->first();
        $organisation = Organisations::getById($protocol->organisation_id);

        $categories = [];
        $categoriesList = Categories::getForOrganisation($organisation->id, false);

        //Get Categories for Date
        foreach ($categoriesList as $category) {
            $cat = Categories::getForDate($category->id, $protocol->ende);
            if ($cat->status == "active") {
                $categories[] = $cat;
            }
        }

        usort($categories, [$this, 'sortByPosition']);

        $items = Item::getAllForOrganisation($organisation->id);
        foreach ($items as $itemData) {
            $protocolItem = ProtocolItems::query()
                ->where("item_id", "=", $itemData->id)
                ->where("protocol_id", "=", $protocol->id)
                ->first();
            if ($protocolItem != null) {
                $item = Item::getForDate($itemData->id, $protocol->ende);
                $item->protocol = new ProtocolItem($protocolItem);
                $itemsPerCat[$item->category_id][] = $item;
            }
        }

        $resCategories = [];
        foreach ($categories as $category) {
            if (isset($itemsPerCat[$category->id])) {
                usort($itemsPerCat[$category->id], [$this, 'sortByPosition']);
                $category->items = $itemsPerCat[$category->id];
                $resCategories[] = $category;
            }
        }

        return $resCategories;
    }
    public function getProtocolDeprecated($organisation_id, $protocol_id, Response $response)
    {
        $organisation = Organisations::getById($organisation_id);
        $protocol = Protocol::query()->where("id", "=", $protocol_id)->first();

        if ($protocol->organisation_id != $organisation_id) {
            throw new HTTPException("Organisation has not this Protocol", 400);
        }

        if ($organisation->public == false) {
            if (!Auth::check()) {
                throw new NotLoggedInException();
            }

            $organisationAuth = UserOrganisations::query()
                ->where("user_id", "=", Auth::user()->id)
                ->where("organisation_id", "=", $id)
                ->first();

            if ($organisationAuth == null || $organisationAuth->access == false || $organisationAuth->read == false) {
                throw new HTTPException("You don't have permission to see this Page", 403);
            }
        }

        $resCategories = $this->getProtocolDetails($protocol_id);


        $res["agenda"] = $organisation->id;
        $d = new \DateTime($protocol->ende);
        $d->setTimezone(new \DateTimeZone('Europe/Berlin'));
        $res["date"] = $d->format("d.m.Y H:i");
        $res["done"] = true;
        $res["canceld"] = false;
        try {
            $res["accountCreateCallName"] = User::getById($protocol->user_id)->getProfile()->username;
        } catch (\Exception $e) {
            $res["accountCreateCallName"] = "Unknown";
        }
        try {
            $res["accountClosedCallName"] = User::getById($protocol->user_closed)->getProfile()->username;
        } catch (\Exception $e) {
            $res["accountClosedCallName"] = "Unknown";
        }
        #var_dump($categories);exit();
        $res["agendaItems"] = $resCategories;

        return $response->withData($res);
    }

    private function sortByPosition($a, $b)
    {
        if ($a->position == $b->position) {
            Log::warning("To Objects has the same position");
            return 0;
        }
        $res = ($a->position < $b->position) ? -1 : 1;
        return $res;
    }

    public function saveProtocolItems($organisation_id, Request $request, Response $response)
    {
        Log::debug("Save Protocol Items");
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        #$organisation = Organisations::getById($organisation_id);
        $organisationAccess = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);

        Log::debug("Protocol Access: ".$organisationAccess->protocol);

        if (!$organisationAccess->protocol) {
            throw new HTTPException("You don't have the permission to save Protocol Items", 403);
        }

        $protocol = Protocol::getOpenProtocol($organisation_id);

        $input = json_decode($request->getContent());

        Log::debug("Protocol start: ".$protocol->start);

        foreach ($input->items as $itemData) {
            $protocolItem = ProtocolItems::getItemForProtocol($itemData->id, $protocol->id);
            $protocolItem->description = $itemData->text;
            $protocolItem->markedAsClosed = $itemData->close;
            $protocolItem->saveOrFail();
        }

        return $response;
    }

    public function saveProtocolItemsAndClose($organisation_id, Request $request, Response $response)
    {
        Log::debug("Save Protocol Items");
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        #$organisation = Organisations::getById($organisation_id);
        $organisationAccess = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);

        Log::debug("Protocol Access: ".$organisationAccess->protocol);

        if (!$organisationAccess->protocol) {
            throw new HTTPException("You don't have the permission to save Protocol Items", 403);
        }

        $protocol = Protocol::getOpenProtocol($organisation_id);

        $input = json_decode($request->getContent());

        Log::debug("Protocol start: ".$protocol->start);

        foreach ($input->items as $itemData) {
            $protocolItem = ProtocolItems::getItemForProtocol($itemData->id, $protocol->id);
            $protocolItem->description = $itemData->text;
            $protocolItem->markedAsClosed = $itemData->close;
            $protocolItem->saveOrFail();
        }

        $protocol->status = "closed";
        $protocol->ende = date("Y-m-d H:i:s");
        $protocol->user_closed = Auth::user()->id;
        $protocol->saveOrFail();

        $event = new ProtocolClosed($protocol);
        event($event);

        return $response;
    }

    public function cancelProtocol($organisation_id)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $organisationAccess = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);

        if (!$organisationAccess->protocol) {
            throw new HTTPException("You don't have the permission to save Protocol Items", 403);
        }

        $protocol = Protocol::getOpenProtocol($organisation_id);
        $protocol->status = "canceled";
        $protocol->ende = date("Y-m-d H:i:s");
        $protocol->user_closed = Auth::user()->id;
        $protocol->saveOrFail();
    }
}
