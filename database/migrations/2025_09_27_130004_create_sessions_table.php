<?php

use App\Core\Database\Schema;

class CreateSessionsTable
{
    public function up()
    {
        Schema::create('sessions', function($table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index('last_activity');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}