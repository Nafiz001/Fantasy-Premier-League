<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pick Your Squad - Fantasy Premier League</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
                        'fpl-cyan': '#36eca9',
                        'fpl-blue': '#37003c',
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
        .pitch-gradient {
            background: linear-gradient(135deg, #00c851 0%, #007e33 100%);
        }
        .player-slot {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .player-slot:hover {
            transform: scale(1.05);
        }
        .player-slot.empty {
            border: 3px dashed rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.1);
        }
        .player-slot.filled {
            background: rgba(56, 0, 60, 0.9);
            border: 3px solid #00ff85;
            box-shadow: 0 4px 12px rgba(0, 255, 133, 0.3);
        }
        .position-line {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin: 20px 0;
        }
        .player-card {
            background: white;
            border-radius: 8px;
            padding: 12px;
            margin: 4px 0;
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        .player-card:hover {
            background: #f8f9fa;
            border-left-color: #00ff85;
            transform: translateX(4px);
        }
        .player-card.selected {
            background: #e8f5e8;
            border-left-color: #00c851;
        }
        .stats-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
        }
        .stats-fill {
            height: 100%;
            background: linear-gradient(90deg, #00c851 0%, #00ff85 100%);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Header Navigation - Simplified for Squad Selection -->
    <header class="bg-white/95 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <img src="/logo.png" alt="Fantasy Premier League Logo" class="w-10 h-10 rounded-full">
                    <span class="text-lg font-bold text-fpl-purple">Fantasy</span>
                </div>

                <!-- Squad Selection Navigation -->
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="#" class="text-fpl-purple hover:text-fpl-magenta font-medium transition-colors">Pick Squad</a>
                    <a href="#" class="text-gray-500 cursor-not-allowed">My Team</a>
                    <a href="#" class="text-gray-500 cursor-not-allowed">Transfers</a>
                    <a href="#" class="text-gray-500 cursor-not-allowed">Statistics</a>
                    <a href="#" class="text-gray-500 cursor-not-allowed">Fantasy</a>
                </nav>

                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Welcome, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Squad Selection Header -->
            <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Player Selection</h1>
                        @if(isset($nextGameweek))
                            <p class="text-gray-600">Select 15 players to complete your squad. Deadline for GW {{ $nextGameweek->gameweek_id }} is {{ date('D, j M, H:i', strtotime($nextGameweek->deadline_time)) }}</p>
                        @else
                            <p class="text-gray-600">Select 15 players to complete your squad.</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-900">£<span id="budget">100.0</span>m</div>
                        <div class="text-sm text-gray-600">Budget Remaining</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Player Lists -->
                <div class="lg:col-span-1 space-y-4">
                    <!-- Team Name Input -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Squad Selection</h3>
                        <input type="text" id="team-name" placeholder="Your team name"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-fpl-green focus:border-transparent">
                        <div class="mt-2 text-sm text-gray-500">Not selected</div>

                        <!-- Auto Pick Button -->
                        <button id="auto-pick-btn"
                                class="w-full mt-3 bg-fpl-magenta hover:bg-fpl-magenta/90 text-white font-semibold py-2 px-4 rounded-md transition-colors">
                            🎲 Auto Pick Squad
                        </button>
                        <p class="text-xs text-gray-500 mt-1">Randomly selects 15 players within £100m budget</p>
                    </div>

                    <!-- Position Filters -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-4">
                        <div class="flex space-x-2 mb-4">
                            <button class="position-filter active px-3 py-1 rounded-full text-sm font-medium bg-fpl-purple text-white" data-position="all">
                                All players
                            </button>
                            <button class="position-filter px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300" data-position="Goalkeeper">
                                GKP
                            </button>
                            <button class="position-filter px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300" data-position="Defender">
                                DEF
                            </button>
                            <button class="position-filter px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300" data-position="Midfielder">
                                MID
                            </button>
                            <button class="position-filter px-3 py-1 rounded-full text-sm font-medium bg-gray-200 text-gray-700 hover:bg-gray-300" data-position="Forward">
                                FWD
                            </button>
                        </div>

                        <!-- Search -->
                        <input type="text" id="player-search" placeholder="Search for a player"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-fpl-green focus:border-transparent mb-4">

                        <!-- Filters -->
                        <div class="flex space-x-2 mb-4">
                            <select id="team-filter" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">All teams</option>
                                @foreach($goalkeepers->unique('team_name') as $team)
                                    <option value="{{ $team->team_name }}">{{ $team->team_name }}</option>
                                @endforeach
                            </select>
                            <select id="price-filter" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                                <option value="">Any price</option>
                                <option value="0-5">£0.0 - £5.0</option>
                                <option value="5-7">£5.0 - £7.0</option>
                                <option value="7-10">£7.0 - £10.0</option>
                                <option value="10+">£10.0+</option>
                            </select>
                        </div>

                        <!-- Sort -->
                        <select id="sort-by" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm mb-4">
                            <option value="total_points">Sort by total points</option>
                            <option value="price_asc">Price (low to high)</option>
                            <option value="price_desc">Price (high to low)</option>
                            <option value="selected_by_percent">Selected by %</option>
                            <option value="form">Form</option>
                        </select>

                        <!-- Player List -->
                        <div id="player-list" class="max-h-96 overflow-y-auto">
                            <!-- Players will be loaded here -->
                        </div>

                        <!-- Pagination -->
                        <div class="flex justify-center mt-4">
                            <div class="flex space-x-2">
                                <button class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300">1</button>
                                <button class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300">2</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Squad Formation Display -->
                <div class="lg:col-span-2">
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Squad Selection</h3>
                            <div class="flex items-center space-x-4">
                                <span class="text-sm text-gray-600">List View</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer" id="view-toggle">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-fpl-green/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-fpl-green"></div>
                                </label>
                                <span class="text-sm text-gray-600">Pitch View</span>
                            </div>
                        </div>

                        <!-- Formation Display -->
                        <div id="formation-view" class="pitch-gradient rounded-lg p-8 min-h-96 relative">
                            <!-- Goalkeepers -->
                            <div class="position-line">
                                <div class="player-slot empty goalkeeper-slot" data-position="Goalkeeper" data-index="0">
                                    <span class="text-white text-xs">GK</span>
                                </div>
                            </div>

                            <!-- Defenders -->
                            <div class="position-line">
                                <div class="player-slot empty defender-slot" data-position="Defender" data-index="0">
                                    <span class="text-white text-xs">DEF</span>
                                </div>
                                <div class="player-slot empty defender-slot" data-position="Defender" data-index="1">
                                    <span class="text-white text-xs">DEF</span>
                                </div>
                                <div class="player-slot empty defender-slot" data-position="Defender" data-index="2">
                                    <span class="text-white text-xs">DEF</span>
                                </div>
                                <div class="player-slot empty defender-slot" data-position="Defender" data-index="3">
                                    <span class="text-white text-xs">DEF</span>
                                </div>
                                <div class="player-slot empty defender-slot" data-position="Defender" data-index="4">
                                    <span class="text-white text-xs">DEF</span>
                                </div>
                            </div>

                            <!-- Midfielders -->
                            <div class="position-line">
                                <div class="player-slot empty midfielder-slot" data-position="Midfielder" data-index="0">
                                    <span class="text-white text-xs">MID</span>
                                </div>
                                <div class="player-slot empty midfielder-slot" data-position="Midfielder" data-index="1">
                                    <span class="text-white text-xs">MID</span>
                                </div>
                                <div class="player-slot empty midfielder-slot" data-position="Midfielder" data-index="2">
                                    <span class="text-white text-xs">MID</span>
                                </div>
                                <div class="player-slot empty midfielder-slot" data-position="Midfielder" data-index="3">
                                    <span class="text-white text-xs">MID</span>
                                </div>
                                <div class="player-slot empty midfielder-slot" data-position="Midfielder" data-index="4">
                                    <span class="text-white text-xs">MID</span>
                                </div>
                            </div>

                            <!-- Forwards -->
                            <div class="position-line">
                                <div class="player-slot empty forward-slot" data-position="Forward" data-index="0">
                                    <span class="text-white text-xs">FWD</span>
                                </div>
                                <div class="player-slot empty forward-slot" data-position="Forward" data-index="1">
                                    <span class="text-white text-xs">FWD</span>
                                </div>
                                <div class="player-slot empty forward-slot" data-position="Forward" data-index="2">
                                    <span class="text-white text-xs">FWD</span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="absolute bottom-4 right-4">
                                <button id="save-squad" class="bg-fpl-green text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    Save Squad
                                </button>
                            </div>
                        </div>

                        <!-- Squad Summary -->
                        <div class="mt-4 grid grid-cols-4 gap-4 text-center">
                            <div>
                                <div class="text-sm text-gray-600">Goalkeepers</div>
                                <div class="font-semibold" id="gk-count">0/2</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Defenders</div>
                                <div class="font-semibold" id="def-count">0/5</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Midfielders</div>
                                <div class="font-semibold" id="mid-count">0/5</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Forwards</div>
                                <div class="font-semibold" id="fwd-count">0/3</div>
                            </div>
                        </div>
                    </div>

                    <!-- Fixtures Preview -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mt-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Next Gameweek</h3>
                        <div class="text-center text-gray-500">
                            @if(isset($nextGameweek))
                                <p class="text-lg font-bold text-gray-900">Gameweek {{ $nextGameweek->gameweek_id }}</p>
                                <p class="text-sm">Deadline: {{ date('D j M, H:i', strtotime($nextGameweek->deadline_time)) }}</p>
                                @if(!$nextGameweek->finished)
                                    @php
                                        $deadline = new DateTime($nextGameweek->deadline_time);
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
                                    <p class="text-xs text-gray-400 mt-2">{{ $timeRemaining }}</p>
                                @endif
                            @else
                                <p>Gameweek 4</p>
                                <p class="text-sm">Deadline: Sat 14 Sep, 16:00</p>
                            @endif
                        </div>
                        <!-- Fixture list would go here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ================== GLOBAL CSRF INTERCEPTOR ==================
        // Intercept all fetch requests to add CSRF token automatically
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            const [resource, config] = args;

            if (config && ['POST', 'PUT', 'DELETE', 'PATCH'].includes(config.method?.toUpperCase())) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (token) {
                    if (!config.headers) config.headers = {};
                    config.headers['X-CSRF-TOKEN'] = token;
                }
            }

            return originalFetch.apply(this, args);
        };

        // Squad selection data
        let selectedSquad = {
            Goalkeeper: [],
            Defender: [],
            Midfielder: [],
            Forward: []
        };

        let budget = 100.0;
        let allPlayers = @json($goalkeepers->concat($defenders)->concat($midfielders)->concat($forwards));

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            loadPlayerList();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Position filters
            document.querySelectorAll('.position-filter').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.position-filter').forEach(b => {
                        b.classList.remove('active', 'bg-fpl-purple', 'text-white');
                        b.classList.add('bg-gray-200', 'text-gray-700');
                    });
                    this.classList.add('active', 'bg-fpl-purple', 'text-white');
                    this.classList.remove('bg-gray-200', 'text-gray-700');
                    loadPlayerList();
                });
            });

            // Search functionality
            document.getElementById('player-search').addEventListener('input', loadPlayerList);
            document.getElementById('team-filter').addEventListener('change', loadPlayerList);
            document.getElementById('price-filter').addEventListener('change', loadPlayerList);
            document.getElementById('sort-by').addEventListener('change', loadPlayerList);

            // Save squad button
            document.getElementById('save-squad').addEventListener('click', saveSquad);
        }

        function loadPlayerList() {
            const position = document.querySelector('.position-filter.active').dataset.position;
            const search = document.getElementById('player-search').value.toLowerCase();
            const teamFilter = document.getElementById('team-filter').value;
            const priceFilter = document.getElementById('price-filter').value;
            const sortBy = document.getElementById('sort-by').value;

            let filteredPlayers = allPlayers.filter(player => {
                // Position filter
                if (position !== 'all' && player.position !== position) return false;

                // Search filter
                if (search && !player.web_name.toLowerCase().includes(search)) return false;

                // Team filter
                if (teamFilter && player.team_name !== teamFilter) return false;

                // Price filter
                if (priceFilter) {
                    const price = player.price || 5.0;
                    if (priceFilter === '0-5' && price > 5.0) return false;
                    if (priceFilter === '5-7' && (price <= 5.0 || price > 7.0)) return false;
                    if (priceFilter === '7-10' && (price <= 7.0 || price > 10.0)) return false;
                    if (priceFilter === '10+' && price <= 10.0) return false;
                }

                return true;
            });

            // Sort players
            filteredPlayers.sort((a, b) => {
                switch(sortBy) {
                    case 'price_asc': return (a.price || 5.0) - (b.price || 5.0);
                    case 'price_desc': return (b.price || 5.0) - (a.price || 5.0);
                    case 'selected_by_percent': return (b.selected_by_percent || 0) - (a.selected_by_percent || 0);
                    case 'form': return (b.form || 0) - (a.form || 0);
                    default: return (b.total_points || 0) - (a.total_points || 0);
                }
            });

            renderPlayerList(filteredPlayers);
        }

        function renderPlayerList(players) {
            const container = document.getElementById('player-list');
            container.innerHTML = '';

            players.slice(0, 20).forEach(player => {
                const isSelected = Object.values(selectedSquad).flat().some(p => p.fpl_id === player.fpl_id);
                const div = document.createElement('div');
                div.className = `player-card ${isSelected ? 'selected' : ''}`;
                div.dataset.playerId = player.fpl_id;

                div.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <div class="relative w-12 h-12 flex items-center justify-center">
                                    <img src="https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_${player.team_id}-110.png"
                                         alt="${player.team_name} Jersey"
                                         class="w-10 h-10 object-contain"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                    <div class="w-8 h-8 bg-gray-200 rounded-full hidden items-center justify-center text-xs font-bold">
                                        ${player.team_short || 'TM'}
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium text-sm">${player.web_name}</div>
                                    <div class="text-xs text-gray-500">${player.position}</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-semibold">£${(player.price || 5.0).toFixed(1)}m</div>
                            <div class="text-xs text-gray-500">${player.total_points || 0} pts</div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="stats-bar">
                            <div class="stats-fill" style="width: ${Math.min((player.total_points || 0) / 100 * 100, 100)}%"></div>
                        </div>
                    </div>
                `;

                div.addEventListener('click', () => selectPlayer(player));
                container.appendChild(div);
            });
        }

        function selectPlayer(player) {
            const position = player.position;
            const maxCounts = {
                'Goalkeeper': 2,
                'Defender': 5,
                'Midfielder': 5,
                'Forward': 3
            };

            // Check if already selected
            if (selectedSquad[position].some(p => p.fpl_id === player.fpl_id)) {
                deselectPlayer(player);
                return;
            }

            // Check position limits
            if (selectedSquad[position].length >= maxCounts[position]) {
                alert(`You can only select ${maxCounts[position]} ${position.toLowerCase()}s`);
                return;
            }

            // Check team limits (max 3 players from same team)
            const teamCount = Object.values(selectedSquad).flat().filter(p => p.team_name === player.team_name).length;
            if (teamCount >= 3) {
                alert(`You can only select 3 players from ${player.team_name}`);
                return;
            }

            // Check budget
            const playerPrice = player.price || 5.0;
            if (budget < playerPrice) {
                alert('Insufficient budget');
                return;
            }

            // Add player
            selectedSquad[position].push(player);
            budget -= playerPrice;

            updateDisplay();
            loadPlayerList(); // Refresh to show selection
        }

        function deselectPlayer(player) {
            const position = player.position;
            selectedSquad[position] = selectedSquad[position].filter(p => p.fpl_id !== player.fpl_id);
            budget += (player.price || 5.0);

            updateDisplay();
            loadPlayerList(); // Refresh to show deselection
        }

        function updateDisplay() {
            // Update budget
            document.getElementById('budget').textContent = budget.toFixed(1);

            // Update counts
            document.getElementById('gk-count').textContent = `${selectedSquad.Goalkeeper.length}/2`;
            document.getElementById('def-count').textContent = `${selectedSquad.Defender.length}/5`;
            document.getElementById('mid-count').textContent = `${selectedSquad.Midfielder.length}/5`;
            document.getElementById('fwd-count').textContent = `${selectedSquad.Forward.length}/3`;

            // Update formation slots
            updateFormationSlots();

            // Check if squad is complete
            const totalSelected = Object.values(selectedSquad).flat().length;
            const saveButton = document.getElementById('save-squad');
            saveButton.disabled = totalSelected !== 15;
        }

        function updateFormationSlots() {
            // Update goalkeeper slots
            const gkSlots = document.querySelectorAll('.goalkeeper-slot');
            selectedSquad.Goalkeeper.forEach((player, index) => {
                if (gkSlots[index]) {
                    gkSlots[index].className = 'player-slot filled goalkeeper-slot';
                    gkSlots[index].innerHTML = `
                        <div class="flex flex-col items-center">
                            <img src="https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_${player.team_id}-110.png"
                                 alt="${player.web_name}"
                                 class="w-8 h-8 object-contain mb-1"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                            <div class="w-6 h-6 bg-yellow-400 rounded-full hidden items-center justify-center text-xs font-bold">
                                GK
                            </div>
                            <span class="text-white text-xs truncate max-w-16">${player.web_name}</span>
                        </div>
                    `;
                }
            });

            // Update defender slots
            const defSlots = document.querySelectorAll('.defender-slot');
            selectedSquad.Defender.forEach((player, index) => {
                if (defSlots[index]) {
                    defSlots[index].className = 'player-slot filled defender-slot';
                    defSlots[index].innerHTML = `
                        <div class="flex flex-col items-center">
                            <img src="https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_${player.team_id}-110.png"
                                 alt="${player.web_name}"
                                 class="w-8 h-8 object-contain mb-1"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                            <div class="w-6 h-6 bg-blue-400 rounded-full hidden items-center justify-center text-xs font-bold">
                                DEF
                            </div>
                            <span class="text-white text-xs truncate max-w-16">${player.web_name}</span>
                        </div>
                    `;
                }
            });

            // Update midfielder slots
            const midSlots = document.querySelectorAll('.midfielder-slot');
            selectedSquad.Midfielder.forEach((player, index) => {
                if (midSlots[index]) {
                    midSlots[index].className = 'player-slot filled midfielder-slot';
                    midSlots[index].innerHTML = `
                        <div class="flex flex-col items-center">
                            <img src="https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_${player.team_id}-110.png"
                                 alt="${player.web_name}"
                                 class="w-8 h-8 object-contain mb-1"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                            <div class="w-6 h-6 bg-green-400 rounded-full hidden items-center justify-center text-xs font-bold">
                                MID
                            </div>
                            <span class="text-white text-xs truncate max-w-16">${player.web_name}</span>
                        </div>
                    `;
                }
            });

            // Update forward slots
            const fwdSlots = document.querySelectorAll('.forward-slot');
            selectedSquad.Forward.forEach((player, index) => {
                if (fwdSlots[index]) {
                    fwdSlots[index].className = 'player-slot filled forward-slot';
                    fwdSlots[index].innerHTML = `
                        <div class="flex flex-col items-center">
                            <img src="https://fantasy.premierleague.com/dist/img/shirts/standard/shirt_${player.team_id}-110.png"
                                 alt="${player.web_name}"
                                 class="w-8 h-8 object-contain mb-1"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                            <div class="w-6 h-6 bg-red-400 rounded-full hidden items-center justify-center text-xs font-bold">
                                FWD
                            </div>
                            <span class="text-white text-xs truncate max-w-16">${player.web_name}</span>
                        </div>
                    `;
                }
            });
        }

        function saveSquad() {
            const teamName = document.getElementById('team-name').value.trim();
            if (!teamName) {
                alert('Please enter a team name');
                return;
            }

            const totalSelected = Object.values(selectedSquad).flat().length;
            if (totalSelected !== 15) {
                alert('Please select exactly 15 players');
                return;
            }

            // Prepare data
            const squadData = {
                team_name: teamName,
                players: Object.values(selectedSquad).flat().map(p => p.fpl_id),
                formation: '4-4-2', // Default formation
                budget_used: 100.0 - budget
            };

            // Send to server
            fetch('{{ route("squad.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(squadData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Squad saved successfully!');
                    window.location.href = data.redirect;
                } else {
                    alert('Error saving squad: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving squad');
            });
        }

        // Auto Pick Squad functionality
        document.getElementById('auto-pick-btn').addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '🔄 Generating Squad...';

            fetch('{{ route("squad.auto-pick") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Auto-pick response:', data); // Debug log
                if (data.success) {
                    // Clear current squad
                    selectedSquad = {
                        Goalkeeper: [],
                        Defender: [],
                        Midfielder: [],
                        Forward: []
                    };

                    // Add auto-picked players to squad
                    data.squad.forEach(player => {
                        console.log('Adding player:', player); // Debug log
                        const position = player.position; // Use exact position name from backend

                        if (selectedSquad[position]) {
                            selectedSquad[position].push({
                                fpl_id: player.id,
                                web_name: player.name,
                                position: player.position,
                                team_id: player.team_id,
                                team_short: player.team,
                                team_name: player.team, // Add team_name for team limit check
                                price: player.price,
                                jersey_url: player.jersey_url,
                                photo_url: player.photo_url,
                                total_points: player.total_points
                            });
                        }
                    });

                    console.log('Final selectedSquad:', selectedSquad); // Debug log
                    console.log('Budget remaining:', data.budget_remaining); // Debug log

                    // Update budget
                    budget = data.budget_remaining;
                    document.getElementById('budget').textContent = budget.toFixed(1);

                    // Update squad display and counts
                    updateSquadDisplay();

                    // Show success message
                    const message = data.message ?
                        `${data.message}` :
                        `Auto-pick successful! Selected 15 players for £${data.total_cost.toFixed(1)}m`;
                    alert(message);

                    // Redirect to dashboard if provided
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }

                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error generating auto-pick squad');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '🎲 Auto Pick Squad';
            });
        });

        // Update squad display after auto-pick
        function updateSquadDisplay() {
            // Call the existing updateDisplay function
            updateDisplay();
            // Refresh all player lists to update selected states
            loadPlayerList();
        }
    </script>
</body>
</html>
