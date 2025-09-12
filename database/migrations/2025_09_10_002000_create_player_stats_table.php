<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Player statistics table (season cumulative)
        Schema::create('player_stats', function (Blueprint $table) {
            $table->id();
            $table->integer('player_id');
            $table->integer('gameweek');
            $table->string('first_name');
            $table->string('second_name');
            $table->string('web_name');
            $table->string('status')->default('a'); // a=available, i=injured, etc.
            $table->text('news')->nullable();
            $table->timestamp('news_added')->nullable();
            $table->integer('chance_of_playing_next_round')->nullable();
            $table->integer('chance_of_playing_this_round')->nullable();
            
            // Pricing and selection
            $table->integer('now_cost');
            $table->integer('now_cost_rank')->nullable();
            $table->integer('now_cost_rank_type')->nullable();
            $table->integer('cost_change_event')->default(0);
            $table->integer('cost_change_event_fall')->default(0);
            $table->integer('cost_change_start')->default(0);
            $table->integer('cost_change_start_fall')->default(0);
            $table->decimal('selected_by_percent', 5, 2)->default(0);
            $table->integer('selected_rank')->nullable();
            $table->integer('selected_rank_type')->nullable();
            
            // Performance statistics
            $table->integer('total_points')->default(0);
            $table->integer('event_points')->default(0);
            $table->decimal('points_per_game', 5, 2)->default(0);
            $table->integer('points_per_game_rank')->nullable();
            $table->integer('points_per_game_rank_type')->nullable();
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
            $table->integer('starts')->default(0);
            $table->integer('bonus')->default(0);
            $table->integer('bps')->default(0);
            
            // Form and value metrics
            $table->decimal('form', 3, 1)->default(0);
            $table->integer('form_rank')->nullable();
            $table->integer('form_rank_type')->nullable();
            $table->decimal('value_form', 5, 2)->default(0);
            $table->decimal('value_season', 5, 2)->default(0);
            $table->integer('dreamteam_count')->default(0);
            
            // Transfer statistics
            $table->integer('transfers_in')->default(0);
            $table->integer('transfers_in_event')->default(0);
            $table->integer('transfers_out')->default(0);
            $table->integer('transfers_out_event')->default(0);
            
            // Expected statistics
            $table->decimal('ep_next', 5, 2)->nullable();
            $table->decimal('ep_this', 5, 2)->nullable();
            $table->decimal('expected_goals', 5, 2)->default(0);
            $table->decimal('expected_assists', 5, 2)->default(0);
            $table->decimal('expected_goal_involvements', 5, 2)->default(0);
            $table->decimal('expected_goals_conceded', 5, 2)->default(0);
            $table->decimal('expected_goals_per_90', 5, 2)->default(0);
            $table->decimal('expected_assists_per_90', 5, 2)->default(0);
            $table->decimal('expected_goal_involvements_per_90', 5, 2)->default(0);
            $table->decimal('expected_goals_conceded_per_90', 5, 2)->default(0);
            
            // ICT Index
            $table->decimal('influence', 5, 1)->default(0);
            $table->decimal('creativity', 5, 1)->default(0);
            $table->decimal('threat', 5, 1)->default(0);
            $table->decimal('ict_index', 5, 1)->default(0);
            $table->integer('influence_rank')->nullable();
            $table->integer('influence_rank_type')->nullable();
            $table->integer('creativity_rank')->nullable();
            $table->integer('creativity_rank_type')->nullable();
            $table->integer('threat_rank')->nullable();
            $table->integer('threat_rank_type')->nullable();
            $table->integer('ict_index_rank')->nullable();
            $table->integer('ict_index_rank_type')->nullable();
            
            // Set piece orders
            $table->integer('corners_and_indirect_freekicks_order')->nullable();
            $table->integer('direct_freekicks_order')->nullable();
            $table->integer('penalties_order')->nullable();
            $table->string('corners_and_indirect_freekicks_text')->nullable();
            $table->string('direct_freekicks_text')->nullable();
            $table->string('penalties_text')->nullable();
            
            // Enhanced metrics (per 90)
            $table->decimal('defensive_contribution', 5, 2)->default(0);
            $table->decimal('defensive_contribution_per_90', 5, 2)->default(0);
            $table->decimal('saves_per_90', 5, 2)->default(0);
            $table->decimal('clean_sheets_per_90', 5, 2)->default(0);
            $table->decimal('goals_conceded_per_90', 5, 2)->default(0);
            $table->decimal('starts_per_90', 5, 2)->default(0);
            
            $table->timestamps();
            
            // Foreign keys and indexes
            $table->foreign('player_id')->references('fpl_id')->on('players');
            $table->foreign('gameweek')->references('gameweek_id')->on('gameweeks');
            
            // Composite unique index
            $table->unique(['player_id', 'gameweek']);
            
            // Performance indexes
            $table->index(['total_points', 'gameweek']);
            $table->index(['now_cost', 'selected_by_percent']);
            $table->index(['form', 'value_season']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_stats');
    }
};
