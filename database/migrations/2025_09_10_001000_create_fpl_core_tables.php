<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Teams table
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->integer('fpl_code')->unique();
            $table->integer('fpl_id')->unique();
            $table->string('name');
            $table->string('short_name');
            $table->integer('strength');
            $table->integer('strength_overall_home');
            $table->integer('strength_overall_away');
            $table->integer('strength_attack_home');
            $table->integer('strength_attack_away');
            $table->integer('strength_defence_home');
            $table->integer('strength_defence_away');
            $table->integer('pulse_id')->nullable();
            $table->decimal('elo', 8, 2)->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['fpl_id', 'fpl_code']);
        });

        // Players table
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->integer('fpl_code')->unique();
            $table->integer('fpl_id')->unique();
            $table->string('first_name');
            $table->string('second_name');
            $table->string('web_name');
            $table->integer('team_code');
            $table->string('position'); // GKP, DEF, MID, FWD
            $table->integer('element_type'); // 1=GK, 2=DEF, 3=MID, 4=FWD
            $table->timestamps();
            
            // Foreign key and indexes
            $table->foreign('team_code')->references('fpl_code')->on('teams');
            $table->index(['fpl_id', 'element_type', 'team_code']);
        });

        // Gameweeks table
        Schema::create('gameweeks', function (Blueprint $table) {
            $table->id();
            $table->integer('gameweek_id')->unique();
            $table->string('name');
            $table->timestamp('deadline_time');
            $table->bigInteger('deadline_time_epoch');
            $table->integer('deadline_time_game_offset');
            $table->decimal('average_entry_score', 5, 2)->nullable();
            $table->integer('highest_score')->nullable();
            $table->boolean('finished')->default(false);
            $table->boolean('is_previous')->default(false);
            $table->boolean('is_current')->default(false);
            $table->boolean('is_next')->default(false);
            $table->json('chip_plays')->nullable();
            $table->integer('most_selected')->nullable();
            $table->integer('most_transferred_in')->nullable();
            $table->integer('most_captained')->nullable();
            $table->integer('most_vice_captained')->nullable();
            $table->integer('top_element')->nullable();
            $table->json('top_element_info')->nullable();
            $table->integer('transfers_made')->nullable();
            $table->timestamps();
            
            $table->index(['gameweek_id', 'finished']);
        });

        // Fixtures table (upcoming matches)
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->integer('fixture_id')->unique();
            $table->integer('gameweek');
            $table->timestamp('kickoff_time');
            $table->integer('home_team');
            $table->integer('away_team');
            $table->decimal('home_team_elo', 8, 2)->nullable();
            $table->decimal('away_team_elo', 8, 2)->nullable();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->boolean('finished')->default(false);
            $table->string('tournament')->default('Premier League');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('home_team')->references('fpl_id')->on('teams');
            $table->foreign('away_team')->references('fpl_id')->on('teams');
            $table->foreign('gameweek')->references('gameweek_id')->on('gameweeks');
            
            $table->index(['gameweek', 'finished', 'tournament']);
        });

        // Matches table (completed matches)
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->integer('match_id')->unique();
            $table->integer('gameweek');
            $table->timestamp('kickoff_time');
            $table->integer('home_team');
            $table->integer('away_team');
            $table->decimal('home_team_elo', 8, 2);
            $table->decimal('away_team_elo', 8, 2);
            $table->integer('home_score');
            $table->integer('away_score');
            $table->boolean('finished')->default(true);
            $table->string('tournament')->default('Premier League');
            
            // Match statistics
            $table->decimal('home_possession', 5, 2)->nullable();
            $table->decimal('away_possession', 5, 2)->nullable();
            $table->decimal('home_expected_goals_xg', 5, 2)->nullable();
            $table->decimal('away_expected_goals_xg', 5, 2)->nullable();
            $table->integer('home_total_shots')->nullable();
            $table->integer('away_total_shots')->nullable();
            $table->integer('home_shots_on_target')->nullable();
            $table->integer('away_shots_on_target')->nullable();
            $table->integer('home_big_chances')->nullable();
            $table->integer('away_big_chances')->nullable();
            $table->integer('home_big_chances_missed')->nullable();
            $table->integer('away_big_chances_missed')->nullable();
            $table->integer('home_accurate_passes')->nullable();
            $table->integer('away_accurate_passes')->nullable();
            $table->decimal('home_accurate_passes_pct', 5, 2)->nullable();
            $table->decimal('away_accurate_passes_pct', 5, 2)->nullable();
            $table->integer('home_fouls_committed')->nullable();
            $table->integer('away_fouls_committed')->nullable();
            $table->integer('home_corners')->nullable();
            $table->integer('away_corners')->nullable();
            $table->decimal('home_xg_open_play', 5, 2)->nullable();
            $table->decimal('away_xg_open_play', 5, 2)->nullable();
            $table->decimal('home_xg_set_play', 5, 2)->nullable();
            $table->decimal('away_xg_set_play', 5, 2)->nullable();
            $table->integer('home_yellow_cards')->nullable();
            $table->integer('away_yellow_cards')->nullable();
            $table->integer('home_red_cards')->nullable();
            $table->integer('away_red_cards')->nullable();
            $table->integer('home_tackles_won')->nullable();
            $table->integer('away_tackles_won')->nullable();
            $table->integer('home_interceptions')->nullable();
            $table->integer('away_interceptions')->nullable();
            $table->integer('home_blocks')->nullable();
            $table->integer('away_blocks')->nullable();
            $table->integer('home_clearances')->nullable();
            $table->integer('away_clearances')->nullable();
            $table->integer('home_keeper_saves')->nullable();
            $table->integer('away_keeper_saves')->nullable();
            $table->boolean('stats_processed')->default(false);
            $table->boolean('player_stats_processed')->default(false);
            $table->timestamps();
            
            // Foreign keys and indexes
            $table->foreign('home_team')->references('fpl_id')->on('teams');
            $table->foreign('away_team')->references('fpl_id')->on('teams');
            $table->foreign('gameweek')->references('gameweek_id')->on('gameweeks');
            
            $table->index(['gameweek', 'tournament']);
            $table->index(['home_team', 'away_team']);
            $table->index(['kickoff_time', 'finished']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('matches');
        Schema::dropIfExists('fixtures');
        Schema::dropIfExists('gameweeks');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
    }
};
