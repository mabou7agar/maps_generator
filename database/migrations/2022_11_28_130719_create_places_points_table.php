<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('places_points', function (Blueprint $table) {
            $table->id();
            $table->integer('x_point');
            $table->integer('y_point');
            $table->integer('floor');
            $table->string('name');
            $table->string('code');
            $table->enum(
                'type',
                [
                    'ELEVATOR',
                    'SHOP',
                    'FEMALE_TOILET',
                    'MALE_TOILET',
                    'HANDICAP_TOILET',
                    'FIRE_ESCAPE',
                    'ATM',
                    'PANO_ELEVATOR',
                    'ESCALATOR',
                    'STAIRS'
                ]
            )->default('SHOP');
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
        Schema::dropIfExists('places_points');
    }
};
