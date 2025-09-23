<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FPLFixtureService
{
    private const FPL_BASE_URL = 'https://fantasy.premierleague.com/api';

    /**
     * Fetch and update all fixtures from FPL API
     */
    public function updateFixturesFromAPI(): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'fixtures_updated' => 0,
            'errors' => []
        ];

        try {
            echo "🌐 Fetching fixtures from official FPL API...\n";

            // Get fixtures data
            $fixturesResponse = Http::timeout(30)->get(self::FPL_BASE_URL . '/fixtures/');

            if (!$fixturesResponse->successful()) {
                throw new \Exception("Failed to fetch fixtures data: " . $fixturesResponse->status());
            }

            $fixturesData = $fixturesResponse->json();
            echo "✅ Fetched " . count($fixturesData) . " fixtures from FPL API\n";

            // Get teams data for mapping
            $bootstrapResponse = Http::timeout(30)->get(self::FPL_BASE_URL . '/bootstrap-static/');
            $teamsData = $bootstrapResponse->json()['teams'] ?? [];
            $teamMap = [];

            foreach ($teamsData as $team) {
                $teamMap[$team['id']] = $team;
            }

            echo "✅ Loaded " . count($teamMap) . " teams data\n";

            // Clear existing fixtures and insert new ones
            echo "🧹 Clearing existing fixtures...\n";
            DB::table('fixtures')->truncate();

            $fixturesInserted = $this->insertFixturesFromAPI($fixturesData, $teamMap);

            $result['success'] = true;
            $result['message'] = "Successfully updated fixtures from FPL API";
            $result['fixtures_updated'] = $fixturesInserted;

        } catch (\Exception $e) {
            $result['message'] = "Error: " . $e->getMessage();
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Insert fixtures from FPL API data
     */
    private function insertFixturesFromAPI(array $fixturesData, array $teamMap): int
    {
        $inserted = 0;
        $batchSize = 50;
        $batch = [];

        echo "📅 Processing fixtures...\n";

        foreach ($fixturesData as $fixture) {
            try {
                // Skip fixtures without gameweek (these are usually past seasons)
                if (!isset($fixture['event']) || $fixture['event'] === null) {
                    continue;
                }

                $homeTeam = $teamMap[$fixture['team_h']] ?? null;
                $awayTeam = $teamMap[$fixture['team_a']] ?? null;

                if (!$homeTeam || !$awayTeam) {
                    continue;
                }

                // Parse kickoff time
                $kickoffTime = null;
                if (!empty($fixture['kickoff_time'])) {
                    try {
                        $kickoffTime = Carbon::parse($fixture['kickoff_time']);
                    } catch (\Exception $e) {
                        $kickoffTime = now(); // Fallback
                    }
                }

                $fixtureData = [
                    'fixture_id' => $fixture['id'],
                    'gameweek' => $fixture['event'],
                    'kickoff_time' => $kickoffTime,
                    'home_team' => $fixture['team_h'],
                    'away_team' => $fixture['team_a'],
                    'home_score' => $fixture['team_h_score'],
                    'away_score' => $fixture['team_a_score'],
                    'finished' => $fixture['finished'],
                    'tournament' => 'Premier League',
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $batch[] = $fixtureData;

                // Insert in batches
                if (count($batch) >= $batchSize) {
                    DB::table('fixtures')->insert($batch);
                    $inserted += count($batch);
                    $batch = [];

                    if ($inserted % 100 == 0) {
                        echo "   → Inserted $inserted fixtures so far...\n";
                    }
                }

            } catch (\Exception $e) {
                echo "   ⚠️  Error processing fixture {$fixture['id']}: " . $e->getMessage() . "\n";
                continue;
            }
        }

        // Insert remaining batch
        if (!empty($batch)) {
            DB::table('fixtures')->insert($batch);
            $inserted += count($batch);
        }

        echo "✅ Inserted $inserted fixtures total\n";

        // Show summary by gameweek
        $this->showFixtureSummary();

        return $inserted;
    }

    /**
     * Show fixture summary by gameweek
     */
    private function showFixtureSummary(): void
    {
        echo "\n📊 Fixture summary by gameweek:\n";

        $summary = DB::table('fixtures')
            ->selectRaw('gameweek, COUNT(*) as count, SUM(finished) as finished')
            ->groupBy('gameweek')
            ->orderBy('gameweek')
            ->get();

        $currentDate = now();
        $nextGameweek = null;

        foreach ($summary as $gw) {
            $status = "";
            if ($gw->finished == $gw->count) {
                $status = " ✅ (Complete)";
            } elseif ($gw->finished > 0) {
                $status = " 🟡 (In Progress)";
            } else {
                $status = " ⏳ (Upcoming)";
                if (!$nextGameweek) {
                    $nextGameweek = $gw->gameweek;
                }
            }

            echo "GW{$gw->gameweek}: {$gw->count} fixtures ({$gw->finished} finished){$status}\n";
        }

        if ($nextGameweek) {
            echo "\n🎯 Current/Next gameweek: GW{$nextGameweek}\n";
        }

        $totalFixtures = $summary->sum('count');
        $totalFinished = $summary->sum('finished');
        echo "\n📈 Total: {$totalFixtures} fixtures ({$totalFinished} finished)\n";
    }

    /**
     * Update fixture table structure if needed
     */
    public function updateFixtureTableStructure(): void
    {
        echo "🔧 Updating fixture table structure...\n";

        // Add missing columns if they don't exist
        $columns = [
            'home_team_name' => "ALTER TABLE fixtures ADD COLUMN home_team_name VARCHAR(255)",
            'away_team_name' => "ALTER TABLE fixtures ADD COLUMN away_team_name VARCHAR(255)",
            'finished_provisional' => "ALTER TABLE fixtures ADD COLUMN finished_provisional BOOLEAN DEFAULT FALSE",
            'provisional_start_time' => "ALTER TABLE fixtures ADD COLUMN provisional_start_time BOOLEAN DEFAULT FALSE",
            'started' => "ALTER TABLE fixtures ADD COLUMN started BOOLEAN DEFAULT FALSE"
        ];

        foreach ($columns as $column => $sql) {
            try {
                // Check if column exists
                $exists = DB::select("PRAGMA table_info(fixtures)");
                $columnExists = false;

                foreach ($exists as $col) {
                    if ($col->name === $column) {
                        $columnExists = true;
                        break;
                    }
                }

                if (!$columnExists) {
                    DB::statement($sql);
                    echo "   ✅ Added column: $column\n";
                }

            } catch (\Exception $e) {
                echo "   ⚠️  Error adding column $column: " . $e->getMessage() . "\n";
            }
        }
    }
}
