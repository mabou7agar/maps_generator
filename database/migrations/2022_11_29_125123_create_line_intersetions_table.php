<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_intersections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained('lines');
            $table->foreignId('intersection_line_id')->constrained('lines');
            $table->integer('x_point');
            $table->integer('y_point');
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
        Schema::dropIfExists('line_intersetions');
    }
};
