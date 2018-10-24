<?php

namespace App\Http\Controllers;

use App\Exceptions\NotLoggedInException;
use App\Models\UserProfile;
use App\Models\Users;
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
}
