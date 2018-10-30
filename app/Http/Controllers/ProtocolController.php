<?php

namespace App\Http\Controllers;

use App\Exceptions\HTTPException;
use App\Exceptions\NotLoggedInException;
use App\Models\Categories;
use App\Models\Organisations;
use App\Models\Protocol;
use App\Models\UserOrganisations;
use App\Models\Users;
use Illuminate\Support\Facades\Auth;
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
        $protocol->start = date("Y-m-d H:i:s e");
        $protocol->ende = null;
        $protocol->saveOrFail();

        return $response->withData(new \App\Http\Resources\Protocol($protocol))->setStatusCode(201);
    }

    public function getProtocolDeprecated($organisation_id, $protocol_id)
    {
        $organisation = Organisations::getById($organisation_id);
        $protocol = Protocol::query()->where("id", "=", $protocol)->first();

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

        $categories = [];
        $categoriesList = Categories::getForOrganisation($organisation_id, false);

        //Get Caregories for Date
        foreach ($categoriesList as $category) {
            $categories[] = Categories::getForDate($category->id, $protocol->ende);
        }
    }
}
