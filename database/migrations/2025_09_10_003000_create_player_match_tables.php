<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Player match statistics table (individual match performance)
        Schema::create('player_match_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('player_id');
            $table->integer('match_id');
            $table->integer('start_min')->default(0);
            $table->integer('finish_min')->nullable();
            $table->integer('minutes_played')->default(0);
            
            // Basic performance
            $table->integer('goals')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('penalties_scored')->default(0);
            $table->integer('penalties_missed')->default(0);
            $table->integer('total_shots')->default(0);
            $table->integer('shots_on_target')->default(0);
            $table->integer('big_chances_missed')->default(0);
            
            // Expected statistics
            $table->decimal('xg', 5, 2)->default(0);
            $table->decimal('xa', 5, 2)->default(0);
            $table->decimal('xgot', 5, 2)->default(0);
            
            // Passing and creativity
            $table->integer('touches')->default(0);
            $table->integer('touches_opposition_box')->default(0);
            $table->integer('accurate_passes')->default(0);
            $table->decimal('accurate_passes_percent', 5, 2)->default(0);
            $table->integer('chances_created')->default(0);
            $table->integer('final_third_passes')->default(0);
            $table->integer('accurate_crosses')->default(0);
            $table->decimal('accurate_crosses_percent', 5, 2)->default(0);
            $table->integer('accurate_long_balls')->default(0);
            $table->decimal('accurate_long_balls_percent', 5, 2)->default(0);
            
            // Dribbling and movement
            $table->integer('successful_dribbles')->default(0);
            $table->decimal('successful_dribbles_percent', 5, 2)->default(0);
            $table->integer('dribbled_past')->default(0);
            
            // Defensive actions (CBIT)
            $table->integer('tackles')->default(0);
            $table->integer('tackles_won')->default(0);
            $table->decimal('tackles_won_percent', 5, 2)->default(0);
            $table->integer('interceptions')->default(0);
            $table->integer('recoveries')->default(0);
            $table->integer('blocks')->default(0);
            $table->integer('clearances')->default(0);
            $table->integer('headed_clearances')->default(0);
            
            // Duels
            $table->integer('duels_won')->default(0);
            $table->integer('duels_lost')->default(0);
            $table->integer('ground_duels_won')->default(0);
            $table->decimal('ground_duels_won_percent', 5, 2)->default(0);
            $table->integer('aerial_duels_won')->default(0);
            $table->decimal('aerial_duels_won_percent', 5, 2)->default(0);
            
            // Disciplinary
            $table->integer('was_fouled')->default(0);
            $table->integer('fouls_committed')->default(0);
            $table->integer('offsides')->default(0);
            $table->integer('yellow_cards')->default(0);
            $table->integer('red_cards')->default(0);
            
            // Goalkeeper specific
            $table->integer('saves')->default(0);
            $table->integer('goals_conceded')->default(0);
            $table->integer('team_goals_conceded')->default(0);
            $table->decimal('xgot_faced', 5, 2)->default(0);
            $table->integer('goals_prevented')->default(0);
            $table->integer('sweeper_actions')->default(0);
            $table->integer('high_claim')->default(0);
            $table->integer('gk_accurate_passes')->default(0);
            $table->integer('gk_accurate_long_balls')->default(0);
            
            // FPL specific
            $table->integer('bonus_points')->default(0);
            $table->integer('bps')->default(0);
            $table->integer('total_points')->default(0);
            $table->boolean('clean_sheet')->default(false);
            $table->integer('own_goals')->default(0);
            
            $table->timestamps();
            
            // Foreign keys and indexes
            $table->foreign('player_id')->references('fpl_id')->on('players');
            $table->foreign('match_id')->references('match_id')->on('matches');
            
            // Composite unique index
            $table->unique(['player_id', 'match_id']);
            
            // Performance indexes
            $table->index(['total_points', 'minutes_played']);
            $table->index(['goals', 'assists', 'bonus_points']);
            $table->index(['xg', 'xa', 'xgot']);
            $table->index(['tackles', 'interceptions', 'blocks', 'clearances']); // CBIT index
        });

        // Player gameweek statistics table (discrete weekly performance)
        Schema::create('player_gameweek_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('player_id');
            $table->integer('gameweek');
            $table->integer('fixture_id')->nullable();
            
            // Basic performance (discrete for gameweek)
            $table->integer('minutes')->default(0);
            $table->integer('goals_scored')->default(0);
            $table->integer('assists')->default(0);
            $table->integer('clean_sheets')->default(0);
            $table->integer('goals_conceded')->default(0);
            $table->integer('own_goals')->default(0);
            $table->integer('penalties_saved')->default(0);
            $table->integer('penalties_missed')->default(0);
            $table->integer('yellow_cards')->default(0);
            $table->integer('red_cards')->default(0);
            $table->integer('saves')->default(0);
            $table->integer('bonus')->default(0);
            $table->integer('bps')->default(0);
            $table->integer('total_points')->default(0);
            $table->boolean('was_home')->default(false);
            $table->integer('round')->nullable();
            
            // Extended stats for BPS calculation
            $table->integer('key_passes')->default(0);
            $table->integer('successful_dribbles')->default(0);
            $table->integer('tackles')->default(0);
            $table->integer('interceptions')->default(0);
            $table->integer('clearances')->default(0);
            $table->integer('recoveries')->default(0);
            $table->integer('blocks')->default(0);
            $table->integer('big_chances_created')->default(0);
            $table->integer('errors_leading_to_goal')->default(0);
            $table->integer('errors_leading_to_attempt')->default(0);
            $table->integer('fouls')->default(0);
            $table->integer('offsides')->default(0);
            
            $table->timestamps();
            
            // Foreign keys and indexes
            $table->foreign('player_id')->references('fpl_id')->on('players');
            $table->foreign('gameweek')->references('gameweek_id')->on('gameweeks');
            
            // Composite unique index
            $table->unique(['player_id', 'gameweek']);
            
            // Performance indexes
            $table->index(['total_points', 'gameweek']);
            $table->index(['bonus', 'bps']);
            $table->index(['minutes', 'goals_scored', 'assists']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_gameweek_stats');
        Schema::dropIfExists('player_match_stats');
    }
};
