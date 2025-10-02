<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pick Team - Fantasy Premier League</title>

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

        .player-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .player-card:hover {
            transform: scale(1.02);
        }

        .player-card.selected {
            ring: 2px solid #00ff85;
        }

        .player-card.captain {
            border: 3px solid #fbbf24;
        }

        .player-card.vice-captain {
            border: 3px solid #6b7280;
        }

        .pitch-slot {
            min-height: 100px;
            transition: all 0.3s ease;
        }

        .pitch-slot.occupied {
            background: rgba(255, 255, 255, 0.9);
        }

        .pitch-slot.droppable {
            background: rgba(0, 255, 133, 0.3);
            border: 2px dashed #00ff85;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    @include('partials.navigation')

    <!-- Main Content -->
    <div class="min-h-screen">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header Section -->
            <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Pick Team</h1>
                        <p class="text-gray-600">Select your starting XI, captain, and vice-captain for Gameweek 3</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-900">Deadline</div>
                        <div class="text-sm text-gray-600">Sat 14 Sep, 16:00</div>
                        <div class="text-xs text-red-600 font-medium">2 days remaining</div>
                    </div>
                </div>
            </div>

            <!-- Team Management Interface -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Pitch and Formation -->
                <div class="lg:col-span-2">
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Formation</h3>
                            <select id="formation-select" class="px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="4-4-2">4-4-2</option>
                                <option value="3-5-2">3-5-2</option>
                                <option value="4-5-1">4-5-1</option>
                                <option value="3-4-3">3-4-3</option>
                                <option value="4-3-3">4-3-3</option>
                            </select>
                        </div>

                        <!-- Football Pitch with Drag & Drop -->
                        <div class="relative w-full h-[600px] bg-gradient-to-b from-green-400 to-green-500 rounded-lg overflow-hidden" id="pitch">
                            <!-- Pitch Lines -->
                            <div class="absolute inset-0">
                                <div class="absolute inset-4 border-2 border-white/60 rounded"></div>
                                <div class="absolute top-4 left-1/2 transform -translate-x-1/2 w-32 h-16 border-2 border-white/60"></div>
                                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-32 h-16 border-2 border-white/60"></div>
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-24 h-24 border-2 border-white/60 rounded-full"></div>
                                <div class="absolute top-1/2 left-4 right-4 h-0.5 bg-white/60"></div>
                            </div>

                            <!-- Player Positions (Initially Empty) -->
                            <!-- Goalkeeper Slot -->
                            <div class="absolute top-12 left-1/2 transform -translate-x-1/2 pitch-slot"
                                 data-position="GK" id="gk-slot">
                                <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                    <span class="text-white/50 text-xs">GK</span>
                                </div>
                            </div>

                            <!-- Defender Slots -->
                            <div class="absolute top-36 left-0 right-0 flex justify-between px-16" id="defender-slots">
                                <div class="pitch-slot" data-position="DEF">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">DEF</span>
                                    </div>
                                </div>
                                <div class="pitch-slot" data-position="DEF">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">DEF</span>
                                    </div>
                                </div>
                                <div class="pitch-slot" data-position="DEF">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">DEF</span>
                                    </div>
                                </div>
                                <div class="pitch-slot" data-position="DEF">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">DEF</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Midfielder Slots -->
                            <div class="absolute top-80 left-0 right-0 flex justify-between px-16" id="midfielder-slots">
                                <div class="pitch-slot" data-position="MID">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">MID</span>
                                    </div>
                                </div>
                                <div class="pitch-slot" data-position="MID">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">MID</span>
                                    </div>
                                </div>
                                <div class="pitch-slot" data-position="MID">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">MID</span>
                                    </div>
                                </div>
                                <div class="pitch-slot" data-position="MID">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">MID</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Forward Slots -->
                            <div class="absolute top-[480px] left-0 right-0 flex justify-center space-x-32 px-16" id="forward-slots">
                                <div class="pitch-slot" data-position="FWD">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">FWD</span>
                                    </div>
                                </div>
                                <div class="pitch-slot" data-position="FWD">
                                    <div class="w-20 h-20 border-2 border-dashed border-white/50 rounded-lg flex items-center justify-center">
                                        <span class="text-white/50 text-xs">FWD</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chips Section -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Chips</h3>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <button class="chip-btn p-4 border-2 border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors" data-chip="wildcard">
                                <div class="text-2xl mb-2">🃏</div>
                                <div class="text-sm font-medium">Wildcard</div>
                                <div class="text-xs text-gray-500">Free transfers</div>
                            </button>
                            <button class="chip-btn p-4 border-2 border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors" data-chip="freehit">
                                <div class="text-2xl mb-2">🎯</div>
                                <div class="text-sm font-medium">Free Hit</div>
                                <div class="text-xs text-gray-500">One week change</div>
                            </button>
                            <button class="chip-btn p-4 border-2 border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors" data-chip="bench-boost">
                                <div class="text-2xl mb-2">⚡</div>
                                <div class="text-sm font-medium">Bench Boost</div>
                                <div class="text-xs text-gray-500">Bench points</div>
                            </button>
                            <button class="chip-btn p-4 border-2 border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors" data-chip="triple-captain">
                                <div class="text-2xl mb-2">👑</div>
                                <div class="text-sm font-medium">Triple Captain</div>
                                <div class="text-xs text-gray-500">3x captain</div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Squad Panel -->
                <div class="space-y-6">
                    <!-- Captain Selection -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Captain & Vice-Captain</h3>
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center text-xs font-bold">C</div>
                                <span class="text-sm" id="captain-name">Select Captain</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-6 h-6 bg-gray-400 rounded-full flex items-center justify-center text-xs font-bold text-white">V</div>
                                <span class="text-sm" id="vice-captain-name">Select Vice-Captain</span>
                            </div>
                        </div>
                    </div>

                    <!-- Squad List -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-4">
                        <h3 class="font-semibold text-gray-900 mb-4">Your Squad</h3>

                        <!-- Goalkeepers -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Goalkeepers</h4>
                            <div class="space-y-2" id="gk-list">
                                @foreach(($squad['goalkeepers'] ?? []) as $gk)
                                    <div class="player-card flex items-center space-x-3 p-2 border border-gray-200 rounded-lg"
                                         draggable="true"
                                         data-player-id="{{ $gk->fpl_id }}"
                                         data-position="Goalkeeper">
                                        <img src="{{ $gk->jersey_url }}" alt="Jersey" class="w-8 h-8 rounded">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium">{{ $gk->web_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $gk->team_short }} • £{{ $gk->price }}m</div>
                                        </div>
                                        <div class="flex space-x-1">
                                            <button class="captain-btn w-6 h-6 border border-yellow-400 rounded text-xs" data-player-id="{{ $gk->fpl_id }}">C</button>
                                            <button class="vice-captain-btn w-6 h-6 border border-gray-400 rounded text-xs" data-player-id="{{ $gk->fpl_id }}">V</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Defenders -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Defenders</h4>
                            <div class="space-y-2" id="def-list">
                                @foreach(($squad['defenders'] ?? []) as $def)
                                    <div class="player-card flex items-center space-x-3 p-2 border border-gray-200 rounded-lg"
                                         draggable="true"
                                         data-player-id="{{ $def->fpl_id }}"
                                         data-position="Defender">
                                        <img src="{{ $def->jersey_url }}" alt="Jersey" class="w-8 h-8 rounded">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium">{{ $def->web_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $def->team_short }} • £{{ $def->price }}m</div>
                                        </div>
                                        <div class="flex space-x-1">
                                            <button class="captain-btn w-6 h-6 border border-yellow-400 rounded text-xs" data-player-id="{{ $def->fpl_id }}">C</button>
                                            <button class="vice-captain-btn w-6 h-6 border border-gray-400 rounded text-xs" data-player-id="{{ $def->fpl_id }}">V</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Midfielders -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Midfielders</h4>
                            <div class="space-y-2" id="mid-list">
                                @foreach(($squad['midfielders'] ?? []) as $mid)
                                    <div class="player-card flex items-center space-x-3 p-2 border border-gray-200 rounded-lg"
                                         draggable="true"
                                         data-player-id="{{ $mid->fpl_id }}"
                                         data-position="Midfielder">
                                        <img src="{{ $mid->jersey_url }}" alt="Jersey" class="w-8 h-8 rounded">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium">{{ $mid->web_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $mid->team_short }} • £{{ $mid->price }}m</div>
                                        </div>
                                        <div class="flex space-x-1">
                                            <button class="captain-btn w-6 h-6 border border-yellow-400 rounded text-xs" data-player-id="{{ $mid->fpl_id }}">C</button>
                                            <button class="vice-captain-btn w-6 h-6 border border-gray-400 rounded text-xs" data-player-id="{{ $mid->fpl_id }}">V</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Forwards -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Forwards</h4>
                            <div class="space-y-2" id="fwd-list">
                                @foreach(($squad['forwards'] ?? []) as $fwd)
                                    <div class="player-card flex items-center space-x-3 p-2 border border-gray-200 rounded-lg"
                                         draggable="true"
                                         data-player-id="{{ $fwd->fpl_id }}"
                                         data-position="Forward">
                                        <img src="{{ $fwd->jersey_url }}" alt="Jersey" class="w-8 h-8 rounded">
                                        <div class="flex-1">
                                            <div class="text-sm font-medium">{{ $fwd->web_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $fwd->team_short }} • £{{ $fwd->price }}m</div>
                                        </div>
                                        <div class="flex space-x-1">
                                            <button class="captain-btn w-6 h-6 border border-yellow-400 rounded text-xs" data-player-id="{{ $fwd->fpl_id }}">C</button>
                                            <button class="vice-captain-btn w-6 h-6 border border-gray-400 rounded text-xs" data-player-id="{{ $fwd->fpl_id }}">V</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <button id="save-team" class="w-full py-3 bg-fpl-purple text-white rounded-lg hover:bg-purple-900 transition-colors font-semibold">
                        Save Team Selection
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Drag & Drop and Team Management -->
    <script>
        let selectedChip = null;
        let currentCaptain = null;
        let currentViceCaptain = null;
        let startingXI = [];

        // Team data from database
        const teamData = @json($teamData);

        // Initialize team from database
        function initializeTeamFromDatabase() {
            if (teamData.starting_xi && teamData.starting_xi.length > 0) {
                startingXI = [...teamData.starting_xi];

                // Set captain and vice-captain
                currentCaptain = teamData.captain_id;
                currentViceCaptain = teamData.vice_captain_id;

                // Set active chip
                selectedChip = teamData.active_chip;

                // Set formation
                document.getElementById('formation-select').value = teamData.formation;

                // Update captain/vice-captain display
                if (currentCaptain) {
                    document.getElementById('captain-name').textContent = `Player ${currentCaptain}`;
                    const captainBtn = document.querySelector(`[data-player-id="${currentCaptain}"].captain-btn`);
                    if (captainBtn) {
                        captainBtn.classList.add('bg-yellow-400', 'text-white');
                    }
                }

                if (currentViceCaptain) {
                    document.getElementById('vice-captain-name').textContent = `Player ${currentViceCaptain}`;
                    const viceBtn = document.querySelector(`[data-player-id="${currentViceCaptain}"].vice-captain-btn`);
                    if (viceBtn) {
                        viceBtn.classList.add('bg-gray-400', 'text-white');
                    }
                }

                // Set chip selection
                if (selectedChip) {
                    const chipBtn = document.querySelector(`[data-chip="${selectedChip}"]`);
                    if (chipBtn) {
                        chipBtn.classList.add('border-fpl-green', 'bg-fpl-green/10');
                    }
                }

                // Place players on pitch
                teamData.starting_xi.forEach(playerId => {
                    const playerCard = document.querySelector(`[data-player-id="${playerId}"].player-card`);
                    if (playerCard) {
                        const position = playerCard.dataset.position;
                        const playerName = playerCard.querySelector('.text-sm.font-medium').textContent;
                        const jerseyUrl = playerCard.querySelector('img').src;

                        const availableSlot = findAvailableSlot(position);
                        if (availableSlot) {
                            const playerData = {
                                playerId: playerId,
                                position: position,
                                name: playerName,
                                jerseyUrl: jerseyUrl
                            };
                            addPlayerToPitch(availableSlot, playerData);
                            playerCard.classList.add('bg-green-100', 'border-green-500');
                        }
                    }
                });
            }
        }

        // Drag and Drop Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const playerCards = document.querySelectorAll('.player-card');
            const pitchSlots = document.querySelectorAll('.pitch-slot');

            // Initialize team from database
            initializeTeamFromDatabase();

            // Add drag listeners to player cards
            playerCards.forEach(card => {
                card.addEventListener('dragstart', function(e) {
                    const playerName = this.querySelector('.text-sm.font-medium').textContent;
                    const jerseyUrl = this.querySelector('img').src;
                    e.dataTransfer.setData('text/plain', JSON.stringify({
                        playerId: this.dataset.playerId,
                        position: this.dataset.position,
                        name: playerName,
                        jerseyUrl: jerseyUrl,
                        html: this.outerHTML
                    }));
                });
            });

            // Add drop listeners to pitch slots
            pitchSlots.forEach(slot => {
                slot.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('droppable');
                });

                slot.addEventListener('dragleave', function(e) {
                    this.classList.remove('droppable');
                });

                slot.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('droppable');

                    const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                    const slotPosition = this.dataset.position;

                    // Check position compatibility
                    if (isPositionCompatible(data.position, slotPosition)) {
                        // Check if player is already on the pitch (only if dropping to a different slot)
                        if (startingXI.includes(data.playerId)) {
                            // Find the current slot of this player
                            const currentSlot = document.querySelector(`[data-player-id="${data.playerId}"].player-pitch-card`)?.closest('.pitch-slot');
                            if (currentSlot && currentSlot !== this) {
                                // Remove from current slot first
                                removePlayerFromPitch(currentSlot, data.playerId);
                            } else if (currentSlot === this) {
                                // Same slot, do nothing
                                return;
                            }
                        }

                        addPlayerToPitch(this, data);

                        // Add visual feedback that player is selected
                        const playerCard = document.querySelector(`[data-player-id="${data.playerId}"].player-card`);
                        if (playerCard) {
                            playerCard.classList.add('bg-green-100', 'border-green-500');
                        }
                    }
                });
            });

            // Player card click handling
            document.addEventListener('click', function(e) {
                // Handle captain/vice-captain button clicks
                if (e.target.classList.contains('captain-btn')) {
                    selectCaptain(e.target.dataset.playerId);
                    return;
                }
                if (e.target.classList.contains('vice-captain-btn')) {
                    selectViceCaptain(e.target.dataset.playerId);
                    return;
                }

                // Handle player card clicks
                const playerCard = e.target.closest('.player-card');
                if (playerCard && !e.target.classList.contains('captain-btn') && !e.target.classList.contains('vice-captain-btn')) {
                    const playerId = playerCard.dataset.playerId;
                    const position = playerCard.dataset.position;
                    const playerName = playerCard.querySelector('.text-sm.font-medium').textContent;
                    const jerseyUrl = playerCard.querySelector('img').src;

                    // Check if player is already on the pitch
                    if (startingXI.includes(playerId)) {
                        alert('This player is already in your starting XI');
                        return;
                    }

                    // Find an available slot for this position
                    const availableSlot = findAvailableSlot(position);
                    if (availableSlot) {
                        const playerData = {
                            playerId: playerId,
                            position: position,
                            name: playerName,
                            jerseyUrl: jerseyUrl
                        };
                        addPlayerToPitch(availableSlot, playerData);

                        // Add visual feedback that player is selected
                        playerCard.classList.add('bg-green-100', 'border-green-500');
                    } else {
                        // No available slots, but offer to substitute
                        const occupiedSlots = document.querySelectorAll(`[data-position="${findPositionCode(position)}"].occupied`);
                        if (occupiedSlots.length > 0) {
                            if (confirm(`All ${position} slots are filled. Would you like to substitute a player?`)) {
                                const playerData = {
                                    playerId: playerId,
                                    position: position,
                                    name: playerName,
                                    jerseyUrl: jerseyUrl
                                };
                                // Use the first occupied slot for substitution
                                addPlayerToPitch(occupiedSlots[0], playerData);

                                // Add visual feedback that player is selected
                                playerCard.classList.add('bg-green-100', 'border-green-500');
                            }
                        } else {
                            alert(`No available ${position} slots on the pitch`);
                        }
                    }
                }
            });

            // Chip selection
            document.querySelectorAll('.chip-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    selectChip(this.dataset.chip);
                });
            });

            // Save team
            document.getElementById('save-team').addEventListener('click', saveTeamSelection);
        });

        function findPositionCode(position) {
            const posMap = {
                'Goalkeeper': 'GK',
                'Defender': 'DEF',
                'Midfielder': 'MID',
                'Forward': 'FWD'
            };
            return posMap[position];
        }

        function findAvailableSlot(position) {
            const posMap = {
                'Goalkeeper': 'GK',
                'Defender': 'DEF',
                'Midfielder': 'MID',
                'Forward': 'FWD'
            };

            const slotPosition = posMap[position];
            const slots = document.querySelectorAll(`[data-position="${slotPosition}"]`);

            for (let slot of slots) {
                if (!slot.classList.contains('occupied')) {
                    return slot;
                }
            }
            return null;
        }

        function isPositionCompatible(playerPos, slotPos) {
            const posMap = {
                'Goalkeeper': 'GK',
                'Defender': 'DEF',
                'Midfielder': 'MID',
                'Forward': 'FWD'
            };
            return posMap[playerPos] === slotPos;
        }

        function addPlayerToPitch(slot, playerData) {
            // Check if slot is already occupied and remove the previous player
            if (slot.classList.contains('occupied')) {
                const existingPlayer = slot.querySelector('.player-pitch-card');
                if (existingPlayer) {
                    const existingPlayerId = existingPlayer.dataset.playerId;

                    // Remove from starting XI
                    const index = startingXI.indexOf(existingPlayerId);
                    if (index > -1) {
                        startingXI.splice(index, 1);
                    }

                    // Remove visual feedback from previous player card
                    const previousPlayerCard = document.querySelector(`[data-player-id="${existingPlayerId}"].player-card`);
                    if (previousPlayerCard) {
                        previousPlayerCard.classList.remove('bg-green-100', 'border-green-500');
                    }

                    // Remove captain/vice-captain if this player was selected
                    if (currentCaptain === existingPlayerId) {
                        currentCaptain = null;
                        document.getElementById('captain-name').textContent = 'None';
                        document.querySelectorAll('.captain-btn').forEach(btn => {
                            btn.classList.remove('bg-yellow-400', 'text-white');
                        });
                    }

                    if (currentViceCaptain === existingPlayerId) {
                        currentViceCaptain = null;
                        document.getElementById('vice-captain-name').textContent = 'None';
                        document.querySelectorAll('.vice-captain-btn').forEach(btn => {
                            btn.classList.remove('bg-gray-400', 'text-white');
                        });
                    }
                }
            }

            // Create player card for pitch
            const pitchPlayer = document.createElement('div');
            pitchPlayer.className = 'player-pitch-card cursor-pointer';
            pitchPlayer.dataset.playerId = playerData.playerId;
            pitchPlayer.innerHTML = `
                <div class="w-16 h-16 bg-white rounded-lg shadow-lg flex items-center justify-center mb-2">
                    <img src="${playerData.jerseyUrl}" alt="Jersey" class="w-12 h-12 rounded">
                </div>
                <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                    <div class="text-xs font-semibold text-gray-900">${playerData.name || playerData.playerId}</div>
                </div>
            `;

            // Add click to remove functionality
            pitchPlayer.addEventListener('click', function() {
                removePlayerFromPitch(slot, playerData.playerId);
            });

            slot.innerHTML = '';
            slot.appendChild(pitchPlayer);
            slot.classList.add('occupied');

            // Add to starting XI
            if (!startingXI.includes(playerData.playerId)) {
                startingXI.push(playerData.playerId);
            }
        }

        function removePlayerFromPitch(slot, playerId) {
            slot.innerHTML = '';
            slot.classList.remove('occupied');

            // Remove from starting XI
            const index = startingXI.indexOf(playerId);
            if (index > -1) {
                startingXI.splice(index, 1);
            }

            // Remove visual feedback from player card
            const playerCard = document.querySelector(`[data-player-id="${playerId}"].player-card`);
            if (playerCard) {
                playerCard.classList.remove('bg-green-100', 'border-green-500');
            }

            // Remove captain/vice-captain if this player was selected
            if (currentCaptain === playerId) {
                currentCaptain = null;
                document.getElementById('captain-name').textContent = 'None';
                document.querySelectorAll('.captain-btn').forEach(btn => {
                    btn.classList.remove('bg-yellow-400', 'text-white');
                });
            }

            if (currentViceCaptain === playerId) {
                currentViceCaptain = null;
                document.getElementById('vice-captain-name').textContent = 'None';
                document.querySelectorAll('.vice-captain-btn').forEach(btn => {
                    btn.classList.remove('bg-gray-400', 'text-white');
                });
            }
        }

        function selectCaptain(playerId) {
            // Remove previous captain styling
            document.querySelectorAll('.captain-btn').forEach(btn => {
                btn.classList.remove('bg-yellow-400', 'text-white');
            });

            // Add captain styling
            const captainBtn = document.querySelector(`[data-player-id="${playerId}"].captain-btn`);
            if (captainBtn) {
                captainBtn.classList.add('bg-yellow-400', 'text-white');
                currentCaptain = playerId;
                document.getElementById('captain-name').textContent = `Player ${playerId}`;
            }
        }

        function selectViceCaptain(playerId) {
            // Remove previous vice-captain styling
            document.querySelectorAll('.vice-captain-btn').forEach(btn => {
                btn.classList.remove('bg-gray-400', 'text-white');
            });

            // Add vice-captain styling
            const viceBtn = document.querySelector(`[data-player-id="${playerId}"].vice-captain-btn`);
            if (viceBtn) {
                viceBtn.classList.add('bg-gray-400', 'text-white');
                currentViceCaptain = playerId;
                document.getElementById('vice-captain-name').textContent = `Player ${playerId}`;
            }
        }

        function selectChip(chipType) {
            // Remove previous chip selection
            document.querySelectorAll('.chip-btn').forEach(btn => {
                btn.classList.remove('border-fpl-green', 'bg-fpl-green/10');
            });

            // Add chip selection
            const chipBtn = document.querySelector(`[data-chip="${chipType}"]`);
            if (chipBtn) {
                chipBtn.classList.add('border-fpl-green', 'bg-fpl-green/10');
                selectedChip = chipType;
            }
        }

        function saveTeamSelection() {
            if (startingXI.length !== 11) {
                alert('Please select 11 players for your starting XI');
                return;
            }

            if (!currentCaptain) {
                alert('Please select a captain');
                return;
            }

            if (!currentViceCaptain) {
                alert('Please select a vice-captain');
                return;
            }

            const data = {
                starting_xi: startingXI,
                captain: currentCaptain,
                vice_captain: currentViceCaptain,
                formation: document.getElementById('formation-select').value,
                chip: selectedChip,
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            };

            fetch('{{ route("pick.team.save") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Team selection saved successfully!');
                } else {
                    alert('Error saving team selection');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving team selection');
            });
        }
    </script>
</body>
</html>
