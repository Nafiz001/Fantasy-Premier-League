<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Leagues - Fantasy Premier League</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

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
    @include('partials.navigation')

    <div class="min-h-screen">
    <!-- Header -->
    <header class="bg-white/10 backdrop-blur-md border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">My Leagues</h1>
                    <p class="text-white/80">Create and join leagues with friends</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('leagues.join') }}" class="bg-fpl-green text-fpl-purple px-6 py-3 rounded-lg font-bold hover:bg-opacity-90 transition-all">
                        Join League
                    </a>
                    <a href="{{ route('leagues.create') }}" class="bg-white/20 text-white px-6 py-3 rounded-lg font-bold hover:bg-white/30 transition-all">
                        Create League
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- My Leagues -->
            <div class="lg:col-span-2">
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">My Leagues ({{ $myLeagues->count() }})</h2>

                    @if($myLeagues->isEmpty())
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">🏆</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No leagues yet!</h3>
                            <p class="text-gray-600 mb-6">Create your first league or join one with a code</p>
                            <div class="flex justify-center gap-4">
                                <a href="{{ route('leagues.create') }}" class="bg-fpl-purple text-white px-6 py-3 rounded-lg font-bold hover:bg-opacity-90">
                                    Create League
                                </a>
                                <a href="{{ route('leagues.join') }}" class="bg-fpl-green text-fpl-purple px-6 py-3 rounded-lg font-bold hover:bg-opacity-90">
                                    Join League
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($myLeagues as $league)
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-fpl-green transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-3">
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $league->name }}</h3>
                                            @if($league->admin_id === auth()->id())
                                                <span class="bg-fpl-purple text-white text-xs px-2 py-1 rounded-full">Admin</span>
                                            @endif
                                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full">
                                                {{ ucfirst($league->type) }}
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600">
                                            Code: <span class="font-mono font-bold">{{ $league->league_code }}</span>
                                        </div>
                                    </div>

                                    @if($league->description)
                                        <p class="text-gray-600 text-sm mb-3">{{ $league->description }}</p>
                                    @endif

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4 text-sm text-gray-600">
                                            <span>{{ $league->current_entries }}/{{ $league->max_entries }} members</span>
                                            @php
                                                $userMember = $league->leagueMembers->where('user_id', auth()->id())->first();
                                                $userRank = $userMember ? $userMember->rank : 'N/A';
                                            @endphp
                                            <span>Your rank: #{{ $userRank }}</span>
                                        </div>
                                        <a href="{{ route('leagues.show', $league) }}" class="text-fpl-purple hover:text-fpl-magenta font-semibold">
                                            View League →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Discover Public Leagues -->
            <div>
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Discover Leagues</h2>

                    @if($publicLeagues->isEmpty())
                        <div class="text-center py-8">
                            <div class="text-4xl mb-3">🔍</div>
                            <p class="text-gray-600">No public leagues available to join right now.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($publicLeagues as $league)
                                <div class="border border-gray-200 rounded-lg p-3 hover:border-fpl-green transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $league->name }}</h4>
                                            <p class="text-xs text-gray-600 mb-2">by {{ $league->admin->name }}</p>
                                            <div class="text-xs text-gray-500">
                                                {{ $league->current_entries }}/{{ $league->max_entries }} members
                                            </div>
                                        </div>
                                        <form action="{{ route('leagues.join-code') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="league_code" value="{{ $league->league_code }}">
                                            <button type="submit" class="text-xs bg-fpl-green text-fpl-purple px-3 py-1 rounded font-semibold hover:bg-opacity-90">
                                                Join
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Quick Join -->
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Join</h3>
                    <form action="{{ route('leagues.join-code') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="league_code" class="block text-sm font-medium text-gray-700 mb-2">League Code</label>
                            <input type="text"
                                   id="league_code"
                                   name="league_code"
                                   maxlength="6"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fpl-green focus:border-fpl-green font-mono uppercase"
                                   placeholder="ABC123"
                                   style="text-transform: uppercase;">
                        </div>
                        <button type="submit" class="w-full bg-fpl-purple text-white py-2 rounded-lg font-semibold hover:bg-opacity-90">
                            Join League
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
    // Auto-uppercase league code input
    document.getElementById('league_code').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
    </script>
</body>
</html>
