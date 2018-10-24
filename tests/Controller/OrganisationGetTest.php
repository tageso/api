<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class OrganisationGetTest extends TestCase
{

    public function testGetOrganisation() {

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = new \App\Models\Organisations();
        $organisation->user_id = 1;
        $organisation->name = "Test Organisation";
        $organisation->public = true;
        $organisation->saveOrFail();


        $this->get('/v2/organisations/'.$organisation->id, ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertTrue(array_key_exists("id", $data["data"]));
        $this->assertEquals($organisation->name, $data["data"]["name"]);
        $this->assertEquals(true, $data["data"]["public"]);
        $this->assertEquals("active", $data["data"]["status"]);
        $this->assertNull($data["data"]["url"]);
        $this->assertFalse($data["data"]["delete"]);
        $this->assertTrue(array_key_exists("_id", $data["data"]));
        $this->assertFalse($data["data"]["openProtocol"]);
    }

    public function testGetPrivateOrganisation() {

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = new \App\Models\Organisations();
        $organisation->user_id = 1;
        $organisation->name = "Test Organisation";
        $organisation->public = false;
        $organisation->saveOrFail();


        $this->get('/v2/organisations/'.$organisation->id, ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(4, count($data));
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(403, $data["httpCode"]);
        $this->assertEquals("http", $data["typ"]);
        $this->seeStatusCode(403);
        $this->assertEquals("You don't have permission to see this Page", $data["msg"]);
    }

    public function testGetPrivateOrganisationWithAccess() {

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = new \App\Models\Organisations();
        $organisation->user_id = 1;
        $organisation->name = str_random(16);
        $organisation->public = false;
        $organisation->saveOrFail();

        $organisationAccess = new \App\Models\UserOrganisations();
        $organisationAccess->user_id = $user->id;
        $organisationAccess->organisation_id = $organisation->id;
        $organisationAccess->access = true;
        $organisationAccess->admin = false;
        $organisationAccess->edit = false;
        $organisationAccess->comment = false;
        $organisationAccess->read = false;
        $organisationAccess->saveOrFail();



        $this->get('/v2/organisations/'.$organisation->id, ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertTrue(array_key_exists("id", $data["data"]));
        $this->assertEquals($organisation->name, $data["data"]["name"]);
        $this->assertEquals(false, $data["data"]["public"]);
        $this->assertEquals("active", $data["data"]["status"]);
        $this->assertNull($data["data"]["url"]);
        $this->assertFalse($data["data"]["delete"]);
        $this->assertTrue(array_key_exists("_id", $data["data"]));
        $this->assertFalse($data["data"]["openProtocol"]);
    }
    // @ToDo Check if user is logged in and has the right access

}
