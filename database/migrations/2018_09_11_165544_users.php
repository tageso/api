<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Users extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->text('name');
            $table->text('email');
            $table->text('password');
            $table->text("twoAuthSecret")->nullable()->default(NULL);
            $table->text("twoAuthBackupCode")->nullable()->default(NULL);
            $table->enum("status", ["active", "validateSend", "disabled", "deleted"]);
            $table->boolean("admin")->default(false);
            $table->boolean("developer")->default(false);
            $table->boolean("systemAccount")->default(false)->comment("Depricated");
            $table->enum("mailStatus", ["active", "validateSend", "disabled"])->comment("validationSend comes when the user disabled the mails and want to enabled it again");
            $table->text("mailToken")->nullable();
            $table->text("disabledMailsToken")->nullable();
            $table->text("old_uid")->nullable()->comment("For Migration from old API");
        });

        Schema::create("twoAuthCallange", function(Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger("user_id");

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('user_profiles', function(Blueprint $table) {
           $table->increments('id');
           $table->timestamps();
           $table->unsignedInteger("user_id");
           $table->text("username")->comment("Public Username");

           $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create("user_logins", function(Blueprint $table) {
            $table->timestamps();
            $table->unsignedInteger("user_id")->nullable();
            $table->timestamp("login");

            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create("api_keys", function (Blueprint $table) {
           $table->increments("id");
           $table->timestamps();
           $table->unsignedInteger("user_id");
           $table->text("api_token");
           $table->enum("typ", ["login", "apiToken"])->default("login");
           $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('users');
    }
}
