<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInformativeProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('informative_products', function (Blueprint $table) {
            $table->id();
            $table->string('title', 128);
            $table->string('image', 255);
            $table->string('description', 1500)->nullable();
            $table->integer('price')->unsigned();
            $table->string('category_id');
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
        Schema::dropIfExists('informative_products');
    }
}
