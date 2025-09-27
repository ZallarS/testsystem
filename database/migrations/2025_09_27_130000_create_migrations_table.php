<?php

use App\Core\Database\Schema;

class CreateMigrationsTable
{
    public function up()
    {
        Schema::create('migrations', function($table) {
            $table->id();
            $table->string('migration', 255);
            $table->integer('batch');
            $table->timestamps();

            $table->index('migration');
            $table->index('batch');
        });
    }

    public function down()
    {
        Schema::dropIfExists('migrations');
    }
}