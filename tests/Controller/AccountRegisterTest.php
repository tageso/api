<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AccountRegisterTest extends TestCase
{
    public function testRegisterUsernameExists() {
        $this->post('/v2/account/register', ["name"=>"admin", "password"=>"adminadmin", "email" => "info@tageso.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("The given data was invalid.", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(400, $data["httpCode"]);
        $this->assertEquals("validation", $data["typ"]);
        $this->assertEquals("The name has already been taken.", $data["validation"]["name"][0]);
        $this->assertEquals(1, count($data["validation"]));
        $this->assertEquals(1, count($data["validation"]["name"]));
    }

    public function testRegisterAccount() {
        $this->withoutEvents();

        $this->post('/v2/account/register', ["name"=>"testuser", "password"=>"adminadmin", "email" => "hello@kekskurse.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("testuser", $data["data"]["name"]);
        $this->assertEquals("hello@kekskurse.de", $data["data"]["email"]);
        $this->assertEquals("validateSend", $data["data"]["status"]);
        $this->assertEquals("validateSend", $data["data"]["mailStatus"]);
    }

    public function testRegisterUsernameToLower() {
        $this->withoutEvents();

        $this->post('/v2/account/register', ["name"=>"tEstUsEr2", "password"=>"adminadmin", "email" => "hello2@kekskurse.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("testuser2", $data["data"]["name"]);
        $this->assertEquals("hello2@kekskurse.de", $data["data"]["email"]);
        $this->assertEquals("validateSend", $data["data"]["status"]);
        $this->assertEquals("validateSend", $data["data"]["mailStatus"]);

    }

    public function testTwoRegistrationWithSameUsernameButLowerUpper() {
        $this->withoutEvents();

        $this->post('/v2/account/register', ["name"=>"tEstUsEr3", "password"=>"adminadmin", "email" => "hello3@kekskurse.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("testuser3", $data["data"]["name"]);
        $this->assertEquals("hello3@kekskurse.de", $data["data"]["email"]);
        $this->assertEquals("validateSend", $data["data"]["status"]);
        $this->assertEquals("validateSend", $data["data"]["mailStatus"]);

        $this->post('/v2/account/register', ["name"=>"TeSTuSeR3", "password"=>"adminadmin", "email" => "test3@tageso.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("The given data was invalid.", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(400, $data["httpCode"]);
        $this->assertEquals("validation", $data["typ"]);
        $this->assertEquals("The name has already been taken.", $data["validation"]["name"][0]);
        $this->assertEquals(1, count($data["validation"]));
        $this->assertEquals(1, count($data["validation"]["name"]));

    }

    public function testUserEmailTwice() {
        $this->withoutEvents();

        $this->post('/v2/account/register', ["name"=>"user1", "password"=>"adminadmin", "email" => "test@tageso.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("user1", $data["data"]["name"]);
        $this->assertEquals("test@tageso.de", $data["data"]["email"]);
        $this->assertEquals("validateSend", $data["data"]["status"]);
        $this->assertEquals("validateSend", $data["data"]["mailStatus"]);

        $this->post('/v2/account/register', ["name"=>"user2", "password"=>"adminadmin", "email" => "test@tageso.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("The given data was invalid.", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(400, $data["httpCode"]);
        $this->assertEquals("validation", $data["typ"]);
        $this->assertEquals("The email has already been taken.", $data["validation"]["email"][0]);
        $this->assertEquals(1, count($data["validation"]));
        $this->assertEquals(1, count($data["validation"]["email"]));

    }

    public function testInvalideEmail() {
        $this->withoutEvents();

        $this->post('/v2/account/register', ["name"=>"TeSTuSeRInvalide", "password"=>"adminadmin", "email" => "test@google"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("The given data was invalid.", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(400, $data["httpCode"]);
        $this->assertEquals("validation", $data["typ"]);
        $this->assertEquals("The email must be a valid email address.", $data["validation"]["email"][0]);
        $this->assertEquals(1, count($data["validation"]));
        $this->assertEquals(1, count($data["validation"]["email"]));
    }

    public function testShortUsername() {
        $this->withoutEvents();
        $this->post('/v2/account/register', ["name"=>"abc", "password"=>"adminadmin", "email" => "testinvalide@tageso.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("The given data was invalid.", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(400, $data["httpCode"]);
        $this->assertEquals("validation", $data["typ"]);
        $this->assertEquals("The name must be at least 4 characters.", $data["validation"]["name"][0]);
        $this->assertEquals(1, count($data["validation"]));
        $this->assertEquals(1, count($data["validation"]["name"]));

    }

    public function testInvalideName() {
        $this->withoutEvents();
        $this->post('/v2/account/register', ["name"=>"abökc", "password"=>"adminadmin", "email" => "testinvalide@tageso.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("The given data was invalid.", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(400, $data["httpCode"]);
        $this->assertEquals("validation", $data["typ"]);
        $this->assertEquals("The name format is invalid.", $data["validation"]["name"][0]);
        $this->assertEquals(1, count($data["validation"]));
        $this->assertEquals(1, count($data["validation"]["name"]));

    }
    public function testTooShortPassword() {
        $this->withoutEvents();
        $this->post('/v2/account/register', ["name"=>"abcuserabc", "password"=>"abc", "email" => "testinvalide@tageso.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals("The given data was invalid.", $data["msg"]);
        $this->assertEquals(0, $data["code"]);
        $this->assertEquals(400, $data["httpCode"]);
        $this->assertEquals("validation", $data["typ"]);
        $this->assertEquals("The password must be at least 8 characters.", $data["validation"]["password"][0]);
        $this->assertEquals(1, count($data["validation"]));
        $this->assertEquals(1, count($data["validation"]["password"]));

    }

    public function testEventIsFired() {
        $this->expectsEvents(App\Events\UserRegisterEvent::class);

        $this->post('/v2/account/register', ["name"=>"testuser4", "password"=>"adminadmin", "email" => "hello4@kekskurse.de"], ["accept" => "application/json"]);

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

    }


}
