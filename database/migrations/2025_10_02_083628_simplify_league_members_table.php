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
        Schema::table('league_members', function (Blueprint $table) {
            // Drop unnecessary columns since we use user's points directly
            $table->dropColumn(['rank', 'total_points', 'gameweeks_played']);
        });

        Schema::dropIfExists('league_gameweek_rankings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('league_members', function (Blueprint $table) {
            $table->integer('rank')->nullable();
            $table->integer('total_points')->default(0);
            $table->integer('gameweeks_played')->default(0);
        });

        Schema::create('league_gameweek_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('gameweek');
            $table->integer('points')->default(0);
            $table->integer('rank')->nullable();
            $table->integer('total_points')->default(0);
            $table->integer('overall_rank')->nullable();
            $table->boolean('played')->default(false);
            $table->timestamps();

            $table->unique(['league_id', 'user_id', 'gameweek']);
        });
    }
};
