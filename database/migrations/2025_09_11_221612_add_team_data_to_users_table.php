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
            $table->decimal('points', 8, 1)->default(0)->after('budget_remaining');
            $table->integer('free_transfers')->default(1)->after('points');
            $table->string('active_chip')->nullable()->after('free_transfers');
            $table->json('used_chips')->nullable()->after('active_chip'); // Array of used chips
            $table->json('starting_xi')->nullable()->after('used_chips'); // Array of player IDs
            $table->integer('captain_id')->nullable()->after('starting_xi');
            $table->integer('vice_captain_id')->nullable()->after('captain_id');
            $table->string('formation')->default('4-4-2')->after('vice_captain_id');
            $table->integer('gameweek')->default(1)->after('formation');
            $table->boolean('squad_completed')->default(false)->after('gameweek');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'points',
                'free_transfers',
                'active_chip',
                'used_chips',
                'starting_xi',
                'captain_id',
                'vice_captain_id',
                'formation',
                'gameweek',
                'squad_completed'
            ]);
        });
    }
};
