<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CategoryListTest extends TestCase
{
    public function testGetEmptyCategory() {

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


        $this->get('/v2/organisations/'.$organisation->id."/categories", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(0, count($data["data"]));
        $this->assertEquals(2, count($data));
    }
    public function testGetCategory() {

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

        \Illuminate\Support\Facades\DB::table('categories')->truncate();

        $categorie = new \App\Models\Categories();
        $categorie->name = "Test";
        $categorie->status = "active";
        $categorie->position = 0;
        $categorie->organisation_id = $organisation->id;
        $categorie->user_id = 1;
        $categorie->saveOrFail();


        $this->get('/v2/organisations/'.$organisation->id."/categories", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(2, count($data));
        $this->assertEquals(1, count($data["data"]));
        $this->assertEquals($organisation->id, $data["data"][0]["organisation"]);
        $this->assertEquals($categorie->id, $data["data"][0]["id"]);
        $this->assertEquals($categorie->name, $data["data"][0]["name"]);
        $this->assertEquals($categorie->status, $data["data"][0]["status"]);
        $this->assertEquals($categorie->position, $data["data"][0]["position"]);
        $this->assertEquals(null, $data["data"][0]["openItemsCount"]);
        $this->assertEquals($organisation->id, $data["data"][0]["agenda"]);
        $this->assertEquals(false, $data["data"][0]["delete"]);
        $this->assertEquals($categorie->id, $data["data"][0]["_id"]);
    }

    // @todo meht tests
}