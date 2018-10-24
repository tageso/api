<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AccountLoginTest extends TestCase
{
    public function testLogin() {
        $this->withoutEvents();
        $this->post('/v2/account/login', ["name"=>"admin", "password"=>"secret"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("login", $data["data"]["typ"]);
        $this->assertEquals(1, $data["data"]["user_id"]);
    }

    public function testShortUsernameLogin() {
        $this->withoutEvents();
        $this->post('/v2/account/login', ["name"=>"abc", "password"=>"secret"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("The given data was invalid.", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(400, $data["httpCode"]);
        $this->assertEquals("validation", $data["typ"]);
        $this->assertEquals("The name must be at least 4 characters.", $data["validation"]["name"][0]);
        $this->assertEquals(1, count($data["validation"]));
        $this->assertEquals(1, count($data["validation"]["name"]));
    }

    public function testInvalidePassword() {
        $this->withoutEvents();
        $this->post('/v2/account/login', ["name"=>"admin", "password"=>"wrongPW"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("Password wrong", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(400, $data["httpCode"]);
        $this->assertEquals("http", $data["typ"]);
    }

    public function testInvalideUser() {
        $this->withoutEvents();
        $this->post('/v2/account/login', ["name"=>"invaludeUserNameNotExistInDB", "password"=>"secret"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("User not found", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(404, $data["httpCode"]);
        $this->assertEquals("http", $data["typ"]);
    }





}
