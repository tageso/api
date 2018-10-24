<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->get('/info');

        $data = \GuzzleHttp\json_decode($this->response->getContent(), true);

        $this->assertEquals($data["lumen"], $this->app->version());
    }
}
