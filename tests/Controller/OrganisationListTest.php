<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class OrganisationListTest extends TestCase
{

    public function testListOrganisation() {

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();


        $this->get('/v2/organisations', ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(20, count($data["data"]));
        $this->assertEquals(20, $data["pagination"]["itemsPerPage"]);
        $this->assertEquals(6, $data["pagination"]["pageCount"]);
        $this->assertEquals(1, $data["pagination"]["currentPage"]);

        $this->assertTrue(isset($data["data"][0]["id"]));
        $this->assertTrue(isset($data["data"][0]["name"]));
        $this->assertTrue(isset($data["data"][0]["public"]));
        $this->assertTrue(isset($data["data"][0]["status"]));
        $this->assertTrue(array_key_exists("url", $data["data"][0]));
        $this->assertTrue(isset($data["data"][0]["delete"]));
        $this->assertTrue(isset($data["data"][0]["_id"]));

        $this->assertEquals(7, count($data["data"][0]));

        $this->assertTrue(isset($data["data"][18]["id"]));
        $this->assertTrue(isset($data["data"][18]["name"]));
        $this->assertTrue(isset($data["data"][18]["public"]));
        $this->assertTrue(isset($data["data"][18]["status"]));
        $this->assertTrue(array_key_exists("url", $data["data"][18]));
        $this->assertTrue(isset($data["data"][18]["delete"]));
        $this->assertTrue(isset($data["data"][18]["_id"]));

        $this->assertEquals(7, count($data["data"][18]));
    }

    public function testListOrganisationPagination() {

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();


        $this->get('/v2/organisations?page=3', ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(20, count($data["data"]));
        $this->assertEquals(20, $data["pagination"]["itemsPerPage"]);
        $this->assertEquals(6, $data["pagination"]["pageCount"]);
        $this->assertEquals(3, $data["pagination"]["currentPage"]);
    }

    public function testListOrganisationPaginationEmptyPAge() {

        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();


        $this->get('/v2/organisations?page=99', ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(0, count($data["data"]));
        $this->assertEquals(20, $data["pagination"]["itemsPerPage"]);
        $this->assertEquals(6, $data["pagination"]["pageCount"]);
        $this->assertEquals(99, $data["pagination"]["currentPage"]);
    }

    // @ToDo Check access


}
