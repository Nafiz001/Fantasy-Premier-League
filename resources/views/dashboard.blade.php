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

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Full Navigation Header - Available after squad selection -->
    @include('partials.navigation')

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
                        @if(isset($currentGameweek))
                            <div class="text-lg font-bold text-gray-900">{{ $currentGameweek->name }}</div>
                            <div class="text-sm text-gray-600">
                                @if($currentGameweek->finished)
                                    Finished
                                @else
                                    Deadline: {{ date('D j M, H:i', strtotime($currentGameweek->deadline_time)) }}
                                @endif
                            </div>
                        @else
                            <div class="text-lg font-bold text-gray-900">Gameweek 3</div>
                            <div class="text-sm text-gray-600">Next deadline: Sat 14 Sep, 16:00</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Latest Gameweek Points</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $teamData['latest_points'] ?? 0 }}</div>
                    <div class="text-xs text-gray-500">
                        @if(isset($teamData['latest_gameweek']))
                            {{ $teamData['latest_gameweek'] }}
                        @else
                            No gameweek finished yet
                        @endif
                    </div>
                </div>
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Total Points</div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($teamData['points'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500">Season total</div>
                </div>
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Team Value</div>
                    <div class="text-2xl font-bold text-gray-900">£{{ 100 - (auth()->user()->budget_remaining ?? 0) / 10 }}m</div>
                    <div class="text-xs text-gray-500">In the bank: £{{ (auth()->user()->budget_remaining ?? 0) / 10 }}m</div>
                </div>
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Free Transfers</div>
                    <div class="text-2xl font-bold text-gray-900">{{ auth()->user()->free_transfers ?? 1 }}</div>
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
                            <a href="{{ route('squad.view') }}" class="text-sm text-fpl-purple hover:text-fpl-magenta">View full team</a>
                        </div>

                        <!-- Mini Squad Preview on Pitch -->
                        <div class="relative w-full h-48 bg-gradient-to-b from-green-400 to-green-500 rounded-lg overflow-hidden">
                            <!-- Mini pitch lines -->
                            <div class="absolute inset-0">
                                <div class="absolute inset-1 border border-white/30 rounded"></div>
                                <div class="absolute top-1/2 left-1 right-1 h-px bg-white/30"></div>
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-8 h-8 border border-white/30 rounded-full"></div>
                            </div>

                            <!-- Mini player positions (using full height) -->
                            <!-- GK (TOP - 8% from top) -->
                            <div class="absolute top-4 left-1/2 transform -translate-x-1/2">
                                <div class="w-3 h-3 bg-blue-600 rounded-full border border-white/50"></div>
                            </div>

                            <!-- Defenders (25% from top) - Full width -->
                            <div class="absolute top-12 left-0 right-0 flex justify-between px-6">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full border border-white/50"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full border border-white/50"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full border border-white/50"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full border border-white/50"></div>
                            </div>

                            <!-- Midfielders (55% from top) - Full width -->
                            <div class="absolute top-24 left-0 right-0 flex justify-between px-6">
                                <div class="w-3 h-3 bg-teal-500 rounded-full border border-white/50"></div>
                                <div class="w-3 h-3 bg-teal-500 rounded-full border border-white/50"></div>
                                <div class="w-3 h-3 bg-teal-500 rounded-full border border-white/50"></div>
                                <div class="w-3 h-3 bg-teal-500 rounded-full border border-white/50"></div>
                            </div>

                            <!-- Forwards (80% from top) - Spread apart -->
                            <div class="absolute top-36 left-0 right-0 flex justify-center space-x-8 px-6">
                                <div class="w-3 h-3 bg-red-500 rounded-full border border-white/50"></div>
                                <div class="w-3 h-3 bg-red-500 rounded-full border border-white/50"></div>
                            </div>

                            <!-- Formation text -->
                            <div class="absolute top-2 left-2 text-xs text-white/70 font-semibold">4-4-2</div>
                        </div>

                        <div class="text-center mt-4">
                            <p class="text-gray-600">Your squad is ready for the season!</p>
                            <p class="text-sm text-gray-500 mt-1">15 players selected</p>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Gameweek Info -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Next Gameweek</h3>
                        <div class="text-center">
                            @if(isset($currentGameweek))
                                <div class="text-3xl font-bold text-fpl-purple mb-2">GW{{ $currentGameweek->gameweek_id }}</div>
                                <div class="text-sm text-gray-600 mb-3">
                                    @if($currentGameweek->finished)
                                        Finished
                                    @else
                                        Deadline: {{ date('D j M, H:i', strtotime($currentGameweek->deadline_time)) }}
                                    @endif
                                </div>
                                @if(!$currentGameweek->finished)
                                    @php
                                        $deadline = new DateTime($currentGameweek->deadline_time);
                                        $now = new DateTime();
                                        $interval = $now->diff($deadline);

                                        if ($interval->invert) {
                                            $timeRemaining = 'Deadline passed';
                                        } else {
                                            $days = $interval->days;
                                            $hours = $interval->h;

                                            if ($days > 0) {
                                                $timeRemaining = "{$days} day" . ($days > 1 ? 's' : '') . ", {$hours} hour" . ($hours > 1 ? 's' : '') . " remaining";
                                            } elseif ($hours > 0) {
                                                $timeRemaining = "{$hours} hour" . ($hours > 1 ? 's' : '') . " remaining";
                                            } else {
                                                $minutes = $interval->i;
                                                $timeRemaining = "{$minutes} minute" . ($minutes > 1 ? 's' : '') . " remaining";
                                            }
                                        }
                                    @endphp
                                    <div class="text-xs text-gray-500">{{ $timeRemaining }}</div>
                                @endif
                            @else
                                <div class="text-3xl font-bold text-fpl-purple mb-2">GW4</div>
                                <div class="text-sm text-gray-600 mb-3">Deadline: Sat 14 Sep, 16:00</div>
                                <div class="text-xs text-gray-500">2 days, 14 hours remaining</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Premier League News Section - Full Width -->
            <div class="mt-6 bg-white/95 backdrop-blur-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Latest Premier League News</h2>
                        <p class="text-sm text-gray-500 mt-1">Live updates from BBC Sport, Sky Sports & The Guardian</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700">BBC</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700">Sky</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">Guardian</span>
                    </div>
                </div>

                @if(isset($fplNews) && !empty($fplNews))
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($fplNews as $news)
                            <a href="{{ $news['link'] }}" target="_blank" class="group block bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                <!-- News Image -->
                                <div class="relative h-48 bg-gray-100 overflow-hidden">
                                    <img src="{{ $news['image'] }}"
                                         alt="{{ $news['title'] }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect width=\'400\' height=\'300\' fill=\'%2338003c\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' fill=\'white\' font-size=\'60\' font-weight=\'bold\'%3EFPL%3C/text%3E%3C/svg%3E'">
                                    @if(isset($news['source']))
                                        <span class="absolute top-3 right-3 text-xs px-2 py-1 rounded-full backdrop-blur-sm bg-white/90 font-medium
                                            @if($news['source'] === 'Bbc') text-red-700
                                            @elseif($news['source'] === 'Skysports') text-blue-700
                                            @elseif($news['source'] === 'Guardian') text-green-700
                                            @else text-purple-700
                                            @endif">
                                            {{ $news['source'] }}
                                        </span>
                                    @endif
                                </div>

                                <!-- News Content -->
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 text-base mb-2 line-clamp-2 group-hover:text-fpl-purple transition-colors">
                                        {{ $news['title'] }}
                                    </h3>
                                    <p class="text-gray-600 text-sm line-clamp-3 mb-3">
                                        {{ $news['description'] }}
                                    </p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-400 text-xs">{{ $news['date'] ?? '' }}</span>
                                        <span class="text-fpl-purple text-sm font-medium group-hover:text-fpl-magenta">
                                            Read more →
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <!-- Fallback content -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="font-medium text-gray-900 mb-2">Welcome to FPL!</div>
                            <div class="text-gray-600 text-sm">Your journey starts here. Good luck this season!</div>
                        </div>
                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="font-medium text-gray-900 mb-2">Pro Tip</div>
                            <div class="text-gray-600 text-sm">Check fixture difficulty before making transfers.</div>
                        </div>
                        <div class="p-6 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="font-medium text-gray-900 mb-2">Stay Updated</div>
                            <div class="text-gray-600 text-sm">Follow injury news and team press conferences.</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
