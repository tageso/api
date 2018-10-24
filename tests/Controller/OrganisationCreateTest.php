<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class OrganisationCreateTest extends TestCase
{

    public function testOrganisationCreateResponse() {
        $this->withoutEvents();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = \App\Models\Organisations::query()
            ->where("name", "=", "PHPUnit")
            ->count();

        $this->assertEquals(0, $organisation);


        $this->post("/v2/organisations", ["name" => "PHPUnit"], ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("PHPUnit", $data["data"]["name"]);
        $this->assertTrue($data["data"]["public"]);
        $this->assertEquals("active", $data["data"]["status"]);
        $this->assertNull($data["data"]["url"]);
        $this->assertFalse($data["data"]["delete"]);
        $this->assertTrue(array_key_exists("id", $data["data"]));
        $this->assertTrue(array_key_exists("_id", $data["data"]));

        $this->assertEquals(7, count($data["data"]));
    }

    public function testOrganisationModelCreated() {
        $this->withoutEvents();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = \App\Models\Organisations::query()
            ->where("name", "=", "PHPUnit2")
            ->count();

        $this->assertEquals(0, $organisation);


        $this->post("/v2/organisations", ["name" => "PHPUnit2"], ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $organisations = \App\Models\Organisations::query()
            ->where("name", "=", "PHPUnit2")
            ->get();

        $this->assertEquals(1, count($organisations));

        $organisation = $organisations[0];

        $this->assertEquals("PHPUnit2", $organisation->name);
        $this->assertEquals(true, $organisation->public);
        $this->assertNull($organisation->url);
        $this->assertEquals("active", $organisation->status);
        $this->assertEquals($user->id, $organisation->user_id);
        $this->assertNull($organisation->old_uid);
    }

    public function testOrganisationModelCreatedWithURL() {
        $this->withoutEvents();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = \App\Models\Organisations::query()
            ->where("name", "=", "PHPUnit3")
            ->count();

        $this->assertEquals(0, $organisation);


        $this->post("/v2/organisations", ["name" => "PHPUnit3", "url" => "phpunit"], ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $organisations = \App\Models\Organisations::query()
            ->where("name", "=", "PHPUnit3")
            ->get();

        $this->assertEquals(1, count($organisations));

        $organisation = $organisations[0];

        $this->assertEquals("PHPUnit3", $organisation->name);
        $this->assertEquals(true, $organisation->public);
        $this->assertEquals("phpunit", $organisation->url);
        $this->assertEquals("active", $organisation->status);
        $this->assertEquals($user->id, $organisation->user_id);
        $this->assertNull($organisation->old_uid);
    }

    public function testOrganisationModelCreatedWithURLNoAdmin() {
        $this->withoutEvents();
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $user->admin = false;
        $user->saveOrFail();

        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = \App\Models\Organisations::query()
            ->where("name", "=", "PHPUnit4")
            ->count();

        $this->assertEquals(0, $organisation);


        $this->post("/v2/organisations", ["name" => "PHPUnit4", "url" => "phpunit"], ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("URL parameter is just avalible for admins atm", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(500, $data["httpCode"]);
        $this->assertEquals("http", $data["typ"]);

        $this->assertEquals(4, count($data));
       
    }

    public function testEventIsFired() {
        $this->artisan("migrate:fresh");
        $this->artisan("db:seed");
        $this->expectsEvents(App\Events\OrganisationCreate::class);

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $user->saveOrFail();

        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit2createtest";
        $apiKey->saveOrFail();

        $this->post("/v2/organisations", ["name" => "PHPUnit5"], ["accept" => "application/json", "authorization" => "phpunit2createtest"]);
    }

    public function testCreateORganisationWithoutValidAPIKey() {
        $this->post("/v2/organisations", ["name" => "PHPUnit6"], ["accept" => "application/json", "authorization" => "phpunitInvalide"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("You need to login to perform this action", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(401, $data["httpCode"]);
        $this->assertEquals("http", $data["typ"]);
        $this->assertEquals(4, count($data));

        $this->assertEquals(401, $this->response->getStatusCode());
    }

}
