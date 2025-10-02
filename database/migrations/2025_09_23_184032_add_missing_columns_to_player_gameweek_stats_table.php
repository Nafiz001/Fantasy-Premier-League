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
        Schema::table('player_gameweek_stats', function (Blueprint $table) {
            // ICT Index components
            $table->decimal('influence', 5, 1)->default(0);
            $table->decimal('creativity', 5, 1)->default(0);
            $table->decimal('threat', 5, 1)->default(0);
            $table->decimal('ict_index', 5, 1)->default(0);

            // Expected statistics
            $table->decimal('expected_goals', 5, 2)->default(0);
            $table->decimal('expected_assists', 5, 2)->default(0);
            $table->decimal('expected_goal_involvements', 5, 2)->default(0);
            $table->decimal('expected_goals_conceded', 5, 2)->default(0);

            // Additional performance stats
            $table->integer('starts')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_gameweek_stats', function (Blueprint $table) {
            $table->dropColumn([
                'influence',
                'creativity',
                'threat',
                'ict_index',
                'expected_goals',
                'expected_assists',
                'expected_goal_involvements',
                'expected_goals_conceded',
                'starts'
            ]);
        });
    }
};
