<?php

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{

    static private $hasSetup = false;
    public function setUp()
    {
        parent::setUp();
        if(!self::$hasSetup)
        {
            $this->artisan("migrate:fresh");
            $this->artisan("db:seed");
            self::$hasSetup = true;
        }

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
