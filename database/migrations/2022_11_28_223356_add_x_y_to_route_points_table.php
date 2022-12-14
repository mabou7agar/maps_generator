<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('route_points', function (Blueprint $table) {
            $table->integer('screen_width')->default(142);
            $table->integer('screen_height')->default(368);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('route_points', function (Blueprint $table) {
            //
        });
    }
};
