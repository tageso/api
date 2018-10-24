<?php

class AgendaGetDeprecatedTest extends TestCase
{
    public function testGetAgenda()
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

        $this->get('/v2/organisations/'.$organisation->id."/agenda/deprecated", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(1, count($data));
        $this->assertEquals(3, count($data["data"]));
        $this->assertArrayHasKey("data", $data);

        //Items
        $this->assertArrayHasKey("allItems", $data["data"]);
        $this->assertEquals(0, count($data["data"]["allItems"]));

        //Agenda
        $this->assertArrayHasKey("agenda", $data["data"]);
        $this->assertEquals($organisation->id, $data["data"]["agenda"]["id"]);
        $this->assertEquals($organisation->name, $data["data"]["agenda"]["name"]);
        $this->assertEquals($organisation->public, $data["data"]["agenda"]["public"]);
        $this->assertEquals("active", $data["data"]["agenda"]["status"]);
        $this->assertEquals($organisation->url, $data["data"]["agenda"]["url"]);
        $this->assertEquals(false, $data["data"]["agenda"]["delete"]);
        $this->assertEquals($organisation->id, $data["data"]["agenda"]["_id"]);

        //Access
        $this->assertArrayHasKey("access", $data["data"]);
        $this->assertEquals($user->id, $data["data"]["access"]["account"]);
        $this->assertEquals($organisation->id, $data["data"]["access"]["organisation"]);
        $this->assertFalse($data["data"]["access"]["admin"]);
        $this->assertFalse($data["data"]["access"]["edit"]);
        $this->assertTrue($data["data"]["access"]["new"]);
        $this->assertFalse($data["data"]["access"]["protocol"]);
        $this->assertTrue($data["data"]["access"]["read"]);
        $this->assertFalse($data["data"]["access"]["access"]);
        $this->assertFalse($data["data"]["access"]["notificationMailProtocol"]);
        $this->assertFalse($data["data"]["access"]["comment"]);
        $this->assertNull($data["data"]["access"]["_id"]);
        $this->assertEquals($organisation->id, $data["data"]["access"]["agenda"]);

    }
    public function testGetPrivateAgendaWithNoAccess()
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

        $this->get('/v2/organisations/'.$organisation->id."/agenda/deprecated", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(4, count($data));
        $this->seeStatusCode(403);
        $this->assertEquals(403, $data["httpCode"]);
        $this->assertEquals("You don't have permission to see this Page", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals("http", $data["typ"]);

    }

    public function testGetPrivateAgendaWithAccess()
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

        $this->get('/v2/organisations/'.$organisation->id."/agenda/deprecated", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(1, count($data));
        $this->assertEquals(3, count($data["data"]));
        $this->assertArrayHasKey("data", $data);

        //Items
        $this->assertArrayHasKey("allItems", $data["data"]);
        $this->assertEquals(0, count($data["data"]["allItems"]));

        //Agenda
        $this->assertArrayHasKey("agenda", $data["data"]);
        $this->assertEquals($organisation->id, $data["data"]["agenda"]["id"]);
        $this->assertEquals($organisation->name, $data["data"]["agenda"]["name"]);
        $this->assertEquals($organisation->public, $data["data"]["agenda"]["public"]);
        $this->assertEquals("active", $data["data"]["agenda"]["status"]);
        $this->assertEquals($organisation->url, $data["data"]["agenda"]["url"]);
        $this->assertEquals(false, $data["data"]["agenda"]["delete"]);
        $this->assertEquals($organisation->id, $data["data"]["agenda"]["_id"]);

        //Access
        $this->assertArrayHasKey("access", $data["data"]);
        $this->assertEquals($user->id, $data["data"]["access"]["account"]);
        $this->assertEquals($organisation->id, $data["data"]["access"]["organisation"]);
        $this->assertFalse($data["data"]["access"]["admin"]);
        $this->assertFalse($data["data"]["access"]["edit"]);
        $this->assertFalse($data["data"]["access"]["new"]);
        $this->assertFalse($data["data"]["access"]["protocol"]);
        $this->assertTrue($data["data"]["access"]["read"]);
        $this->assertTrue($data["data"]["access"]["access"]);
        $this->assertFalse($data["data"]["access"]["notificationMailProtocol"]);
        $this->assertFalse($data["data"]["access"]["comment"]);
        $this->assertEquals($organisationAccess->id, $data["data"]["access"]["_id"]);
        $this->assertEquals($organisation->id, $data["data"]["access"]["agenda"]);
    }

    public function testCategoryAndItems() {
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

        $category = new \App\Models\Categories();
        $category->organisation_id = $organisation->id;
        $category->position = 2;
        $category->name = "Cat 2";
        $category->status = "active";
        $category->user_id = $user->id;
        $category->saveOrFail();

        $category = new \App\Models\Categories();
        $category->organisation_id = $organisation->id;
        $category->position = 3;
        $category->name = "Cat 3";
        $category->status = "deleted";
        $category->user_id = $user->id;
        $category->saveOrFail();

        $category = new \App\Models\Categories();
        $category->organisation_id = $organisation->id;
        $category->position = 1;
        $category->name = "Cat 1";
        $category->status = "active";
        $category->user_id = $user->id;
        $category->saveOrFail();

        $item = new \App\Models\Item();
        $item->name = "Item 1";
        $item->description = "Beschreibung";
        $item->user_id = $user->id;
        $item->position = 0;
        $item->category_id = $category->id;
        $item->status = "active";
        $item->saveOrFail();

        $this->get('/v2/organisations/'.$organisation->id."/agenda/deprecated", ["accept" => "application/json", "authorization" => "phpunit"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals(1, count($data));
        $this->assertEquals(3, count($data["data"]));
        $this->assertArrayHasKey("data", $data);

        //Items
        $this->assertArrayHasKey("allItems", $data["data"]);
        $this->assertEquals(2, count($data["data"]["allItems"]));

        $this->assertEquals("Cat 1", $data["data"]["allItems"][0]["name"]);
        $this->assertEquals("Cat 2", $data["data"]["allItems"][1]["name"]);
        $this->assertEquals(0, count($data["data"]["allItems"][1]["items"]));

        $this->assertEquals(1, count($data["data"]["allItems"][0]["items"]));
        $this->assertEquals("Item 1", $data["data"]["allItems"][0]["items"][0]["name"]);
        $this->assertEquals("Beschreibung", $data["data"]["allItems"][0]["items"][0]["description"]);

    }
}