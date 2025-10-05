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
        Schema::table('users', function (Blueprint $table) {
            // Add selected_squad to store all 15 players (11 starting + 4 bench)
            $table->json('selected_squad')->nullable()->after('starting_xi');

            // Add current_gameweek to track which gameweek the user is on
            $table->integer('current_gameweek')->default(1)->after('points');

            // Add gameweek_points to track points for current gameweek
            $table->integer('gameweek_points')->default(0)->after('current_gameweek');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['selected_squad', 'current_gameweek', 'gameweek_points']);
        });
    }
};
