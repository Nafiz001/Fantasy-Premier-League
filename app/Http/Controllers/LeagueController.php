<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\League;
use App\Models\LeagueMember;
use App\Models\User;

class LeagueController extends Controller
{
    /**
     * Display leagues dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's leagues with member count
        $myLeagues = $user->leagues()
            ->with('admin')
            ->withCount('members')
            ->get();

        // Get public leagues the user can join
        $publicLeagues = League::where('privacy', 'public')
            ->where('is_active', true)
            ->whereDoesntHave('members', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('current_entries', '<', 'max_entries')
            ->with('admin')
            ->latest()
            ->take(10)
            ->get();

        return view('leagues.index', compact('myLeagues', 'publicLeagues'));
    }

    /**
     * Show create league form
     */
    public function create()
    {
        return view('leagues.create');
    }

    /**
     * Store new league
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:classic,head_to_head',
            'privacy' => 'required|in:public,private',
            'max_entries' => 'required|integer|min:2|max:100'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();

        // Create league
        $league = League::create([
            'name' => $request->name,
            'league_code' => League::generateLeagueCode(),
            'description' => $request->description,
            'type' => $request->type,
            'privacy' => $request->privacy,
            'admin_id' => $user->id,
            'max_entries' => $request->max_entries,
            'current_entries' => 1,
            'is_active' => true
        ]);

        // Add creator as first member and admin
        LeagueMember::create([
            'league_id' => $league->id,
            'user_id' => $user->id,
            'joined_at' => now(),
            'is_admin' => true
        ]);

        return redirect()->route('leagues.show', $league)
            ->with('success', "League '{$league->name}' created successfully! League code: {$league->league_code}");
    }

    /**
     * Show specific league
     */
    public function show(League $league)
    {
        $user = Auth::user();

        // Check if user is member
        $isMember = $league->hasMember($user->id);
        $isAdmin = $league->admin_id === $user->id;

        // Get leaderboard (users ordered by their total points)
        $leaderboard = $league->getLeaderboard();

        // Get user's rank if member
        $userRank = null;
        if ($isMember) {
            $userMember = $leaderboard->firstWhere('id', $user->id);
            $userRank = $userMember ? $userMember->current_rank : null;
        }

        return view('leagues.show', compact(
            'league',
            'leaderboard',
            'isMember',
            'isAdmin',
            'userRank'
        ));
    }

    /**
     * Show join league form
     */
    public function join()
    {
        return view('leagues.join');
    }

    /**
     * Join league with code
     */
    public function joinWithCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'league_code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $leagueCode = strtoupper($request->league_code);

        // Find league
        $league = League::where('league_code', $leagueCode)
            ->where('is_active', true)
            ->first();

        if (!$league) {
            return back()->withErrors(['league_code' => 'League not found or inactive.']);
        }

        // Check if already member
        if ($league->hasMember($user->id)) {
            return redirect()->route('leagues.show', $league)
                ->with('info', 'You are already a member of this league.');
        }

        // Check if league is full
        if ($league->isFull()) {
            return back()->withErrors(['league_code' => 'This league is full.']);
        }

        // Add member
        if ($league->addMember($user->id)) {
            return redirect()->route('leagues.show', $league)
                ->with('success', "Successfully joined '{$league->name}'!");
        }

        return back()->withErrors(['league_code' => 'Failed to join league.']);
    }

    /**
     * Leave league
     */
    public function leave(League $league)
    {
        $user = Auth::user();

        // Can't leave if admin (need to transfer ownership first)
        if ($league->admin_id === $user->id) {
            return back()->withErrors(['error' => 'League admin cannot leave. Transfer ownership first.']);
        }

        if ($league->removeMember($user->id)) {
            return redirect()->route('leagues.index')
                ->with('success', "Left '{$league->name}' successfully.");
        }

        return back()->withErrors(['error' => 'Failed to leave league.']);
    }

    /**
     * Delete league (admin only)
     */
    public function destroy(League $league)
    {
        $user = Auth::user();

        if ($league->admin_id !== $user->id) {
            return back()->withErrors(['error' => 'Only league admin can delete the league.']);
        }

        $leagueName = $league->name;
        $league->delete();

        return redirect()->route('leagues.index')
            ->with('success', "League '{$leagueName}' deleted successfully.");
    }

    /**
     * League settings (admin only)
     */
    public function settings(League $league)
    {
        $user = Auth::user();

        if ($league->admin_id !== $user->id) {
            abort(403, 'Only league admin can access settings.');
        }

        return view('leagues.settings', compact('league'));
    }

    /**
     * Update league settings
     */
    public function updateSettings(Request $request, League $league)
    {
        $user = Auth::user();

        if ($league->admin_id !== $user->id) {
            abort(403, 'Only league admin can update settings.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'max_entries' => 'required|integer|min:' . $league->current_entries . '|max:100'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $league->update([
            'name' => $request->name,
            'description' => $request->description,
            'max_entries' => $request->max_entries
        ]);

        return back()->with('success', 'League settings updated successfully.');
    }
}
