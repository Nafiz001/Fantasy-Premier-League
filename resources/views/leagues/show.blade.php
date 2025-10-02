<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $league->name }} - Fantasy Premier League</title>

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
                    <h1 class="text-3xl font-bold text-white">{{ $league->name }}</h1>
                    <div class="flex items-center gap-4 text-white/80">
                        <span>{{ ucfirst($league->type) }} League</span>
                        <span>•</span>
                        <span>{{ $league->current_entries }}/{{ $league->max_entries }} members</span>
                        <span>•</span>
                        <span class="font-mono">{{ $league->league_code }}</span>
                    </div>
                </div>
                <div class="flex gap-3">
                    @if($isAdmin)
                        <a href="{{ route('leagues.settings', $league) }}" class="bg-white/20 text-white px-4 py-2 rounded-lg font-semibold hover:bg-white/30 transition-all">
                            Settings
                        </a>
                    @endif
                    @if($isMember && !$isAdmin)
                        <form action="{{ route('leagues.leave', $league) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure you want to leave this league?')" class="bg-red-500/80 text-white px-4 py-2 rounded-lg font-semibold hover:bg-red-500 transition-all">
                                Leave League
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('leagues.index') }}" class="text-white hover:text-fpl-green">
                        ← Back to Leagues
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-8">
        @if(!$isMember)
            <!-- Join League Banner -->
            <div class="bg-fpl-green/10 border border-fpl-green rounded-lg p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">You're not a member of this league</h3>
                        <p class="text-gray-600">Join to see the full leaderboard and compete with other managers</p>
                    </div>
                    @if(!$league->isFull())
                        <form action="{{ route('leagues.join-code') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="league_code" value="{{ $league->league_code }}">
                            <button type="submit" class="bg-fpl-green text-fpl-purple px-6 py-3 rounded-lg font-bold hover:bg-opacity-90">
                                Join League
                            </button>
                        </form>
                    @else
                        <span class="text-red-600 font-semibold">League Full</span>
                    @endif
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Leaderboard -->
            <div class="lg:col-span-3">
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Leaderboard</h2>
                        @if($isMember && $userRank)
                            <div class="text-sm text-gray-600">
                                Your rank: <span class="font-bold text-fpl-purple">#{{ $userRank }}</span>
                            </div>
                        @endif
                    </div>

                    @if($leaderboard->isEmpty())
                        <div class="text-center py-12">
                            <div class="text-6xl mb-4">📊</div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No members yet</h3>
                            <p class="text-gray-600">Be the first to join this league!</p>
                        </div>
                    @else
                        <div class="overflow-hidden">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-3 px-2 font-semibold text-gray-900">Rank</th>
                                        <th class="text-left py-3 px-2 font-semibold text-gray-900">Manager</th>
                                        <th class="text-center py-3 px-2 font-semibold text-gray-900">Current GW</th>
                                        <th class="text-right py-3 px-2 font-semibold text-gray-900">Total Points</th>
                                        @if($isMember)
                                            <th class="text-right py-3 px-2 font-semibold text-gray-900">Avg/GW</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaderboard as $member)
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 {{ $member->id === auth()->id() ? 'bg-fpl-green/10' : '' }}">
                                            <td class="py-4 px-2">
                                                <div class="flex items-center gap-2">
                                                    @if($member->current_rank <= 3)
                                                        <span class="text-xl">
                                                            @if($member->current_rank === 1) 🥇
                                                            @elseif($member->current_rank === 2) 🥈
                                                            @else 🥉
                                                            @endif
                                                        </span>
                                                    @endif
                                                    <span class="font-semibold">{{ $member->current_rank }}</span>
                                                </div>
                                            </td>
                                            <td class="py-4 px-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="font-medium">{{ $member->name }}</span>
                                                    @if($member->team_name)
                                                        <span class="text-sm text-gray-500">({{ $member->team_name }})</span>
                                                    @endif
                                                    @if($member->pivot->is_admin ?? false)
                                                        <span class="bg-fpl-purple text-white text-xs px-2 py-1 rounded-full">Admin</span>
                                                    @endif
                                                    @if($member->id === auth()->id())
                                                        <span class="bg-fpl-green text-fpl-purple text-xs px-2 py-1 rounded-full">You</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-4 px-2 text-center">
                                                {{ $member->gameweek ?? 1 }}
                                            </td>
                                            <td class="py-4 px-2 text-right font-bold">
                                                {{ number_format($member->points ?? 0) }}
                                            </td>
                                            @if($isMember)
                                                <td class="py-4 px-2 text-right text-sm text-gray-600">
                                                    @php
                                                        $gw = $member->gameweek ?? 1;
                                                        $pts = $member->points ?? 0;
                                                        $avg = $gw > 0 ? ($pts / $gw) : 0;
                                                    @endphp
                                                    {{ number_format($avg, 1) }}
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- League Info Sidebar -->
            <div>
                <!-- League Details -->
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">League Details</h3>

                    <div class="space-y-3">
                        <div>
                            <span class="text-sm text-gray-600">League Code</span>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-lg">{{ $league->league_code }}</span>
                                <button onclick="copyToClipboard('{{ $league->league_code }}')" class="text-fpl-purple hover:text-fpl-magenta text-sm">
                                    📋 Copy
                                </button>
                            </div>
                        </div>

                        <div>
                            <span class="text-sm text-gray-600">Created by</span>
                            <div class="font-semibold">{{ $league->admin->name }}</div>
                        </div>

                        <div>
                            <span class="text-sm text-gray-600">League Type</span>
                            <div class="font-semibold">{{ ucfirst($league->type) }}</div>
                        </div>

                        <div>
                            <span class="text-sm text-gray-600">Privacy</span>
                            <div class="font-semibold">{{ ucfirst($league->privacy) }}</div>
                        </div>

                        <div>
                            <span class="text-sm text-gray-600">Members</span>
                            <div class="font-semibold">{{ $league->current_entries }}/{{ $league->max_entries }}</div>
                        </div>

                        <div>
                            <span class="text-sm text-gray-600">Created</span>
                            <div class="font-semibold">{{ $league->created_at->format('M j, Y') }}</div>
                        </div>
                    </div>

                    @if($league->description)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <span class="text-sm text-gray-600">Description</span>
                            <p class="text-sm mt-1">{{ $league->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                @if($isMember)
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>

                        <div class="space-y-3">
                            <button onclick="copyToClipboard('{{ $league->league_code }}')" class="w-full text-left bg-gray-50 hover:bg-gray-100 p-3 rounded-lg transition-colors">
                                <div class="font-semibold text-sm">Share League Code</div>
                                <div class="text-xs text-gray-600">Copy code to invite friends</div>
                            </button>

                            <a href="{{ route('leagues.index') }}" class="block w-full text-left bg-gray-50 hover:bg-gray-100 p-3 rounded-lg transition-colors">
                                <div class="font-semibold text-sm">View All Leagues</div>
                                <div class="text-xs text-gray-600">Go to leagues dashboard</div>
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show temporary success message
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '✓ Copied!';
        button.className = button.className.replace('text-fpl-purple', 'text-fpl-green');

        setTimeout(() => {
            button.textContent = originalText;
            button.className = button.className.replace('text-fpl-green', 'text-fpl-purple');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
</body>
</html>
