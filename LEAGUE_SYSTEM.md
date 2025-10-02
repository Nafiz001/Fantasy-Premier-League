# Fantasy Premier League - League System Documentation

## Overview
The league system allows users to create and join leagues to compete with friends and other FPL managers, similar to the official Fantasy Premier League classic leagues.

## Features Implemented

### 1. **League Creation**
- Users can create their own custom leagues
- Generate unique 6-character league codes (like FPL: ABC123)
- Customize league settings:
  - League name and description
  - League type: Classic or Head-to-Head
  - Privacy: Public or Private
  - Maximum entries (2-100 members)

### 2. **Join Leagues**
- Join leagues using the unique league code
- Browse and join public leagues
- Automatic validation:
  - Check if league is full
  - Prevent duplicate membership
  - Verify league is active

### 3. **League Leaderboard**
- **Real-time rankings based on user's actual FPL points**
- Display member information:
  - Current rank (with trophy icons for top 3)
  - Manager name and team name
  - Total points accumulated
  - Gameweeks played
  - Average points per gameweek
- Highlight current user's position
- Identify league admin

### 4. **League Management**
- **Admin Controls:**
  - Update league name and description
  - Adjust maximum entries
  - Delete league
  
- **Member Actions:**
  - Leave league (except admin)
  - View league details and statistics

## How It Works

### Points System
Unlike some league implementations that maintain separate league points, this system uses **the user's actual FPL total points** for rankings. This means:

1. Each user has a `points` field in their user record
2. League rankings are calculated by ordering users by their total points
3. No need to sync or update separate league point tallies
4. Rankings automatically update as user points change

### Database Structure

#### Leagues Table
- `id`: Primary key
- `name`: League name
- `league_code`: Unique 6-character code
- `description`: Optional description
- `type`: classic or head_to_head
- `privacy`: public or private
- `admin_id`: User who created the league
- `max_entries`: Maximum number of members
- `current_entries`: Current member count
- `is_active`: League status

#### League Members Table (Simplified)
- `id`: Primary key
- `league_id`: Foreign key to leagues
- `user_id`: Foreign key to users
- `joined_at`: Timestamp when user joined
- `is_admin`: Boolean flag for admin status

**Note:** Removed unnecessary columns (`rank`, `total_points`, `gameweeks_played`) since we use the user's actual points directly.

## Routes

### Public Routes (Authenticated)
- `GET /leagues` - League dashboard
- `GET /leagues/create` - Create league form
- `POST /leagues/create` - Store new league
- `GET /leagues/join` - Join league form
- `POST /leagues/join` - Join league with code
- `GET /leagues/{league}` - View league details
- `DELETE /leagues/{league}` - Delete league (admin only)
- `DELETE /leagues/{league}/leave` - Leave league
- `GET /leagues/{league}/settings` - League settings (admin only)
- `PUT /leagues/{league}/settings` - Update settings (admin only)

## Usage Examples

### Creating a League
1. Navigate to `/leagues`
2. Click "Create New League"
3. Fill in league details
4. Submit form
5. League code is generated (e.g., ABC123)
6. Share code with friends

### Joining a League
1. Get league code from league admin
2. Navigate to `/leagues/join`
3. Enter the 6-character code
4. Click "Join League"
5. Automatically added to league

### Viewing Rankings
1. Navigate to any league you're a member of
2. See live leaderboard with:
   - Current rank
   - Total points
   - Average per gameweek
   - Your highlighted position

## Key Components

### Models
- `League.php` - Main league model with business logic
- `LeagueMember.php` - Pivot model for league membership
- `User.php` - Extended with league relationships

### Controllers
- `LeagueController.php` - Handles all league operations

### Views
- `leagues/index.blade.php` - League dashboard
- `leagues/create.blade.php` - Create league form
- `leagues/join.blade.php` - Join league form
- `leagues/show.blade.php` - League details and leaderboard
- `leagues/settings.blade.php` - League settings (admin)

### Middleware
- All league routes protected by `auth` middleware
- Admin-only routes check league ownership

## Technical Details

### League Code Generation
```php
public static function generateLeagueCode(): string
{
    do {
        $code = strtoupper(Str::random(6));
    } while (self::where('league_code', $code)->exists());
    
    return $code;
}
```

### Leaderboard Calculation
```php
public function getLeaderboard()
{
    return $this->members()
        ->withPivot('joined_at')
        ->orderByDesc('points') // Order by user's total points
        ->orderBy('league_members.joined_at', 'asc') // Tiebreaker
        ->get()
        ->map(function ($user, $index) {
            $user->current_rank = $index + 1;
            $user->league_joined_at = $user->pivot->joined_at;
            return $user;
        });
}
```

## Design Philosophy

### Simplicity
- Use existing user points instead of maintaining separate league points
- Minimal database overhead
- Real-time rankings without complex calculations

### Scalability
- Efficient queries with proper indexing
- Eager loading to prevent N+1 queries
- Pagination-ready structure

### User Experience
- Familiar FPL interface and terminology
- Clear visual hierarchy
- Responsive design for all devices

## Future Enhancements (Potential)

1. **Gameweek-specific rankings** - Track performance by gameweek
2. **Head-to-Head mode** - Implement H2H fixtures
3. **League cups** - Knockout competitions within leagues
4. **League chat** - Communication between members
5. **Historical data** - View past seasons
6. **Prizes and rewards** - League-specific achievements
7. **Invitations** - Email invites to join leagues

## Testing

To test the league system:

1. **Create a test league:**
   ```bash
   php test_league.php
   ```

2. **Access the leagues:**
   - Visit `http://localhost:8000/leagues`
   - Create a new league
   - Use the generated code to join

3. **Verify rankings:**
   - Ensure users are ordered by their total points
   - Check tiebreaker (joined_at) works correctly

## Maintenance

### Database Migrations
- Initial: `2025_10_02_081753_create_leagues_tables.php`
- Simplification: `2025_10_02_083628_simplify_league_members_table.php`

### Cleanup
The league system automatically:
- Deletes league members when league is deleted (cascade)
- Removes members when user is deleted (cascade)
- Validates data integrity on operations

## Support

For issues or questions about the league system:
1. Check this documentation
2. Review the code comments in the models
3. Test with sample data using `test_league.php`
4. Check Laravel logs in `storage/logs/`

---

**Last Updated:** October 2, 2025
**Version:** 1.0
**Status:** Production Ready
