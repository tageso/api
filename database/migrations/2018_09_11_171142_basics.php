<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Basics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('organisations', function(Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger("user_id");
            $table->text("name");
            $table->boolean("public")->default(True)->comment("Gueast can see to and protocols");
            $table->text("url")->nullable()->default(null)->comment("Organisation avalible add tageso/p/-url- and tageso/p/-id-, must contain one letter");
            $table->enum("status", ["active", "deleted"])->default("active");
            $table->foreign('user_id')->references('id')->on('users');
            $table->text("old_uid")->nullable()->comment("For Migration from old API");
        });

        Schema::create('user_organisations', function(Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger("user_id");
            $table->unsignedInteger("organisation_id");
            $table->boolean("access")->default(false)->comment("See the Planed Agenda");
            $table->boolean("read")->default(false)->comment("Can Read Agenda and Protocols");
            $table->boolean("comment")->default(false)->comment("User has permission to create Comments");
            $table->boolean("edit")->default(false)->comment("User can edit the Items and Categories of the Agenda");
            $table->boolean("protocol")->default(false)->comment("User can write a Protocol");
            $table->boolean("admin")->default(false)->comment("User can manage this Organisation");
            $table->boolean("new")->default(false)->comment("New Access Request");
            $table->boolean("notification_protocol")->default(false)->comment("User get a E-Mail for a new Protocol");
            $table->text("old_uid")->nullable()->comment("For Migration from old API");

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('organisation_id')->references('id')->on('organisations');
        });

        Schema::create('categories', function(Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger("user_id");
            $table->unsignedInteger("organisation_id");
            $table->text("name");
            $table->integer("position");
            $table->enum("status", ["active", "deleted"]);
            $table->text("old_uid")->nullable()->default(null)->comment("For Migration from old API");

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('organisation_id')->references('id')->on('organisations');
        });

        Schema::create('items', function(Blueprint $table) {
           $table->increments('id');
           $table->timestamps();
           $table->unsignedInteger("user_id");
           $table->unsignedInteger("category_id");
           #$table->boolean("aktive")->default(1);
           $table->enum("status", ["active", "closed", "deleted"]);
           $table->text("name");
           $table->text("description")->nullable()->default(null);
           $table->integer("position")->comment("Position in the current Categorie");
           $table->text("old_uid")->nullable()->comment("For Migration from old API");


           $table->foreign('user_id')->references('id')->on('users');
           $table->foreign('category_id')->references('id')->on('categories');
        });

        Schema::create("protocols", function(Blueprint $table) {
           $table->increments('id');
            $table->timestamps();
           $table->unsignedInteger("user_id");
            $table->unsignedInteger("user_closed")->nullable()->default(null);
           $table->unsignedInteger("organisation_id");
           $table->enum("status", ["open", "closed", "canceled"])->default("open");
           $table->timestamp("start")->nullable();
           $table->timestamp("ende")->nullable()->comment("If it null, the protocol is still open");
            $table->text("old_uid")->nullable()->comment("For Migration from old API");


           $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('user_closed')->references('id')->on('users');
           $table->foreign('organisation_id')->references('id')->on('organisations');
        });

        Schema::create("protocol_items", function(Blueprint $table) {
           $table->increments('id');
            $table->timestamps();
           $table->unsignedInteger("user_id");
           $table->unsignedInteger("protocol_id");
           $table->unsignedInteger("item_id");
           $table->longText("description");
           $table->boolean("markedAsClosed");
            $table->text("old_uid")->nullable()->comment("For Migration from old API");

           $table->foreign('user_id')->references('id')->on('users');
           $table->foreign('protocol_id')->references('id')->on('protocols');
           $table->foreign('item_id')->references('id')->on('items');
        });

        Schema::create("comments", function(Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger("item_id");
            $table->unsignedInteger("user_id");
            $table->longText("text");
            $table->text("old_uid")->nullable()->comment("For Migration from old API");

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('item_id')->references('id')->on('items');

        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('protocol_items');
        Schema::dropIfExists('protocols');
        Schema::dropIfExists('items');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('organisations');
    }
}
