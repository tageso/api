<?php

class AgendaGetTest extends TestCase
{
    public function testGetEmptyAgenda()
    {
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = new \App\Models\Organisations();
        $organisation->user_id = 1;
        $organisation->name = str_random(16);
        $organisation->public = true;
        $organisation->saveOrFail();

        $this->get('/v2/organisations/'.$organisation->id."/agenda", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);


        $this->assertResponseOk();
        $this->assertEquals(0, count($data["data"]));
        $this->assertEquals(1, count($data));
    }

    public function testGetEmptyAgendaNoAccessAndPrivate()
    {
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

        $this->get('/v2/organisations/'.$organisation->id."/agenda", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(403);
        $this->assertEquals("You don't have permission to see this Page", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(403, $data["httpCode"]);
        $this->assertEquals("http", $data["typ"]);
        $this->assertEquals(4, count($data));
    }

    public function testGetEmptyAgendaPrivate()
    {
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
        $organisationAccess->read = true;
        $organisationAccess->saveOrFail();

        $this->get('/v2/organisations/'.$organisation->id."/agenda", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        $this->assertEquals(0, count($data["data"]));
        $this->assertEquals(1, count($data));
    }

    public function testGetEmptyCategories()
    {
        $user = \App\Models\User::query()->where("name", "=", "admin")->first();
        $apiKey = new App\Models\ApiKey();
        $apiKey->user_id = $user->id;
        $apiKey->typ = "login";
        $apiKey->api_token = "phpunit";
        $apiKey->saveOrFail();

        $organisation = new \App\Models\Organisations();
        $organisation->user_id = 1;
        $organisation->name = str_random(16);
        $organisation->public = true;
        $organisation->saveOrFail();

        $category2 = new \App\Models\Categories();
        $category2->organisation_id = $organisation->id;
        $category2->position = 2;
        $category2->name = "Cat 2";
        $category2->status = "active";
        $category2->user_id = $user->id;
        $category2->saveOrFail();

        $category1 = new \App\Models\Categories();
        $category1->organisation_id = $organisation->id;
        $category1->position = 1;
        $category1->name = "Cat 1";
        $category1->status = "active";
        $category1->user_id = $user->id;
        $category1->saveOrFail();

        $category = new \App\Models\Categories();
        $category->organisation_id = $organisation->id;
        $category->position = 3;
        $category->name = "Cat 2";
        $category->status = "deleted";
        $category->user_id = $user->id;
        $category->saveOrFail();

        $this->get('/v2/organisations/'.$organisation->id."/agenda", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        $this->assertEquals(1, count($data));
        $this->assertEquals(2, count($data["data"]));

        $this->assertEquals($category1->id, $data["data"][0]["id"]);
        $this->assertEquals($category1->name, $data["data"][0]["name"]);
        $this->assertEquals($category1->organisation_id, $data["data"][0]["organisation"]);
        $this->assertEquals($category1->status, $data["data"][0]["status"]);
        $this->assertEquals($category1->position, $data["data"][0]["position"]);
        $this->assertEquals($category1->organisation_id, $data["data"][0]["agenda"]);
        $this->assertEquals($category1->id, $data["data"][0]["_id"]);
        $this->assertFalse($data["data"][0]["delete"]);
        $this->assertEquals(0, $data["data"][0]["openItemsCount"]);
        $this->assertEquals(0, count($data["data"][0]["items"]));

        $this->assertEquals($category2->id, $data["data"][1]["id"]);
        $this->assertEquals($category2->name, $data["data"][1]["name"]);
        $this->assertEquals($category2->organisation_id, $data["data"][1]["organisation"]);
        $this->assertEquals($category2->status, $data["data"][1]["status"]);
        $this->assertEquals($category2->position, $data["data"][1]["position"]);
        $this->assertEquals($category2->organisation_id, $data["data"][1]["agenda"]);
        $this->assertEquals($category2->id, $data["data"][1]["_id"]);
        $this->assertFalse($data["data"][1]["delete"]);
        $this->assertEquals(0, $data["data"][1]["openItemsCount"]);
        $this->assertEquals(0, count($data["data"][1]["items"]));
    }

    // @todo check items

}