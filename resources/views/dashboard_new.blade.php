<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fantasy Premier League - Dashboard</title>
    
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
<body>
    <!-- Full Navigation Header - Available after squad selection -->
    <header class="bg-white/95 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-fpl-purple rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-lg">F</span>
                    </div>
                    <span class="text-lg font-bold text-fpl-purple">Fantasy</span>
                </div>
                
                <!-- Full Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="text-fpl-purple hover:text-fpl-magenta font-medium transition-colors">My Team</a>
                    <a href="#" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">Transfers</a>
                    <a href="{{ route('fpl.dashboard') }}" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">Statistics</a>
                    <a href="{{ route('fixtures') }}" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">Fixtures</a>
                    <a href="#" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">Leagues</a>
                    <a href="#" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">More</a>
                </nav>

                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-semibold text-gray-900">{{ auth()->user()->team_name ?? 'My Team' }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->name }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Dashboard Content -->
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Welcome Section -->
            <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
                        <p class="text-gray-600">Manage your Fantasy Premier League team: {{ auth()->user()->team_name }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-900">Gameweek 3</div>
                        <div class="text-sm text-gray-600">Next deadline: Sat 14 Sep, 16:00</div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Total Points</div>
                    <div class="text-2xl font-bold text-gray-900">0</div>
                    <div class="text-xs text-green-600">+0 this week</div>
                </div>
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Overall Rank</div>
                    <div class="text-2xl font-bold text-gray-900">-</div>
                    <div class="text-xs text-gray-500">First season</div>
                </div>
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Team Value</div>
                    <div class="text-2xl font-bold text-gray-900">£100.0m</div>
                    <div class="text-xs text-gray-500">In the bank: £{{ auth()->user()->budget_remaining ?? '0.0' }}m</div>
                </div>
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Free Transfers</div>
                    <div class="text-2xl font-bold text-gray-900">1</div>
                    <div class="text-xs text-gray-500">Available this week</div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Team Management -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Squad Overview -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Your Squad</h3>
                            <a href="#" class="text-sm text-fpl-purple hover:text-fpl-magenta">View full team</a>
                        </div>
                        
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-gray-400 text-2xl">⚽</span>
                            </div>
                            <p class="text-gray-600">Your squad is ready for the season!</p>
                            <p class="text-sm text-gray-500 mt-1">15 players selected</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="#" class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors">
                                <div class="text-2xl mb-2">🔄</div>
                                <div class="text-sm font-medium">Make Transfers</div>
                            </a>
                            <a href="#" class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors">
                                <div class="text-2xl mb-2">📊</div>
                                <div class="text-sm font-medium">View Statistics</div>
                            </a>
                            <a href="#" class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors">
                                <div class="text-2xl mb-2">🏆</div>
                                <div class="text-sm font-medium">Join Leagues</div>
                            </a>
                            <a href="{{ route('fpl.dashboard') }}" class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors">
                                <div class="text-2xl mb-2">🔍</div>
                                <div class="text-sm font-medium">Analysis Hub</div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Gameweek Info -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Next Gameweek</h3>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-fpl-purple mb-2">GW4</div>
                            <div class="text-sm text-gray-600 mb-3">Deadline: Sat 14 Sep, 16:00</div>
                            <div class="text-xs text-gray-500">2 days, 14 hours remaining</div>
                        </div>
                    </div>

                    <!-- News & Tips -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">FPL News</h3>
                        <div class="space-y-4">
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">Welcome to FPL!</div>
                                <div class="text-gray-600 text-xs mt-1">Your journey starts here. Good luck this season!</div>
                            </div>
                            <div class="text-sm">
                                <div class="font-medium text-gray-900">Pro Tip</div>
                                <div class="text-gray-600 text-xs mt-1">Check fixture difficulty before making transfers.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Links to Other Features -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Advanced Features</h3>
                        <div class="space-y-3">
                            <a href="{{ route('fpl.data.dashboard') }}" class="block text-sm text-fpl-purple hover:text-fpl-magenta">
                                📊 Data Management
                            </a>
                            <a href="{{ route('fpl.captains') }}" class="block text-sm text-fpl-purple hover:text-fpl-magenta">
                                👑 Captain Picks
                            </a>
                            <a href="{{ route('fpl.differentials') }}" class="block text-sm text-fpl-purple hover:text-fpl-magenta">
                                🎯 Differential Players
                            </a>
                            <a href="{{ route('fpl.transfers') }}" class="block text-sm text-fpl-purple hover:text-fpl-magenta">
                                🔄 Transfer Suggestions
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
