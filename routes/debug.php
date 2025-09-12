<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Debug route to check teams data
Route::get('/debug/teams', function () {
    $teams = DB::table('teams')
        ->select('fpl_code', 'name', 'short_name')
        ->orderBy('fpl_code')
        ->get();
    
    return response()->json($teams);
});

// Debug route to check players with team data
Route::get('/debug/players', function () {
    $players = DB::table('players')
        ->join('teams', 'players.team_code', '=', 'teams.fpl_code')
        ->select('players.web_name', 'players.position', 'teams.name as team_name', 'teams.fpl_code')
        ->limit(20)
        ->get();
    
    return response()->json($players);
});