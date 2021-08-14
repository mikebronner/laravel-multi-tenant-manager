<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTenantsTable extends Migration
{
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId("website_id");
            $table->timestamps();

            $table->string("domain");
            $table->string("logo")->nullable();
            $table->string("name");
            $table->json("settings")->nullable();

            $table->foreign("website_id")
                ->references("id")
                ->on("websites")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
        });
    }
}
