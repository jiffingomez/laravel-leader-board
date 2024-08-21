<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('winner_boards', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('leaderboard_id')->unsigned()->index();
            $table->integer('highest_score');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('leaderboard_id')->references('id')->on('leader_boards');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winner_boards');
    }
};
