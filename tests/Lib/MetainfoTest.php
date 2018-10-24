<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class MetainfoTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCommitHashWithFileExists()
    {
        file_put_contents(__DIR__."/../../commit.info", "test");

        $metaInfo = new \App\Lib\Metainfo();

        $commit = $metaInfo->getCommitHash();

        $this->assertEquals("test", $commit);

        unlink(__DIR__."/../../commit.info");
    }

    public function testCommitHashWithoutFile() {
        $metaInfo = new \App\Lib\Metainfo();
        $commit = $metaInfo->getCommitHash();
        $this->assertEquals("dev", $commit);
    }


}
