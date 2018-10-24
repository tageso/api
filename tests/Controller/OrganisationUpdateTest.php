<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class OrganisationUpdateTest extends TestCase
{

    public function testChangeOrganisationName() {
        $this->withoutEvents();

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = new \App\Models\Organisations();
        $organisation->name = "FooBar";
        $organisation->user_id = $user->id;
        $organisation->public = True;
        $organisation->status = "active";
        $organisation->saveOrFail();


        $organisationAccess = new \App\Models\UserOrganisations();
        $organisationAccess->organisation_id = $organisation->id;
        $organisationAccess->user_id = $user->id;
        $organisationAccess->access = True;
        $organisationAccess->admin = True;
        $organisationAccess->saveOrFail();


        $this->patch("/v2/organisations/".$organisation->id, ["name" => "PHPUnit"], ["accept" => "application/json", "authorization" => "phpunit"]);

        $this->assertEquals(200, $this->response->getStatusCode());

        $newOrganisation = \App\Models\Organisations::getById($organisation->id);

        $this->assertEquals("PHPUnit", $newOrganisation->name);
    }

    public function testChangeWithoutAccess() {
        $this->withoutEvents();

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = new \App\Models\Organisations();
        $organisation->name = "FooBar";
        $organisation->user_id = $user->id;
        $organisation->public = True;
        $organisation->status = "active";
        $organisation->saveOrFail();


        $this->patch("/v2/organisations/".$organisation->id, ["name" => "PHPUnit"], ["accept" => "application/json", "authorization" => "phpunit"]);

        $this->assertEquals(403, $this->response->getStatusCode());

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("You don't have access to this Organisation", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(403, $data["httpCode"]);
        $this->assertEquals("http", $data["typ"]);
        $this->assertEquals(4, count($data));
    }

    public function testEventIsFired() {
        $this->expectsEvents(App\Events\OrganisationUpdated::class);

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $user->saveOrFail();

        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = new \App\Models\Organisations();
        $organisation->name = "FooBar";
        $organisation->user_id = $user->id;
        $organisation->public = True;
        $organisation->status = "active";
        $organisation->saveOrFail();

        $organisationAccess = new \App\Models\UserOrganisations();
        $organisationAccess->user_id = $user->id;
        $organisationAccess->organisation_id = $organisation->id;
        $organisationAccess->access = true;
        $organisationAccess->admin = true;
        $organisationAccess->saveOrFail();


        $this->patch("/v2/organisations/".$organisation->id, ["name" => "PHPUnit", "url" => "phpunit"], ["accept" => "application/json", "authorization" => "phpunit"]);
    }
}
