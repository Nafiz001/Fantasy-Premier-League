<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fixtures & Results - Fantasy Premier League</title>

    @php
        use App\Services\TeamLogoService;
    @endphp

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'fpl-purple': '#38003c',
                        'fpl-magenta': '#e90052',
                        'fpl-green': '#00ff85',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #38003c 0%, #e90052 50%, #00ff85 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation Header -->
    @include('partials.navigation')

    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Fixtures & Results</h1>

            <!-- Filter Tabs -->
            <div class="flex space-x-4 border-b">
                <button class="px-4 py-2 text-fpl-purple border-b-2 border-fpl-purple font-semibold">
                    Fixtures
                </button>
                <button class="px-4 py-2 text-gray-500 hover:text-fpl-purple">
                    FDR
                </button>
            </div>
        </div>

        <!-- Gameweek Header -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <!-- Previous Gameweek Arrow -->
                @if($previousGameweek)
                    <a href="{{ route('fixtures', ['gameweek' => $previousGameweek->gameweek_id]) }}"
                       class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                @else
                    <div class="p-2 rounded-full opacity-50">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </div>
                @endif

                <div class="text-center">
                    <h2 class="text-xl font-bold text-gray-900">Gameweek {{ $currentGameweek }}</h2>
                    @if($gameweekData && $gameweekData->deadline_time)
                        <p class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($gameweekData->deadline_time)->format('D j M, H:i') }}</p>
                        <div class="mt-2">
                            <span class="bg-fpl-purple text-white px-3 py-1 rounded-full text-sm">
                                Deadline: {{ \Carbon\Carbon::parse($gameweekData->deadline_time)->format('D j M, H:i') }}
                            </span>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">No deadline available</p>
                    @endif
                    <p class="text-xs text-gray-500 mt-2">*All times are shown in your local time</p>
                </div>

                <!-- Next Gameweek Arrow -->
                @if($nextGameweek)
                    <a href="{{ route('fixtures', ['gameweek' => $nextGameweek->gameweek_id]) }}"
                       class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                @else
                    <div class="p-2 rounded-full opacity-50">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                @endif
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Fixtures List -->
        @if($fixtures->isNotEmpty())
            <!-- Group fixtures by date -->
            @php
                $fixturesByDate = $fixtures->groupBy(function($fixture) {
                    // Handle both string dates and Carbon objects
                    $kickoffTime = $fixture->kickoff_time;
                    if (is_string($kickoffTime)) {
                        return \Carbon\Carbon::parse($kickoffTime)->format('Y-m-d');
                    }
                    return $kickoffTime->format('Y-m-d');
                });
            @endphp

            @foreach($fixturesByDate as $date => $dayFixtures)
                <div class="bg-white rounded-lg shadow-lg mb-4">
                    <!-- Date Header -->
                    <div class="bg-gray-50 px-6 py-3 border-b">
                        <h3 class="font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($date)->format('D j M') }}
                        </h3>
                    </div>

                    <!-- Fixtures for this date -->
                    <div class="divide-y divide-gray-100">
                        @foreach($dayFixtures as $fixture)
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <!-- Home Team -->
                                    <div class="flex items-center space-x-3 flex-1">
                                        <div class="w-8 h-8 flex items-center justify-center">
                                            <img src="{{ $fixture->home_team_logo ?? TeamLogoService::getLogoUrl($fixture->home_team_short, 'api-football') }}"
                                                 alt="{{ $fixture->home_team_short }}"
                                                 class="w-8 h-8"
                                                 onerror="this.onerror=null; this.src='{{ TeamLogoService::getLogoUrl($fixture->home_team_short, 'logos-world') }}'; if(this.complete && this.naturalHeight === 0) { this.style.display='none'; this.nextElementSibling.style.display='flex'; }">
                                            <div class="w-8 h-8 bg-gray-200 rounded-full items-center justify-center text-xs font-bold text-gray-600" style="display: none;">
                                                {{ $fixture->home_team_short }}
                                            </div>
                                        </div>
                                        <span class="font-medium text-gray-900">{{ $fixture->home_team_short }}</span>
                                    </div>

                                    <!-- Time -->
                                    <div class="text-center px-4">
                                        <div class="text-sm font-semibold text-gray-900">
                                            @php
                                                $kickoffTime = $fixture->kickoff_time;
                                                if (is_string($kickoffTime)) {
                                                    echo \Carbon\Carbon::parse($kickoffTime)->format('H:i');
                                                } else {
                                                    echo $kickoffTime->format('H:i');
                                                }
                                            @endphp
                                        </div>
                                    </div>

                                    <!-- Away Team -->
                                    <div class="flex items-center space-x-3 flex-1 justify-end">
                                        <span class="font-medium text-gray-900">{{ $fixture->away_team_short }}</span>
                                        <div class="w-8 h-8 flex items-center justify-center">
                                            <img src="{{ $fixture->away_team_logo ?? TeamLogoService::getLogoUrl($fixture->away_team_short, 'api-football') }}"
                                                 alt="{{ $fixture->away_team_short }}"
                                                 class="w-8 h-8"
                                                 onerror="this.onerror=null; this.src='{{ TeamLogoService::getLogoUrl($fixture->away_team_short, 'logos-world') }}'; if(this.complete && this.naturalHeight === 0) { this.style.display='none'; this.nextElementSibling.style.display='flex'; }">
                                            <div class="w-8 h-8 bg-gray-200 rounded-full items-center justify-center text-xs font-bold text-gray-600" style="display: none;">
                                                {{ $fixture->away_team_short }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Info -->
                                    <div class="ml-4">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No fixtures available</h3>
                <p class="text-gray-600 mb-4">Fixtures for this gameweek will be available soon.</p>
                <button class="bg-fpl-purple text-white px-4 py-2 rounded hover:bg-purple-800 transition-colors"
                        onclick="importFixtures()">
                    Import Fixtures
                </button>
            </div>
        @endif
    </div>

    <script>
        function importFixtures() {
            // Show loading state
            const button = event.target;
            const originalText = button.textContent;
            button.textContent = 'Importing...';
            button.disabled = true;

            // Make request to import fixtures
            fetch('{{ route("fixtures.import") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Optionally reload the page to show imported fixtures
                    // window.location.reload();
                } else {
                    alert('Import failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while importing fixtures.');
            })
            .finally(() => {
                button.textContent = originalText;
                button.disabled = false;
            });
        }
    </script>
</body>
</html>
