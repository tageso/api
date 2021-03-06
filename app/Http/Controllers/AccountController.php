<?php

namespace App\Http\Controllers;

use App\Events\NewsEvent;
use App\Events\UserRegisterEvent;
use App\Exceptions\NotLoggedInException;
use App\Models\EmailValidation;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Event;
use App\Events\UserLoggedInEvent;
use App\Exceptions\HTTPException;
use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Http\Request;
use TaGeSo\APIResponse\Response;

class AccountController extends Controller
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

    public function login(Request $request, Response $response)
    {
        $request->merge(["name" => strtolower($request->input("name"))]);
        $this->validate($request, [
            'name' => 'required|min:4|max:64|regex:@^[a-z0-9-_+]*$@',
            'password' => 'required'
        ]);

        $username = strtolower($request->input("name"));
        $password = $request->input("password");

        $user = User::query()->where("name", "=", $username)->get();

        if (count($user) == 0) {
            throw new HTTPException("User not found", 404);
        }

        if (count($user) > 1) {
            throw new HTTPException("User not unique", 500);
        }

        if ($user[0]->password != hash("sha512", $password)) {
            throw new HTTPException("Password wrong", 400);
        }

        if ($user[0]->twoAuthSecret != null) {
            throw new HTTPException("Two Auth is enabled!", 500);
        }

        if ($user[0]->status == "validateSend") {
            throw new HTTPException("Please check your E-Mail and activate the Account", 400);
        }

        if ($user[0]->status == "disabled") {
            throw new HTTPException("The Account is disabled, please contact the support!", 400);
        }

        if ($user[0]->status == "deleted") {
            throw new HTTPException("User not found", 404);
        }


        $apiKey = new ApiKey();
        $apiKey->generateApiKey();
        $apiKey->user_id = $user[0]->id;
        $apiKey->typ = "login";
        $apiKey->save();

        //Compatible to Version 1
        $response->withData(["token" => $apiKey->api_token, "typ" => $apiKey->typ, "user_id" => $apiKey->user_id]);

        Event::fire(new UserLoggedInEvent($user[0]));

        return $response;
    }

    public function register(Request $request, Response $response)
    {
        $request->merge(["name" => strtolower($request->input("name"))]);
        $this->validate($request, [
            'name' => 'required|min:4|max:64|regex:@^[a-z0-9-_+]*$@|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        DB::beginTransaction();

        $user = new User();
        $user->name = $request->input("name");
        $user->email = $request->input("email");
        $user->password = hash("sha512", $request->input("password"));
        $user->status = "validateSend";
        $user->mailStatus = "validateSend";
        $user->generateDisabledMailToken();
        $user->saveOrFail();

        $userProfile = new UserProfile();
        $userProfile->username = $user->name;
        $userProfile->user_id = $user->id;
        $userProfile->saveOrFail();

        $emailValidation = new EmailValidation();
        $emailValidation->user_id = $user->id;
        $emailValidation->email = $request->input("email");
        $emailValidation->used_for = "user";
        $emailValidation->status = "validationSend";
        $emailValidation->generateToken();
        $emailValidation->saveOrFail();

        Event::fire(new UserRegisterEvent($user));

        $response->withData(new \App\Http\Resources\User($user));

        DB::commit();

        Event::fire(new NewsEvent($user->id, $user->id, "newAccount"));

        return $response;
    }

    public function getAccount($account_id, Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        $user = User::query()->where("id", "=", $account_id)->first();

        if (!(Auth::user()->admin || (Auth::user()->id === $account_id))) {
            throw new HTTPException("You have no permission to see this user", 403);
        }

        $user->callName = $user->getProfile()->username; // @todo remove this with new api

        return $response->withData(new \App\Http\Resources\User($user));
    }

    public function getAccountMe(Response $response)
    {
        if (!Auth::check()) {
            throw new NotLoggedInException();
        }

        return $this->getAccount(Auth::user()->id, $response);
    }

    public function validateMail($validation_id, $token, Response $response)
    {
        $validation = EmailValidation::query()
            ->where("id", "=", $validation_id)
            ->where("status", "=", "validationSend")
            ->first();

        if ($validation === null) {
            throw new HTTPException("Validation not found", 400);
        }

        if ($validation->token == $token) {
            $validation->status = "validated";
            if ($validation->used_for == "user") {
                $user = User::query()
                    ->where("id", "=", $validation->user_id)
                    ->first();
                $user->email = $validation->email;
                $user->status = "active";
                if ($user->mailStatus == "validateSend") {
                    $user->mailStatus = "active";
                }
                $user->saveOrFail();
            }
            $validation->saveOrFail();
            return $response->withData([]);
        }
        throw new HTTPException("Validation not possible", 400);
    }
}
