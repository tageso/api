<?php

namespace App\Http\Controllers;

use App\Exceptions\HTTPException;
use App\Exceptions\NotLoggedInException;
use App\Models\UserProfile;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Routing\Controller as BaseController;
use TaGeSo\APIResponse\Response;

class ProfileController extends BaseController
{

    public function getProfile($user_id, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $userProfile = UserProfile::query()->where("user_id", "=", $user_id)->first();

        return new \App\Http\Resources\UserProfile($userProfile);
    }

    public function updateProfile($user_id, Request $request, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        if (!(Auth::user()->admin || Auth::user()->id == $user_id)) {
           throw new HTTPException("You have no permission to change this User", 403);
        }

        $userProfile = UserProfile::query()->where("user_id", "=", $user_id)->first();
        $userProfile->username = $request->input("username", $userProfile->username);

        $userProfile->saveOrFail();

        return new \App\Http\Resources\UserProfile($userProfile);
    }
}
