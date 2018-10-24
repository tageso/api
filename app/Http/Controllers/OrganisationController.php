<?php

namespace App\Http\Controllers;

use App\Events\OrganisationCreate;
use App\Events\OrganisationUpdated;
use App\Exceptions\NotLoggedInException;
use App\Http\Resources\Organisation;
use App\Models\Organisations;
use App\Models\UserOrganisations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Event;
use App\Exceptions\HTTPException;
use Illuminate\Http\Request;
use TaGeSo\APIResponse\Response;

class OrganisationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function listOrganisationForUser(Request $request, Response $response) {
        if(!Auth::check()) {
            throw new NotLoggedInException();
        }


        $userOrganisations = UserOrganisations::query()
            ->where("user_id", "=", Auth::user()->id)
            ->where("access", "=", true)
            ->paginate(20);

        $organisations = [];
        foreach($userOrganisations as $org) {
            $org = Organisations::getById($org->organisation_id);
            if($org !== null)
            {
                $organisations[] = $org;
            }
        }

        $response->setPagination($userOrganisations->currentPage(), $userOrganisations->lastPage(), $userOrganisations->perPage());
        $response->withData(Organisation::collection(collect($organisations)));

        return $response;
    }

    public function getOrganisation($id, Response $response) {
        $organisation = Organisations::getById($id);
        if($organisation == NULL) {
            throw new HTTPException("Organisation not found", 404);
        }

        if($organisation->public == false) {
            if(!Auth::check()) {
                throw new NotLoggedInException();
            }

            $organisationAuth = UserOrganisations::query()
                ->where("user_id", "=", Auth::user()->id)
                ->where("organisation_id", "=", $id)
                ->first();

            if($organisationAuth == NULL || $organisationAuth->access == false) {
                throw new HTTPException("You don't have permission to see this Page", 403);
            }
        }

        // @todo Open Protokol
        $organisation->openProtocol = false;

        return $response->withData(new Organisation($organisation));
    }

    public function createOrganisation(Request $request, Response $response) {
        if(!Auth::check()) {
            throw new NotLoggedInException();
        }

        $this->validate($request, [
            'name' => 'required|min:4|max:64|regex:@^[a-zA-Z0-9-_+ ]*$@',
            'public' => 'boolean',
            'url' => 'min:4|max:64|regex:@^[a-z0-9-]*$@',
        ]);

        if($request->input("url", true) && !Auth::user()->admin) {
            throw new HTTPException("URL parameter is just avalible for admins atm", 500);
        }

        DB::beginTransaction();

        $organisation = new Organisations();
        $organisation->user_id = Auth::user()->id;
        $organisation->name = $request->input("name");
        $organisation->public = (bool)$request->input("public", true);
        $organisation->url = $request->input("url", null);
        $organisation->status = "active";

        $organisation->saveOrFail();

        $userOrganisation = new UserOrganisations();
        $userOrganisation->user_id = Auth::user()->id;
        $userOrganisation->organisation_id = $organisation->id;
        $userOrganisation->access = true;
        $userOrganisation->read = true;
        $userOrganisation->comment = true;
        $userOrganisation->edit = true;
        $userOrganisation->protocol = true;
        $userOrganisation->admin = true;
        $userOrganisation->notification_protocol = false;
        $userOrganisation->saveOrFail();

        DB::commit();

        Event::fire(new OrganisationCreate($organisation));
        return $response->withData(new Organisation($organisation));
    }

    public function updateOrganisation($id, Request $request, Response $response) {
        // Check if user is logged in
        if(!Auth::check()) {
            throw new NotLoggedInException();
        }

        // get Organisation
        $organisation = Organisations::getById($id);
        if($organisation == NULL) {
            throw new HTTPException("Organisation not found", 404);
        }

        // get Users Access to the Organisation
        $organisationAccess = UserOrganisations::query()
            ->where("organisation_id", "=", $id)
            ->where("user_id", "=", Auth::user()->id)
            ->first();

        // Check users permissions
        if($organisationAccess == null) {
            throw new HTTPException("You don't have access to this Organisation", 403);
        }
        if(!$organisationAccess->access) {
            throw new HTTPException("You don't have permission to change this Organisation", 403);
        }
        if(!$organisationAccess->admin) {
            throw new HTTPException("You don't have permission to change this Organisation", 403);
        }

        // Validate user Input
        $this->validate($request, [
            'name' => 'min:4|max:64|regex:@^[a-zA-Z0-9-_+ ]*$@',
            'public' => 'boolean',
            'url' => 'min:4|max:64|regex:@^[a-z0-9-]*$@',
        ]);


        if($request->input("url", true) && !Auth::user()->admin) {
            throw new HTTPException("URL parameter is just avalible for admins atm", 500);
        }

        $changes = [];

        if($request->input("name", null) !== null) {
            $changes["name"] = ["old" => $organisation->name, "new" => $request->input("name")];
            $organisation->name = $request->input("name");
        }

        if($request->input("public", null) !== null) {
            $changes["public"] = ["old" => $organisation->name, "new" => $request->input("public")];
            $organisation->public = $request->input("public");
        }

        $organisation->saveOrFail();

        Event::fire(new OrganisationUpdated($organisation, $changes));

        return $response->withData(new Organisation($organisation));
    }

    public function deleteOrganisation($id, Response $response) {
        // Check if user is logged in
        if(!Auth::check()) {
            throw new NotLoggedInException();
        }

        $access = UserOrganisations::getAccess(Auth::user()->id, $id);

        if(!$access->admin) {
            throw new HTTPException("Only Admins can delete a organisation", 403);
        }

        $organisation = Organisations::getById($id);

        $changeArray = ["status" => ["old" => $organisation->status, "new" => "deleted"]];
        $organisation->status = "deleted";
        $organisation->saveOrFail();

        Event::fire(new OrganisationUpdated($organisation, $changeArray));

        return $response->withData(new Organisation($organisation));

    }
}
