<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fantasy Premier League - Points</title>

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

        .player-card-pitch {
            position: relative;
            transition: transform 0.2s ease;
        }

        .player-card-pitch:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    @include('partials.navigation')

    <!-- Main Content -->
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-6">
            @if(isset($error))
                <!-- Error State -->
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="text-center">
                        <h1 class="text-2xl font-bold text-gray-900 mb-4">No Points Available</h1>
                        <p class="text-gray-600">{{ $error }}</p>
                    </div>
                </div>
            @else
                <!-- Header Section with Navigation -->
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Previous Gameweek Arrow -->
                            @if($previousGameweek)
                                <a href="{{ route('points', ['gameweek' => $previousGameweek->gameweek_id]) }}"
                                   class="p-2 bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </a>
                            @else
                                <div class="p-2 bg-gray-50 rounded-full opacity-50">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </div>
                            @endif

                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ $currentGameweek['gameweek_name'] ?? 'Gameweek Points' }}</h1>
                                <p class="text-gray-600">{{ $user->team_name ?? 'My Team' }} - Points Breakdown</p>
                            </div>

                            <!-- Next Gameweek Arrow -->
                            @if($nextGameweek)
                                <a href="{{ route('points', ['gameweek' => $nextGameweek->gameweek_id]) }}"
                                   class="p-2 bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @else
                                <div class="p-2 bg-gray-50 rounded-full opacity-50">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="text-right">
                            <div class="text-3xl font-bold text-fpl-purple">{{ $squadPoints['total_points'] ?? 0 }}</div>
                            <div class="text-sm text-gray-600">Total Points</div>
                            @if($currentGameweek && isset($currentGameweek['average_score']))
                                <div class="text-sm text-gray-500 mt-1">Avg: {{ $currentGameweek['average_score'] }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($squadPoints && isset($squadPoints['player_details']))
                    <!-- Squad Points Layout -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Squad Performance</h2>

                        <!-- Football Pitch Layout - Exact copy from squad view -->
                        <div class="relative w-full h-[650px] bg-gradient-to-b from-green-400 to-green-500 rounded-lg overflow-hidden mb-6">
                            <!-- Pitch Lines -->
                            <div class="absolute inset-0">
                                <!-- Outer boundary -->
                                <div class="absolute inset-4 border-2 border-white/60 rounded"></div>

                                <!-- Goal Areas (top and bottom) -->
                                <div class="absolute top-4 left-1/2 transform -translate-x-1/2 w-32 h-16 border-2 border-white/60"></div>
                                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-32 h-16 border-2 border-white/60"></div>

                                <!-- Penalty Areas (top and bottom) -->
                                <div class="absolute top-4 left-1/2 transform -translate-x-1/2 w-48 h-24 border-2 border-white/60"></div>
                                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-48 h-24 border-2 border-white/60"></div>

                                <!-- Center Circle -->
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-24 h-24 border-2 border-white/60 rounded-full"></div>
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-2 h-2 bg-white/60 rounded-full"></div>

                                <!-- Center Line -->
                                <div class="absolute top-1/2 left-4 right-4 h-0.5 bg-white/60"></div>
                            </div>

                            @php
                                $playersByPosition = collect($squadPoints['player_details'])->groupBy(function($item) {
                                    return $item['player']->position;
                                });

                                $goalkeepers = $playersByPosition->get('Goalkeeper', collect());
                                $defenders = $playersByPosition->get('Defender', collect());
                                $midfielders = $playersByPosition->get('Midfielder', collect());
                                $forwards = $playersByPosition->get('Forward', collect());

                                // Function to get jersey URL (same as squad controller)
                                function getJerseyUrl($teamId) {
                                    return "https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_{$teamId}-110.png";
                                }
                            @endphp

                            <!-- Goalkeeper (TOP - 8% from top) -->
                            <div class="absolute top-12 left-1/2 transform -translate-x-1/2">
                                @foreach($goalkeepers->take(1) as $playerData)
                                    <div class="player-card-pitch">
                                        @if($playerData['is_captain'])
                                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold z-10">C</div>
                                        @elseif($playerData['is_vice_captain'])
                                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-xs font-bold text-white z-10">V</div>
                                        @endif

                                        <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2">
                                            <img src="{{ getJerseyUrl($playerData['player']->team_code) }}"
                                                 alt="Jersey"
                                                 class="w-12 h-12 rounded">
                                        </div>
                                        <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                            <div class="text-xs font-semibold text-gray-900">{{ $playerData['player']->web_name }}</div>
                                            <div class="text-xs text-gray-600">{{ $playerData['player']->team->short_name ?? 'TBC' }}</div>
                                            <div class="text-sm font-bold text-fpl-purple">{{ $playerData['points'] }}pts</div>
                                            @if($playerData['multiplier'] > 1)
                                                <div class="text-xs text-fpl-magenta">({{ $playerData['original_points'] }} x {{ $playerData['multiplier'] }})</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Defenders Row (25% from top) - Full width spread -->
                            <div class="absolute top-36 left-0 right-0 flex justify-between px-16">
                                @foreach($defenders as $index => $playerData)
                                    <div class="player-card-pitch">
                                        @if($playerData['is_captain'])
                                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold z-10">C</div>
                                        @elseif($playerData['is_vice_captain'])
                                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-xs font-bold text-white z-10">V</div>
                                        @endif

                                        <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2">
                                            <img src="{{ getJerseyUrl($playerData['player']->team_code) }}"
                                                 alt="Jersey"
                                                 class="w-12 h-12 rounded">
                                        </div>
                                        <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                            <div class="text-xs font-semibold text-gray-900">{{ $playerData['player']->web_name }}</div>
                                            <div class="text-xs text-gray-600">{{ $playerData['player']->team->short_name ?? 'TBC' }}</div>
                                            <div class="text-sm font-bold text-fpl-purple">{{ $playerData['points'] }}pts</div>
                                            @if($playerData['multiplier'] > 1)
                                                <div class="text-xs text-fpl-magenta">({{ $playerData['original_points'] }} x {{ $playerData['multiplier'] }})</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Midfielders Row (55% from top) - Full width spread -->
                            <div class="absolute top-80 left-0 right-0 flex justify-between px-16">
                                @foreach($midfielders as $index => $playerData)
                                    <div class="player-card-pitch">
                                        @if($playerData['is_captain'])
                                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold z-10">C</div>
                                        @elseif($playerData['is_vice_captain'])
                                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-xs font-bold text-white z-10">V</div>
                                        @endif

                                        <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2">
                                            <img src="{{ getJerseyUrl($playerData['player']->team_code) }}"
                                                 alt="Jersey"
                                                 class="w-12 h-12 rounded">
                                        </div>
                                        <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                            <div class="text-xs font-semibold text-gray-900">{{ $playerData['player']->web_name }}</div>
                                            <div class="text-xs text-gray-600">{{ $playerData['player']->team->short_name ?? 'TBC' }}</div>
                                            <div class="text-sm font-bold text-fpl-purple">{{ $playerData['points'] }}pts</div>
                                            @if($playerData['multiplier'] > 1)
                                                <div class="text-xs text-fpl-magenta">({{ $playerData['original_points'] }} x {{ $playerData['multiplier'] }})</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Forwards Row (80% from top) - Spread apart -->
                            <div class="absolute top-[480px] left-0 right-0 flex justify-center space-x-32 px-16">
                                @foreach($forwards as $index => $playerData)
                                    <div class="player-card-pitch">
                                        @if($playerData['is_captain'])
                                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold z-10">C</div>
                                        @elseif($playerData['is_vice_captain'])
                                            <div class="absolute -top-2 -right-2 w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-xs font-bold text-white z-10">V</div>
                                        @endif

                                        <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2">
                                            <img src="{{ getJerseyUrl($playerData['player']->team_code) }}"
                                                 alt="Jersey"
                                                 class="w-12 h-12 rounded">
                                        </div>
                                        <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                            <div class="text-xs font-semibold text-gray-900">{{ $playerData['player']->web_name }}</div>
                                            <div class="text-xs text-gray-600">{{ $playerData['player']->team->short_name ?? 'TBC' }}</div>
                                            <div class="text-sm font-bold text-fpl-purple">{{ $playerData['points'] }}pts</div>
                                            @if($playerData['multiplier'] > 1)
                                                <div class="text-xs text-fpl-magenta">({{ $playerData['original_points'] }} x {{ $playerData['multiplier'] }})</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Points Summary -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="font-semibold text-gray-900 mb-2">Gameweek Performance</h3>
                                <div class="text-2xl font-bold text-fpl-purple">{{ $squadPoints['total_points'] }} pts</div>
                                @if($currentGameweek && isset($currentGameweek['average_score']))
                                    <div class="text-sm text-gray-600">League Average: {{ $currentGameweek['average_score'] }}</div>
                                @endif
                            </div>

                            @if($currentGameweek && isset($currentGameweek['highest_score']))
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-900 mb-2">Gameweek High</h3>
                                    <div class="text-2xl font-bold text-fpl-green">{{ $currentGameweek['highest_score'] }} pts</div>
                                    <div class="text-sm text-gray-600">Best score this week</div>
                                </div>
                            @endif

                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="font-semibold text-gray-900 mb-2">Captain Performance</h3>
                                @php
                                    $captain = collect($squadPoints['player_details'])->firstWhere('is_captain', true);
                                @endphp
                                @if($captain)
                                    <div class="text-lg font-bold text-fpl-magenta">{{ $captain['player']['web_name'] }}</div>
                                    <div class="text-sm text-gray-600">{{ $captain['points'] }} pts ({{ $captain['original_points'] }} x 2)</div>
                                @else
                                    <div class="text-sm text-gray-600">No captain data</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <!-- No Squad Data -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <div class="text-center">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">No Squad Data</h2>
                            <p class="text-gray-600">No squad data available for this gameweek.</p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</body>
</html>
