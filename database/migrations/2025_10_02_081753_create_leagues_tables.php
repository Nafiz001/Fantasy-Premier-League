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
        // Leagues table
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('league_code', 8)->unique(); // Unique 8-character code
            $table->text('description')->nullable();
            $table->enum('type', ['classic', 'head_to_head'])->default('classic');
            $table->enum('privacy', ['public', 'private'])->default('private');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->integer('max_entries')->default(50); // Max number of participants
            $table->integer('current_entries')->default(1); // Current participants count
            $table->boolean('is_active')->default(true);
            $table->timestamp('start_gameweek')->nullable();
            $table->timestamp('end_gameweek')->nullable();
            $table->timestamps();

            $table->index(['league_code', 'is_active']);
            $table->index(['admin_id', 'type']);
        });

        // League Members table (many-to-many relationship)
        Schema::create('league_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('joined_at')->useCurrent();
            $table->boolean('is_admin')->default(false);
            $table->integer('rank')->nullable(); // Current rank in league
            $table->integer('total_points')->default(0); // Total points across all gameweeks
            $table->integer('gameweeks_played')->default(0);
            $table->timestamps();

            // Ensure unique user per league
            $table->unique(['league_id', 'user_id']);
            $table->index(['league_id', 'rank']);
            $table->index(['user_id', 'total_points']);
        });

        // League Gameweek Rankings (track performance per gameweek)
        Schema::create('league_gameweek_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('gameweek');
            $table->integer('points')->default(0); // Points scored this gameweek
            $table->integer('rank')->nullable(); // Rank this gameweek
            $table->integer('total_points')->default(0); // Running total points
            $table->integer('overall_rank')->nullable(); // Overall rank after this gameweek
            $table->boolean('played')->default(false); // Whether user played this gameweek
            $table->timestamps();

            $table->unique(['league_id', 'user_id', 'gameweek']);
            $table->index(['league_id', 'gameweek', 'points']);
            $table->index(['league_id', 'overall_rank']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_gameweek_rankings');
        Schema::dropIfExists('league_members');
        Schema::dropIfExists('leagues');
    }
};
