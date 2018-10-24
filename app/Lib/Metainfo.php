<?php
namespace App\Lib;

class Metainfo {
    public function getCommitHash() {
        if(!file_exists(__DIR__."/../../commit.info")) {
            return "dev";
        }
        $commitHash = file_get_contents(__DIR__."/../../commit.info");
        return $commitHash;
    }
}