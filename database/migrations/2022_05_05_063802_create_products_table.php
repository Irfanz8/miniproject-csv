<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string("unique_key")->nullable();
            $table->string("product_title")->nullable();
            $table->text("product_description")->nullable();
            $table->string("style#")->nullable();;
            $table->string("sanmar_mainframe_color")->nullable();
            $table->string("size")->nullable();
            $table->string("color_name")->nullable();
            $table->string("piece_price")->nullable();
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
        Schema::dropIfExists('products');
    }
}
