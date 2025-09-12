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
    <header class="bg-white/95 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-fpl-purple rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-lg">F</span>
                    </div>
                    <span class="text-lg font-bold text-fpl-purple">Fantasy</span>
                </div>
                
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">My Team</a>
                    <a href="{{ route('pick.team') }}" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">Pick Team</a>
                    <a href="#" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">Transfers</a>
                    <a href="{{ route('fpl.dashboard') }}" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">Statistics</a>
                    <a href="{{ route('fpl.fixtures') }}" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">Fixtures</a>
                    <a href="#" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">Leagues</a>
                    <a href="#" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">More</a>
                </nav>

                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-semibold text-gray-900">{{ $user->team_name ?? 'My Team' }}</div>
                        <div class="text-xs text-gray-500">{{ $user->name }}</div>
                    </div>
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
                        <div class="text-lg font-bold text-gray-900">Gameweek 3</div>
                        <div class="text-sm text-gray-600">Team Value: £100.0m</div>
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
                    <!-- GKP -->
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-600 mb-2">GKP</div>
                        @if(isset($squad['goalkeepers'][1]))
                            <div class="player-card-bench">
                                <div class="w-14 h-14 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 mx-auto">
                                    <img src="{{ $squad['goalkeepers'][1]->jersey_url }}" 
                                         alt="Jersey" 
                                         class="w-10 h-10 rounded">
                                </div>
                                <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                    <div class="text-xs font-semibold text-gray-900">{{ $squad['goalkeepers'][1]->web_name }}</div>
                                    <div class="text-xs text-gray-600">{{ $squad['goalkeepers'][1]->team_short }} (H)</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- 1. DEF -->
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-600 mb-2">1. DEF</div>
                        @if(isset($squad['defenders'][4]))
                            <div class="player-card-bench">
                                <div class="w-14 h-14 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 mx-auto">
                                    <img src="{{ $squad['defenders'][4]->jersey_url }}" 
                                         alt="Jersey" 
                                         class="w-10 h-10 rounded">
                                </div>
                                <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                    <div class="text-xs font-semibold text-gray-900">{{ $squad['defenders'][4]->web_name }}</div>
                                    <div class="text-xs text-gray-600">{{ $squad['defenders'][4]->team_short }} (H)</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- 2. MID -->
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-600 mb-2">2. MID</div>
                        @if(isset($squad['midfielders'][4]))
                            <div class="player-card-bench">
                                <div class="w-14 h-14 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 mx-auto">
                                    <img src="{{ $squad['midfielders'][4]->jersey_url }}" 
                                         alt="Jersey" 
                                         class="w-10 h-10 rounded">
                                </div>
                                <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                    <div class="text-xs font-semibold text-gray-900">{{ $squad['midfielders'][4]->web_name }}</div>
                                    <div class="text-xs text-gray-600">{{ $squad['midfielders'][4]->team_short }} (H)</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- 3. FWD -->
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-600 mb-2">3. FWD</div>
                        @if(isset($squad['forwards'][2]))
                            <div class="player-card-bench">
                                <div class="w-14 h-14 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 mx-auto">
                                    <img src="{{ $squad['forwards'][2]->jersey_url }}" 
                                         alt="Jersey" 
                                         class="w-10 h-10 rounded">
                                </div>
                                <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                    <div class="text-xs font-semibold text-gray-900">{{ $squad['forwards'][2]->web_name }}</div>
                                    <div class="text-xs text-gray-600">{{ $squad['forwards'][2]->team_short }} (H)</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-center space-x-4">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Back to Dashboard
                </a>
                <a href="#" class="px-6 py-3 bg-fpl-purple text-white rounded-lg hover:bg-purple-900 transition-colors">
                    Make Transfers
                </a>
            </div>
        </div>
    </div>
</body>
</html>