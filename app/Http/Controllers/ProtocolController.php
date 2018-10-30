<?php

namespace App\Http\Controllers;

use App\Models\Organisations;
use App\Models\Protocol;
use App\Models\Users;
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
}
