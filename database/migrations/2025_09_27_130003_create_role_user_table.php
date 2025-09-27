<?php

use App\Core\Database\Schema;

class CreateRoleUserTable
{
    public function up()
    {
        Schema::create('role_user', function($table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();

            // Составной первичный ключ
            $table->primary(['user_id', 'role_id']);

            // Внешние ключи
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->index('role_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_user');
    }
}