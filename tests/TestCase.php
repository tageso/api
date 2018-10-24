<?php

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->artisan("migrate:fresh");
        $this->artisan("db:seed");
    }
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }
}
