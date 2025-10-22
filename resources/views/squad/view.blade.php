<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $user->team_name ?? 'My Squad' }} - Fantasy Premier League</title>

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

        .player-card-bench {
            position: relative;
            transition: transform 0.2s ease;
        }

        .player-card-bench:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    @include('partials.navigation')
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header Section -->
            <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $user->team_name ?? 'My Squad' }}</h1>
                        <p class="text-gray-600">Your Fantasy Premier League Squad</p>
                    </div>
                    <div class="text-right">
                        @if(isset($nextGameweek))
                            <div class="text-lg font-bold text-gray-900">{{ $nextGameweek->name }}</div>
                            <div class="text-sm text-gray-600">
                                @if($nextGameweek->finished)
                                    Finished
                                @else
                                    Deadline: {{ date('D j M, H:i', strtotime($nextGameweek->deadline_time)) }}
                                @endif
                            </div>
                        @else
                            <div class="text-lg font-bold text-gray-900">Gameweek 3</div>
                            <div class="text-sm text-gray-600">Team Value: £100.0m</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Football Pitch Layout - Official FPL Style -->
            <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                <div class="relative">
                    <!-- Pitch Background -->
                    <div class="relative w-full h-[600px] bg-gradient-to-b from-green-400 to-green-500 rounded-lg overflow-hidden">
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

                        <!-- Player Cards positioned to use full pitch height -->

                        <!-- Goalkeeper (TOP - 8% from top) -->
                        <div class="absolute top-12 left-1/2 transform -translate-x-1/2">
                            @if(isset($squad['goalkeepers'][0]))
                                <div class="player-card-pitch">
                                    <!-- Captain/Vice-Captain Indicator -->
                                    @if(isset($teamData['captain_id']) && $squad['goalkeepers'][0]->fpl_id == $teamData['captain_id'])
                                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold z-10">
                                            C
                                        </div>
                                    @elseif(isset($teamData['vice_captain_id']) && $squad['goalkeepers'][0]->fpl_id == $teamData['vice_captain_id'])
                                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-xs font-bold text-white z-10">
                                            V
                                        </div>
                                    @endif
                                    <!-- Jersey -->
                                    <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2">
                                        <img src="{{ $squad['goalkeepers'][0]->jersey_url }}"
                                             alt="Jersey"
                                             class="w-12 h-12 rounded">
                                    </div>
                                    <!-- Player Info -->
                                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                        <div class="text-xs font-semibold text-gray-900">{{ $squad['goalkeepers'][0]->web_name }}</div>
                                        <div class="text-xs text-gray-600">{{ $squad['goalkeepers'][0]->team_short }} (H)</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Defenders Row (25% from top) - Full width spread -->
                        <div class="absolute top-36 left-0 right-0 flex justify-between px-16">
                            @foreach(($squad['defenders'] ?? [])->take(4) as $index => $defender)
                                <div class="player-card-pitch">
                                    <!-- Captain/Vice-Captain Indicator -->
                                    @if(isset($teamData['captain_id']) && $defender->fpl_id == $teamData['captain_id'])
                                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold z-10">
                                            C
                                        </div>
                                    @elseif(isset($teamData['vice_captain_id']) && $defender->fpl_id == $teamData['vice_captain_id'])
                                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-xs font-bold text-white z-10">
                                            V
                                        </div>
                                    @endif
                                    <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2">
                                        <img src="{{ $defender->jersey_url }}"
                                             alt="Jersey"
                                             class="w-12 h-12 rounded">
                                    </div>
                                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                        <div class="text-xs font-semibold text-gray-900">{{ $defender->web_name }}</div>
                                        <div class="text-xs text-gray-600">{{ $defender->team_short }} (H)</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Midfielders Row (55% from top) - Full width spread -->
                        <div class="absolute top-80 left-0 right-0 flex justify-between px-16">
                            @foreach(($squad['midfielders'] ?? [])->take(4) as $index => $midfielder)
                                <div class="player-card-pitch">
                                    <!-- Captain/Vice-Captain Indicator -->
                                    @if(isset($teamData['captain_id']) && $midfielder->fpl_id == $teamData['captain_id'])
                                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold z-10">
                                            C
                                        </div>
                                    @elseif(isset($teamData['vice_captain_id']) && $midfielder->fpl_id == $teamData['vice_captain_id'])
                                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-xs font-bold text-white z-10">
                                            V
                                        </div>
                                    @endif
                                    <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2">
                                        <img src="{{ $midfielder->jersey_url }}"
                                             alt="Jersey"
                                             class="w-12 h-12 rounded">
                                    </div>
                                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                        <div class="text-xs font-semibold text-gray-900">{{ $midfielder->web_name }}</div>
                                        <div class="text-xs text-gray-600">{{ $midfielder->team_short }} (H)</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Forwards Row (80% from top) - Spread apart -->
                        <div class="absolute top-[480px] left-0 right-0 flex justify-center space-x-32 px-16">
                            @foreach(($squad['forwards'] ?? [])->take(2) as $index => $forward)
                                <div class="player-card-pitch">
                                    <!-- Captain/Vice-Captain Indicator -->
                                    @if(isset($teamData['captain_id']) && $forward->fpl_id == $teamData['captain_id'])
                                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold z-10">
                                            C
                                        </div>
                                    @elseif(isset($teamData['vice_captain_id']) && $forward->fpl_id == $teamData['vice_captain_id'])
                                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-xs font-bold text-white z-10">
                                            V
                                        </div>
                                    @endif
                                    <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2">
                                        <img src="{{ $forward->jersey_url }}"
                                             alt="Jersey"
                                             class="w-12 h-12 rounded">
                                    </div>
                                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                        <div class="text-xs font-semibold text-gray-900">{{ $forward->web_name }}</div>
                                        <div class="text-xs text-gray-600">{{ $forward->team_short }} (H)</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Substitutes Section - Official FPL Style -->
            <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 text-center">Substitutes</h3>
                <div class="flex justify-center space-x-6">
                    @php
                        // Use the pre-separated bench players from controller
                        $benchPlayers = [];
                        $benchIndex = 1;

                        // Backup GK (from bench goalkeepers)
                        if (isset($benchSquad['goalkeepers']) && $benchSquad['goalkeepers']->count() > 0) {
                            $benchPlayers[] = ['player' => $benchSquad['goalkeepers'][0], 'position' => 'GKP', 'label' => 'GKP'];
                        }

                        // Bench defenders
                        if (isset($benchSquad['defenders']) && $benchSquad['defenders']->count() > 0) {
                            foreach ($benchSquad['defenders'] as $defender) {
                                $benchPlayers[] = ['player' => $defender, 'position' => 'DEF', 'label' => $benchIndex . '. DEF'];
                                $benchIndex++;
                            }
                        }

                        // Bench midfielders
                        if (isset($benchSquad['midfielders']) && $benchSquad['midfielders']->count() > 0) {
                            foreach ($benchSquad['midfielders'] as $midfielder) {
                                $benchPlayers[] = ['player' => $midfielder, 'position' => 'MID', 'label' => $benchIndex . '. MID'];
                                $benchIndex++;
                            }
                        }

                        // Bench forwards
                        if (isset($benchSquad['forwards']) && $benchSquad['forwards']->count() > 0) {
                            foreach ($benchSquad['forwards'] as $forward) {
                                $benchPlayers[] = ['player' => $forward, 'position' => 'FWD', 'label' => $benchIndex . '. FWD'];
                                $benchIndex++;
                            }
                        }
                    @endphp

                    @foreach($benchPlayers as $benchPlayer)
                        <div class="text-center">
                            <div class="text-sm font-medium text-gray-600 mb-2">
                                {{ $benchPlayer['label'] }}
                            </div>
                            <div class="player-card-bench">
                                <div class="w-14 h-14 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 mx-auto">
                                    <img src="{{ $benchPlayer['player']->jersey_url }}"
                                         alt="Jersey"
                                         class="w-10 h-10 rounded">
                                </div>
                                <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                    <div class="text-xs font-semibold text-gray-900">{{ $benchPlayer['player']->web_name }}</div>
                                    <div class="text-xs text-gray-600">{{ $benchPlayer['player']->team_short }} (H)</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-center space-x-4">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Back to Dashboard
                </a>
                <a href="{{ route('transfers') }}" class="px-6 py-3 bg-fpl-purple text-white rounded-lg hover:bg-purple-900 transition-colors">
                    Make Transfers
                </a>
            </div>
        </div>
    </div>
</body>
</html>
