<?php

namespace App\Http\Controllers;

use App\Exceptions\HTTPException;
use App\Exceptions\NotLoggedInException;
use App\Http\Resources\Access;
use App\Models\User;
use App\Models\UserOrganisations;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;
use TaGeSo\APIResponse\Response;

class AccessController extends BaseController
{
    public function getAccessDetails($organisation_id, $user_id, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        if ($user_id != Auth::user()->id) {
            $access = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);
            if (!$access->admin) {
                throw new HTTPException("You don't have Permission to see this Access", 403);
            }
        }

        $user = User::query()->where("id", "=", $user_id)->first();
        if ($user === null) {
            throw new HTTPException("User not found", 404);
        }

        $access = UserOrganisations::getAccess($user_id, $organisation_id);
        return $response->withData(new Access($access));
    }

    public function getAccessDetailsMe($organisation_id, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        return $this->getAccessDetails($organisation_id, Auth::user()->id, $response);
    }

    public function listAccess($organisation_id, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $access = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);

        if (!$access->admin) {
            throw new HTTPException("You don't have Permission to see this Access", 403);
        }

        $access = UserOrganisations::query()
            ->where("organisation_id", "=", $organisation_id)
            ->where("access", "=", true)
            ->paginate(100);

        foreach ($access as $accessItem) {
            $accessItem->username = UserProfile::query()
                ->where("user_id", "=", $accessItem->user_id)
                ->first()
                ->username;
        }
        $response->setPagination(
            $access->currentPage(),
            $access->lastPage(),
            $access->perPage()
        );

        return $response->withData(Access::collection($access));
    }

    public function editAccess($organisation_id, $user_id, Request $request, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $userAccess = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);
        if (!$userAccess->admin) {
            throw new HTTPException("You don't have permission to change the Access for this Organisation", 403);
        }

        $access = UserOrganisations::getAccess($user_id, $organisation_id);

        $access->access = $request->input("access", $access->access);
        $access->read = $request->input("read", $access->read);
        $access->comment = $request->input("comment", $access->comment);
        $access->edit = $request->input("edit", $access->edit);
        $access->protocol = $request->input("protocol", $access->protocol);
        $access->admin = $request->input("admin", $access->admin);
        $access->new = false;

        $access->saveOrFail();

        return $response->withData(new Access($access));
    }

    public function notificationDeprecated($organisation_id, Request $request, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $userAccess = UserOrganisations::getAccess(Auth::user()->id, $organisation_id);

        if (!$userAccess->access || !$userAccess->read) {
            throw new HTTPException("You don't have permission to change the Notification settings");
        }

        $userAccess->notification_protocol = $request->input("mail", false);
        $userAccess->saveOrFail();

        return $response->withData(new Access($userAccess));
    }
}
