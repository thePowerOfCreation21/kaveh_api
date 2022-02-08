<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('last_name', 64);
            $table->string('phone_number')->unique();
            $table->string('second_phone_number')->nullable();
            $table->string('password');
            $table->json('one_time_password')->nullable();
            $table->string('area')->nullable();
            $table->string('card_number', 16)->nullable();
            $table->boolean('should_change_password')->default(false);
            $table->boolean('is_blocked')->default(0);
            $table->string('reason_for_blocking')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
