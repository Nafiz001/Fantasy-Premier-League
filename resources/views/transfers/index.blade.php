<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transfers - Fantasy Premier League</title>

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
            cursor: pointer;
        }

        .player-card-pitch:hover {
            transform: scale(1.05);
        }

        .player-card-pitch.selected-out {
            border: 3px solid #ef4444 !important;
            background-color: rgba(239, 68, 68, 0.1);
        }

        .available-player-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .available-player-card:hover {
            transform: scale(1.02);
            background-color: rgba(59, 130, 246, 0.1);
        }

        .available-player-card.selected-in {
            border: 3px solid #10b981;
            background-color: rgba(16, 185, 129, 0.1);
        }

        .pitch-background {
            background: linear-gradient(90deg, #4ade80 0%, #22c55e 50%, #16a34a 100%);
            position: relative;
        }

        .pitch-lines::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                linear-gradient(to bottom, transparent 0%, rgba(255,255,255,0.2) 1%, transparent 2%),
                linear-gradient(to bottom, transparent 98%, rgba(255,255,255,0.2) 99%, transparent 100%),
                linear-gradient(to right, transparent 49%, rgba(255,255,255,0.2) 50%, transparent 51%);
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation Header -->
    @include('partials.navigation')

    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="grid grid-cols-12 gap-6">
            <!-- Left Sidebar - Find Players -->
            <div class="col-span-3 bg-white rounded-lg shadow-lg p-4">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Find a player</h3>
                    <div class="text-sm text-gray-500 mb-4">Gameweek {{ $transferData['gameweek'] ?? 1 }}</div>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <input type="text" id="player-search" placeholder="Search by name"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Filters -->
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <select id="position-filter" class="px-2 py-1 border border-gray-300 rounded text-sm">
                        <option value="">All Pos</option>
                        <option value="Goalkeeper">GK</option>
                        <option value="Defender">DEF</option>
                        <option value="Midfielder">MID</option>
                        <option value="Forward">FWD</option>
                    </select>
                    <select class="px-2 py-1 border border-gray-300 rounded text-sm">
                        <option>Total</option>
                    </select>
                    <select class="px-2 py-1 border border-gray-300 rounded text-sm">
                        <option>£0.0m</option>
                    </select>
                </div>

                <!-- Players shown counter -->
                <div class="bg-blue-500 text-white text-center py-2 rounded mb-4">
                    <span id="players-shown">
                        {{
                            ($allPlayers['Goalkeeper']->count() ?? 0) +
                            ($allPlayers['Defender']->count() ?? 0) +
                            ($allPlayers['Midfielder']->count() ?? 0) +
                            ($allPlayers['Forward']->count() ?? 0)
                        }} players shown
                    </span>
                </div>

                <!-- Available Players List -->
                <div id="available-players" class="space-y-4 max-h-96 overflow-y-auto">
                    @foreach(['Goalkeeper', 'Defender', 'Midfielder', 'Forward'] as $position)
                        <div class="position-group" data-position="{{ $position }}">
                            <h4 class="font-semibold text-gray-700 mb-2">
                                {{ $position === 'Goalkeeper' ? 'Goalkeepers' : $position.'s' }}
                            </h4>
                            @if(isset($allPlayers[$position]))
                                @foreach($allPlayers[$position] as $player)
                                    <div class="available-player-card flex items-center justify-between p-2 border rounded hover:bg-gray-50"
                                         data-player-id="{{ $player->id }}"
                                         data-player-name="{{ $player->web_name ?? $player->first_name.' '.$player->second_name }}"
                                         data-player-price="{{ $player->price }}"
                                         data-player-position="{{ $player->position }}"
                                         data-player-team="{{ $player->team_short }}">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-8 h-8 bg-gray-100 rounded flex items-center justify-center">
                                                <img src="{{ $player->jersey_url }}" alt="Jersey" class="w-6 h-6">
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium">{{ $player->web_name ?? $player->first_name.' '.$player->second_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $player->team_short ?? $player->team_short_name }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-semibold">£{{ number_format($player->price, 1) }}m</div>
                                            <div class="text-xs text-gray-500">{{ $player->total_points ?? 0 }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Center - Squad Selection and Pitch -->
            <div class="col-span-6">
                <!-- Top Stats Bar -->
                <div class="bg-white rounded-lg shadow-lg p-4 mb-4 flex justify-center items-center space-x-8">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ (($currentSquad['goalkeepers']->count() ?? 0) + ($currentSquad['defenders']->count() ?? 0) + ($currentSquad['midfielders']->count() ?? 0) + ($currentSquad['forwards']->count() ?? 0)) }}/15</div>
                        <div class="text-sm text-gray-600">Players selected</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">£{{ number_format($transferData['budget_remaining'], 1) }}m</div>
                        <div class="text-sm text-gray-600">Bank</div>
                    </div>
                </div>

                <!-- Unlimited Transfers Notice -->
                <div class="bg-fpl-purple text-white text-center py-3 rounded-lg mb-4">
                    You can make unlimited free transfers before the Gameweek {{ $transferData['gameweek'] ?? 1 }} deadline
                </div>

                <!-- Football Pitch - Exact copy from squad view -->
                <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                    <div class="pitch-background rounded-lg relative h-[600px] pitch-lines">
                        <div class="absolute inset-4">
                            <!-- Penalty Areas (top and bottom) -->
                            <div class="absolute top-4 left-1/2 transform -translate-x-1/2 w-48 h-24 border-2 border-white/60"></div>
                            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-48 h-24 border-2 border-white/60"></div>

                            <!-- Center Circle -->
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-24 h-24 border-2 border-white/60 rounded-full"></div>
                            <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-2 h-2 bg-white/60 rounded-full"></div>

                            <!-- Center Line -->
                            <div class="absolute top-1/2 left-4 right-4 h-0.5 bg-white/60"></div>
                        </div>

                        <!-- Player Cards positioned exactly like squad view -->

                        <!-- Goalkeeper (TOP - 8% from top) -->
                        <div class="absolute top-12 left-1/2 transform -translate-x-1/2">
                            @if(isset($currentSquad['goalkeepers']) && $currentSquad['goalkeepers']->isNotEmpty())
                                @php $goalkeeper = $currentSquad['goalkeepers']->first(); @endphp
                                <div class="player-card-pitch"
                                     data-player-id="{{ $goalkeeper->id }}"
                                     data-player-name="{{ $goalkeeper->web_name ?? $goalkeeper->first_name.' '.$goalkeeper->second_name }}"
                                     data-player-price="{{ $goalkeeper->price }}"
                                     data-player-position="{{ $goalkeeper->position }}">
                                    <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 relative">
                                        <img src="{{ $goalkeeper->jersey_url }}"
                                             alt="Jersey"
                                             class="w-12 h-12 rounded">
                                        <!-- Price badge -->
                                        <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                                            {{ number_format($goalkeeper->price, 1) }}
                                        </div>
                                    </div>
                                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                        <div class="text-xs font-semibold text-gray-900">{{ $goalkeeper->web_name ?? $goalkeeper->first_name.' '.$goalkeeper->second_name }}</div>
                                        <div class="text-xs text-gray-600">{{ $goalkeeper->team_short ?? $goalkeeper->team_short_name }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Defenders Row (25% from top) - Full width spread -->
                        <div class="absolute top-36 left-0 right-0 flex justify-between px-16">
                            @if(isset($currentSquad['defenders']))
                                @foreach($currentSquad['defenders']->take(4) as $defender)
                                    <div class="player-card-pitch"
                                         data-player-id="{{ $defender->id }}"
                                         data-player-name="{{ $defender->web_name ?? $defender->first_name.' '.$defender->second_name }}"
                                         data-player-price="{{ $defender->price }}"
                                         data-player-position="{{ $defender->position }}">
                                        <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 relative">
                                            <img src="{{ $defender->jersey_url }}"
                                                 alt="Jersey"
                                                 class="w-12 h-12 rounded">
                                            <!-- Price badge -->
                                            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                                                {{ number_format($defender->price, 1) }}
                                            </div>
                                        </div>
                                        <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                            <div class="text-xs font-semibold text-gray-900">{{ $defender->web_name ?? $defender->first_name.' '.$defender->second_name }}</div>
                                            <div class="text-xs text-gray-600">{{ $defender->team_short ?? $defender->team_short_name }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Midfielders Row (55% from top) - Full width spread -->
                        <div class="absolute top-80 left-0 right-0 flex justify-between px-16">
                            @if(isset($currentSquad['midfielders']))
                                @foreach($currentSquad['midfielders']->take(4) as $midfielder)
                                    <div class="player-card-pitch"
                                         data-player-id="{{ $midfielder->id }}"
                                         data-player-name="{{ $midfielder->web_name ?? $midfielder->first_name.' '.$midfielder->second_name }}"
                                         data-player-price="{{ $midfielder->price }}"
                                         data-player-position="{{ $midfielder->position }}">
                                        <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 relative">
                                            <img src="{{ $midfielder->jersey_url }}"
                                                 alt="Jersey"
                                                 class="w-12 h-12 rounded">
                                            <!-- Price badge -->
                                            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                                                {{ number_format($midfielder->price, 1) }}
                                            </div>
                                        </div>
                                        <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                            <div class="text-xs font-semibold text-gray-900">{{ $midfielder->web_name ?? $midfielder->first_name.' '.$midfielder->second_name }}</div>
                                            <div class="text-xs text-gray-600">{{ $midfielder->team_short ?? $midfielder->team_short_name }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Forwards Row (80% from top) - Spread apart -->
                        <div class="absolute top-[480px] left-0 right-0 flex justify-center space-x-32 px-16">
                            @if(isset($currentSquad['forwards']))
                                @foreach($currentSquad['forwards']->take(2) as $forward)
                                    <div class="player-card-pitch"
                                         data-player-id="{{ $forward->id }}"
                                         data-player-name="{{ $forward->web_name ?? $forward->first_name.' '.$forward->second_name }}"
                                         data-player-price="{{ $forward->price }}"
                                         data-player-position="{{ $forward->position }}">
                                        <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 relative">
                                            <img src="{{ $forward->jersey_url }}"
                                                 alt="Jersey"
                                                 class="w-12 h-12 rounded">
                                            <!-- Price badge -->
                                            <div class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                                                {{ number_format($forward->price, 1) }}
                                            </div>
                                        </div>
                                        <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                            <div class="text-xs font-semibold text-gray-900">{{ $forward->web_name ?? $forward->first_name.' '.$forward->second_name }}</div>
                                            <div class="text-xs text-gray-600">{{ $forward->team_short ?? $forward->team_short_name }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
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
                            @if(isset($currentSquad['goalkeepers'][1]))
                                <div class="player-card-pitch"
                                     data-player-id="{{ $currentSquad['goalkeepers'][1]->id }}"
                                     data-player-name="{{ $currentSquad['goalkeepers'][1]->web_name ?? $currentSquad['goalkeepers'][1]->first_name.' '.$currentSquad['goalkeepers'][1]->second_name }}"
                                     data-player-price="{{ $currentSquad['goalkeepers'][1]->price }}"
                                     data-player-position="{{ $currentSquad['goalkeepers'][1]->position }}">
                                    <div class="w-14 h-14 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 mx-auto relative">
                                        <img src="{{ $currentSquad['goalkeepers'][1]->jersey_url }}"
                                             alt="Jersey"
                                             class="w-10 h-10 rounded">
                                        <!-- Price badge -->
                                        <div class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                            {{ number_format($currentSquad['goalkeepers'][1]->price, 1) }}
                                        </div>
                                    </div>
                                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                        <div class="text-xs font-semibold text-gray-900">{{ $currentSquad['goalkeepers'][1]->web_name ?? $currentSquad['goalkeepers'][1]->first_name.' '.$currentSquad['goalkeepers'][1]->second_name }}</div>
                                        <div class="text-xs text-gray-600">{{ $currentSquad['goalkeepers'][1]->team_short ?? $currentSquad['goalkeepers'][1]->team_short_name }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 1. DEF -->
                        <div class="text-center">
                            <div class="text-sm font-medium text-gray-600 mb-2">1. DEF</div>
                            @if(isset($currentSquad['defenders'][4]))
                                <div class="player-card-pitch"
                                     data-player-id="{{ $currentSquad['defenders'][4]->id }}"
                                     data-player-name="{{ $currentSquad['defenders'][4]->web_name ?? $currentSquad['defenders'][4]->first_name.' '.$currentSquad['defenders'][4]->second_name }}"
                                     data-player-price="{{ $currentSquad['defenders'][4]->price }}"
                                     data-player-position="{{ $currentSquad['defenders'][4]->position }}">
                                    <div class="w-14 h-14 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 mx-auto relative">
                                        <img src="{{ $currentSquad['defenders'][4]->jersey_url }}"
                                             alt="Jersey"
                                             class="w-10 h-10 rounded">
                                        <!-- Price badge -->
                                        <div class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                            {{ number_format($currentSquad['defenders'][4]->price, 1) }}
                                        </div>
                                    </div>
                                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                        <div class="text-xs font-semibold text-gray-900">{{ $currentSquad['defenders'][4]->web_name ?? $currentSquad['defenders'][4]->first_name.' '.$currentSquad['defenders'][4]->second_name }}</div>
                                        <div class="text-xs text-gray-600">{{ $currentSquad['defenders'][4]->team_short ?? $currentSquad['defenders'][4]->team_short_name }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 2. MID -->
                        <div class="text-center">
                            <div class="text-sm font-medium text-gray-600 mb-2">2. MID</div>
                            @if(isset($currentSquad['midfielders'][4]))
                                <div class="player-card-pitch"
                                     data-player-id="{{ $currentSquad['midfielders'][4]->id }}"
                                     data-player-name="{{ $currentSquad['midfielders'][4]->web_name ?? $currentSquad['midfielders'][4]->first_name.' '.$currentSquad['midfielders'][4]->second_name }}"
                                     data-player-price="{{ $currentSquad['midfielders'][4]->price }}"
                                     data-player-position="{{ $currentSquad['midfielders'][4]->position }}">
                                    <div class="w-14 h-14 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 mx-auto relative">
                                        <img src="{{ $currentSquad['midfielders'][4]->jersey_url }}"
                                             alt="Jersey"
                                             class="w-10 h-10 rounded">
                                        <!-- Price badge -->
                                        <div class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                            {{ number_format($currentSquad['midfielders'][4]->price, 1) }}
                                        </div>
                                    </div>
                                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                        <div class="text-xs font-semibold text-gray-900">{{ $currentSquad['midfielders'][4]->web_name ?? $currentSquad['midfielders'][4]->first_name.' '.$currentSquad['midfielders'][4]->second_name }}</div>
                                        <div class="text-xs text-gray-600">{{ $currentSquad['midfielders'][4]->team_short ?? $currentSquad['midfielders'][4]->team_short_name }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- 3. FWD -->
                        <div class="text-center">
                            <div class="text-sm font-medium text-gray-600 mb-2">3. FWD</div>
                            @if(isset($currentSquad['forwards'][2]))
                                <div class="player-card-pitch"
                                     data-player-id="{{ $currentSquad['forwards'][2]->id }}"
                                     data-player-name="{{ $currentSquad['forwards'][2]->web_name ?? $currentSquad['forwards'][2]->first_name.' '.$currentSquad['forwards'][2]->second_name }}"
                                     data-player-price="{{ $currentSquad['forwards'][2]->price }}"
                                     data-player-position="{{ $currentSquad['forwards'][2]->position }}">
                                    <div class="w-14 h-14 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2 mx-auto relative">
                                        <img src="{{ $currentSquad['forwards'][2]->jersey_url }}"
                                             alt="Jersey"
                                             class="w-10 h-10 rounded">
                                        <!-- Price badge -->
                                        <div class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                            {{ number_format($currentSquad['forwards'][2]->price, 1) }}
                                        </div>
                                    </div>
                                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                                        <div class="text-xs font-semibold text-gray-900">{{ $currentSquad['forwards'][2]->web_name ?? $currentSquad['forwards'][2]->first_name.' '.$currentSquad['forwards'][2]->second_name }}</div>
                                        <div class="text-xs text-gray-600">{{ $currentSquad['forwards'][2]->team_short ?? $currentSquad['forwards'][2]->team_short_name }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-center space-x-4">
                    <button id="reset-transfers" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold">
                        Reset
                    </button>
                    <button id="make-transfers" class="bg-fpl-purple hover:bg-purple-800 text-white px-6 py-3 rounded-lg font-semibold">
                        Make Transfers
                    </button>
                </div>
            </div>

            <!-- Right Sidebar - Transfer Stats -->
            <div class="col-span-3 bg-white rounded-lg shadow-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Transfers</h3>
                <div class="text-sm text-gray-600 mb-6">
                    Select a maximum of 3 players from a single team or 'Auto Pick' if you are short of time.
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Free Transfers</span>
                        <span class="font-bold">{{ $transferData['free_transfers'] ?? 1 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Transfers Made</span>
                        <span class="font-bold" id="transfers-made">{{ $transferData['transfers_made'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Point Penalty</span>
                        <span class="font-bold text-red-600" id="point-penalty">{{ $transferData['point_penalty'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Transfer Cost</span>
                        <span class="font-bold" id="transfer-cost">£{{ number_format($transferData['total_cost'], 1) }}m</span>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t">
                    <div class="text-sm text-gray-600 mb-2">Deadline: Sat 13 Sep, 16:00</div>
                </div>

                <!-- Transfer Summary -->
                <div id="transfer-summary" class="mt-6 space-y-3" style="display: none;">
                    <h4 class="font-semibold text-gray-900">Transfers:</h4>
                    <div id="transfer-list"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const transfersOut = [];
        const transfersIn = [];
        const freeTransfers = {{ $transferData['free_transfers'] }};
        let currentBudget = {{ $transferData['budget_remaining'] }};
        let selectedOutPlayer = null;

        function updateTransferStats() {
            const transfersMade = transfersOut.length;
            const extraTransfers = Math.max(0, transfersMade - freeTransfers);
            const pointPenalty = extraTransfers * 4;

            document.getElementById('transfers-made').textContent = transfersMade;
            document.getElementById('point-penalty').textContent = pointPenalty;

            // Calculate cost change
            const outValue = transfersOut.reduce((sum, player) => sum + parseFloat(player.price), 0);
            const inValue = transfersIn.reduce((sum, player) => sum + parseFloat(player.price), 0);
            const costChange = inValue - outValue;

            document.getElementById('transfer-cost').textContent = '£' + costChange.toFixed(1) + 'm';

            // Show/hide transfer summary
            const summaryDiv = document.getElementById('transfer-summary');
            if (transfersMade > 0) {
                summaryDiv.style.display = 'block';
                updateTransferList();
            } else {
                summaryDiv.style.display = 'none';
            }
        }

        function updateTransferList() {
            const listDiv = document.getElementById('transfer-list');
            let html = '';

            for (let i = 0; i < transfersOut.length; i++) {
                const outPlayer = transfersOut[i];
                const inPlayer = transfersIn[i];

                if (outPlayer && inPlayer) {
                    const costDiff = parseFloat(inPlayer.price) - parseFloat(outPlayer.price);
                    html += `
                        <div class="flex items-center justify-between bg-white p-3 rounded border">
                            <div class="flex items-center space-x-3">
                                <span class="text-red-600 text-sm">${outPlayer.name}</span>
                                <span class="text-gray-400">→</span>
                                <span class="text-green-600 text-sm">${inPlayer.name}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                                ${costDiff >= 0 ? '+' : ''}£${costDiff.toFixed(1)}m
                            </div>
                        </div>
                    `;
                }
            }

            listDiv.innerHTML = html;
        }

        // Available player selection
        document.querySelectorAll('.available-player-card').forEach(card => {
            card.addEventListener('click', function() {
                const playerId = this.dataset.playerId;
                const playerName = this.dataset.playerName;
                const playerPrice = this.dataset.playerPrice;
                const playerPosition = this.dataset.playerPosition;

                // Check if a player from the same position is selected for transfer out
                const outPlayerIndex = transfersOut.findIndex(p => p.position === playerPosition);
                if (outPlayerIndex < 0) {
                    alert('Please select a ' + playerPosition.toLowerCase() + ' from your squad first.');
                    return;
                }

                // Check if already selected for transfer in
                const existingIndex = transfersIn.findIndex(p => p.id === playerId);
                if (existingIndex >= 0) {
                    // Remove from transfer in
                    transfersIn.splice(existingIndex, 1);
                    this.classList.remove('selected-in');
                } else {
                    // Check if player already in squad
                    const squadPlayerIds = Array.from(document.querySelectorAll('.player-card-pitch')).map(p => p.dataset.playerId);
                    if (squadPlayerIds.includes(playerId)) {
                        alert('This player is already in your squad.');
                        return;
                    }

                    // Replace existing transfer in for this position
                    transfersIn[outPlayerIndex] = {
                        id: playerId,
                        name: playerName,
                        price: playerPrice,
                        position: playerPosition
                    };

                    // Clear other selections for this position
                    document.querySelectorAll('.available-player-card').forEach(availableCard => {
                        if (availableCard.dataset.playerPosition === playerPosition) {
                            availableCard.classList.remove('selected-in');
                        }
                    });

                    this.classList.add('selected-in');
                }

                updateTransferStats();
            });
        });

        function filterAvailablePlayersByPosition(position) {
            const positionGroups = document.querySelectorAll('#available-players .position-group');
            positionGroups.forEach(group => {
                if (group.dataset.position === position) {
                    group.style.display = 'block';
                } else {
                    group.style.display = 'none';
                }
            });

            // Update position filter dropdown
            document.getElementById('position-filter').value = position;
            updatePlayersShown();
        }

        function showAllPositions() {
            const positionGroups = document.querySelectorAll('#available-players .position-group');
            positionGroups.forEach(group => {
                group.style.display = 'block';
            });
            updatePlayersShown();
        }

        function updatePlayersShown() {
            const visiblePlayers = document.querySelectorAll('#available-players .available-player-card:not([style*="display: none"])').length;
            document.getElementById('players-shown').textContent = visiblePlayers + ' players shown';
        }

        // Position filter
        document.getElementById('position-filter').addEventListener('change', function() {
            const selectedPosition = this.value;
            if (selectedPosition === '') {
                showAllPositions();
            } else {
                filterAvailablePlayersByPosition(selectedPosition);
            }
        });

        // Player search
        document.getElementById('player-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const playerCards = document.querySelectorAll('.available-player-card');

            playerCards.forEach(card => {
                const playerName = card.dataset.playerName.toLowerCase();
                const playerTeam = card.dataset.playerTeam.toLowerCase();
                if (playerName.includes(searchTerm) || playerTeam.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            updatePlayersShown();
        });

        // Reset transfers
        document.getElementById('reset-transfers').addEventListener('click', function() {
            if (confirm('Are you sure you want to reset all transfers?')) {
                transfersOut.length = 0;
                transfersIn.length = 0;
                selectedOutPlayer = null;

                // Reset visual states
                document.querySelectorAll('.player-card-pitch').forEach(card => {
                    card.classList.remove('selected-out');
                });

                document.querySelectorAll('.available-player-card').forEach(card => {
                    card.classList.remove('selected-in');
                });

                // Reset filters
                document.getElementById('position-filter').value = '';
                document.getElementById('player-search').value = '';
                showAllPositions();

                updateTransferStats();
            }
        });

        // Make transfers
        document.getElementById('make-transfers').addEventListener('click', function() {
            if (transfersOut.length !== transfersIn.length) {
                alert('Please complete all transfers before confirming.');
                return;
            }

            if (transfersOut.length === 0) {
                alert('No transfers selected.');
                return;
            }

            const extraTransfers = Math.max(0, transfersOut.length - freeTransfers);
            const pointPenalty = extraTransfers * 4;
            const outValue = transfersOut.reduce((sum, player) => sum + parseFloat(player.price), 0);
            const inValue = transfersIn.reduce((sum, player) => sum + parseFloat(player.price), 0);
            const costChange = inValue - outValue;

            let confirmMessage = `Confirm ${transfersOut.length} transfer(s)?\n\n`;
            confirmMessage += `Transfers:\n`;
            for (let i = 0; i < transfersOut.length; i++) {
                confirmMessage += `${transfersOut[i].name} → ${transfersIn[i].name}\n`;
            }
            confirmMessage += `\nCost: £${costChange.toFixed(1)}m`;
            if (pointPenalty > 0) {
                confirmMessage += `\nPoint penalty: -${pointPenalty} points`;
            }
            confirmMessage += `\n\nProceed with transfers?`;

            if (confirm(confirmMessage)) {
                const transferData = {
                    transfers_out: transfersOut.map(p => p.id),
                    transfers_in: transfersIn.map(p => p.id),
                    _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                };

                // Show loading state
                this.disabled = true;
                this.textContent = 'Processing...';

                fetch('{{ route("transfers.make") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(transferData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Transfers completed successfully!\n\n' + data.message);
                        window.location.reload();
                    } else {
                        alert('Transfer failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing transfers.');
                })
                .finally(() => {
                    this.disabled = false;
                    this.textContent = 'Make Transfers';
                });
            }
        });

        // Initialize
        updatePlayersShown();

        // Add event listeners to all player cards (including substitutes) after DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Re-attach event listeners to all player cards including substitutes
            document.querySelectorAll('.player-card-pitch').forEach(card => {
                card.addEventListener('click', function() {
                    const playerId = this.dataset.playerId;
                    const playerName = this.dataset.playerName;
                    const playerPrice = this.dataset.playerPrice;
                    const playerPosition = this.dataset.playerPosition;

                    // Check if already selected for transfer out
                    const existingIndex = transfersOut.findIndex(p => p.id === playerId);
                    if (existingIndex >= 0) {
                        // Remove from transfer out
                        transfersOut.splice(existingIndex, 1);
                        transfersIn.splice(existingIndex, 1);
                        this.classList.remove('selected-out');

                        // Clear available player selections
                        document.querySelectorAll('.available-player-card').forEach(availableCard => {
                            availableCard.classList.remove('selected-in');
                        });

                        // Reset position filter
                        document.getElementById('position-filter').value = '';
                        showAllPositions();
                    } else {
                        // Add to transfer out
                        transfersOut.push({
                            id: playerId,
                            name: playerName,
                            price: playerPrice,
                            position: playerPosition
                        });
                        this.classList.add('selected-out');

                        // Filter available players by position
                        filterAvailablePlayersByPosition(playerPosition);
                        selectedOutPlayer = {id: playerId, position: playerPosition};
                    }

                    updateTransferStats();
                });
            });
        });
    </script>
</body>
</html>
