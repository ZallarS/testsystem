<?php

use App\Core\Database\Schema;

class CreateRolesTable
{
    public function up()
    {
        Schema::create('roles', function($table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
}