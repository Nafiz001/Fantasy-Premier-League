# League Points System Explanation

## Overview
The Fantasy Premier League league system displays user statistics based on their actual FPL performance. Here's how the data works:

## Data Fields

### 1. **Current GW** (Gameweek)
- **Source**: `users.gameweek` column
- **Meaning**: The current gameweek the user is participating in
- **Example**: If a user is in GW1, they show "1"
- **Note**: This represents the gameweek number, not how many gameweeks they've played

### 2. **Total Points**
- **Source**: `users.points` column
- **Meaning**: The cumulative points the user has earned across all gameweeks
- **Example**: If a user has 0 points, they haven't scored yet (new team or start of season)
- **Updates**: Points accumulate as gameweeks progress and player performances are calculated

### 3. **Avg/GW** (Average per Gameweek)
- **Calculation**: `Total Points ÷ Current GW`
- **Example**: 
  - User in GW5 with 250 points → 250 ÷ 5 = 50.0 avg/GW
  - User in GW1 with 0 points → 0 ÷ 1 = 0.0 avg/GW
- **Purpose**: Shows consistency of performance

## Why Shows 0 Points?

If you see a user with:
- **Current GW**: 1
- **Total Points**: 0
- **Avg/GW**: 0.0

This is **completely normal** and means:
1. The user has just created their squad
2. Gameweek 1 hasn't been processed yet, or
3. The user's players haven't scored any points yet

## How Points Accumulate

Points are calculated and added to users through:

1. **Player Performance**: 
   - Goals, assists, clean sheets, bonus points, etc.
   - Stored in `player_gameweek_stats` table

2. **Squad Composition**:
   - User's starting XI (stored in `users.starting_xi` as JSON)
   - Captain gets double points
   - Vice-captain backs up if captain doesn't play

3. **Gameweek Processing**:
   - FPL data is imported via API
   - Points are calculated based on user's squad
   - `users.points` field is updated with cumulative total

## League Rankings

Users in leagues are ranked by:
1. **Primary**: Total Points (descending)
2. **Tiebreaker**: Join Date (earlier join wins)

The league uses **actual user points** from the users table, not separate league-specific points. This ensures:
- Real-time accuracy
- No sync issues between user points and league points
- Simpler data structure
- Better performance

## Testing League Points

To see the league system with actual points:

1. **Import FPL Data**:
   ```bash
   php artisan fpl:import-all
   ```

2. **Process Gameweek**:
   - Import player performances
   - Calculate user points based on their squads
   - Points automatically appear in leagues

3. **View Updated Rankings**:
   - Visit any league
   - See users ranked by their total points
   - Average per GW updates automatically

## Expected Behavior

### Start of Season (Current State)
- All users: 0 points
- Current GW: 1
- Avg/GW: 0.0
- **This is correct!**

### After GW1 Completes
- Users: Various points (50-80 typical)
- Current GW: 1
- Avg/GW: Same as total (only 1 GW played)

### Mid-Season (e.g., GW10)
- Users: 400-700 points (typical range)
- Current GW: 10
- Avg/GW: 40-70 points per week

## Summary

✅ The league system is **working correctly**
✅ 0 points at GW1 is **expected behavior**  
✅ Points will populate as gameweeks are processed
✅ Rankings update automatically based on user performance

The current display showing:
- **Current GW**: 1
- **Total Points**: 0  
- **Avg/GW**: 0.0

...is **exactly correct** for a new league at the start of the season!
