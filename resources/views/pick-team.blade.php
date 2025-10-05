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
            background: #37003c;
            min-height: 100vh;
        }

        .pitch-player {
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .pitch-player:hover {
            transform: scale(1.08);
        }

        .captain-badge, .vice-captain-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-center;
            font-weight: bold;
            font-size: 11px;
            color: white;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            z-index: 10;
        }

        .captain-badge {
            background: #00ff87;
        }

        .vice-captain-badge {
            background: #e1e8ed;
        }

        .bench-player {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .bench-player:hover {
            background-color: #f3f4f6;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .player-menu {
            display: none;
            position: fixed;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
            z-index: 1000;
            min-width: 220px;
            overflow: hidden;
        }

        .player-menu.active {
            display: block;
        }

        .player-menu button {
            display: block;
            width: 100%;
            padding: 12px 16px;
            text-align: left;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.15s;
            border-bottom: 1px solid #f3f4f6;
        }

        .player-menu button:hover {
            background: #f9fafb;
        }

        .player-menu button:last-child {
            border-bottom: none;
        }

        .substitute-list {
            max-height: 300px;
            overflow-y: auto;
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
                        <p class="text-gray-600">Select your starting XI and captain for {{ $nextGameweek ? 'Gameweek ' . $nextGameweek->gameweek_id : 'the next gameweek' }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-900">Deadline</div>
                        @if($nextGameweek)
                            <div class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($nextGameweek->deadline_time)->format('D j M, H:i') }}</div>
                        @else
                            <div class="text-sm text-gray-600">TBD</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Team Management Interface -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Pitch and Bench (3 columns) -->
                <div class="lg:col-span-3">
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 mb-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900 text-lg">Your Team</h3>
                            <div class="text-sm font-medium text-gray-600">
                                Formation: <span id="formation-display" class="text-fpl-purple font-bold">4-4-2</span>
                            </div>
                        </div>

                        <!-- Football Pitch -->
                        <div class="relative w-full h-[700px] bg-gradient-to-b from-green-400 to-green-500 rounded-lg overflow-hidden">
                            <!-- Pitch Lines -->
                            <div class="absolute inset-0 pointer-events-none">
                                <div class="absolute inset-4 border-2 border-white/60 rounded"></div>
                                <div class="absolute top-4 left-1/2 transform -translate-x-1/2 w-32 h-16 border-2 border-white/60"></div>
                                <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-32 h-16 border-2 border-white/60"></div>
                                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-24 h-24 border-2 border-white/60 rounded-full"></div>
                                <div class="absolute top-1/2 left-4 right-4 h-0.5 bg-white/60"></div>
                            </div>

                            <!-- Starting XI Container -->
                            <div id="starting-xi-container" class="relative w-full h-full pt-8 px-8">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>

                        <!-- Bench Section -->
                        <div class="mt-6 bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg p-5 border-2 border-gray-300">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-gray-900 font-bold text-base">Substitutes</h4>
                                <span class="text-gray-600 text-sm font-medium">Click to substitute</span>
                            </div>
                            <div class="grid grid-cols-4 gap-4" id="bench-container">
                                <!-- Will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Chips Section -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Chips</h3>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <button class="chip-btn p-4 border-2 border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors" data-chip="wildcard">
                                <div class="text-3xl mb-2">🃏</div>
                                <div class="text-sm font-medium">Wildcard</div>
                            </button>
                            <button class="chip-btn p-4 border-2 border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors" data-chip="freehit">
                                <div class="text-3xl mb-2">🎯</div>
                                <div class="text-sm font-medium">Free Hit</div>
                            </button>
                            <button class="chip-btn p-4 border-2 border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors" data-chip="bench-boost">
                                <div class="text-3xl mb-2">⚡</div>
                                <div class="text-sm font-medium">Bench Boost</div>
                            </button>
                            <button class="chip-btn p-4 border-2 border-gray-300 rounded-lg text-center hover:border-fpl-green transition-colors" data-chip="triple-captain">
                                <div class="text-3xl mb-2">👑</div>
                                <div class="text-sm font-medium">Triple Captain</div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Team Info Sidebar (1 column) -->
                <div class="space-y-6">
                    <!-- Team Status -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-5">
                        <h3 class="font-semibold text-gray-900 mb-4 text-lg">Team Status</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                <span class="text-gray-600">Starting XI</span>
                                <span id="starting-count" class="font-bold text-lg">0/11</span>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                                <span class="text-gray-600">Captain</span>
                                <span id="captain-display" class="font-semibold text-yellow-600">None</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Vice-Captain</span>
                                <span id="vice-captain-display" class="font-semibold text-gray-600">None</span>
                            </div>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <button id="save-team" class="w-full py-4 bg-gray-400 text-white rounded-lg cursor-not-allowed font-bold text-lg shadow-lg" disabled>
                        Save Team
                    </button>

                    <!-- Instructions -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-2 text-sm">How to use:</h4>
                        <ul class="text-xs text-blue-800 space-y-1">
                            <li>• Click starting XI players for captain menu</li>
                            <li>• Click bench players to substitute</li>
                            <li>• Change formation to rearrange team</li>
                            <li>• Select 11 starting players to save</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Player Menu (Captain/Vice-Captain) -->
    <div id="player-menu" class="player-menu">
        <!-- Will be populated by JavaScript -->
    </div>

    <!-- Substitute Menu (for bench players) -->
    <div id="substitute-menu" class="player-menu">
        <!-- Will be populated by JavaScript -->
    </div>

    <!-- Hidden formation select for state management -->
    <select id="formation-select" style="display:none;">
        <option value="4-4-2">4-4-2</option>
        <option value="3-5-2">3-5-2</option>
        <option value="4-5-1">4-5-1</option>
        <option value="3-4-3">3-4-3</option>
        <option value="4-3-3">4-3-3</option>
    </select>

    <script>
        // ================== STATE MANAGEMENT ==================
        const squad = @json($squad);
        const teamData = @json($teamData);

        let allPlayers = [];
        let startingXI = [];
        let bench = [];
        let captain = null;
        let viceCaptain = null;
        let selectedChip = null;
        let initialState = null;

        // Formation configurations
        const formations = {
            '4-4-2': { def: 4, mid: 4, fwd: 2 },
            '3-5-2': { def: 3, mid: 5, fwd: 2 },
            '4-5-1': { def: 4, mid: 5, fwd: 1 },
            '3-4-3': { def: 3, mid: 4, fwd: 3 },
            '4-3-3': { def: 4, mid: 3, fwd: 3 }
        };

        // ================== INITIALIZATION ==================
        function init() {
            // Flatten squad into single array
            allPlayers = [
                ...squad.goalkeepers,
                ...squad.defenders,
                ...squad.midfielders,
                ...squad.forwards
            ];

            console.log('Total players:', allPlayers.length);
            console.log('TeamData starting_xi:', teamData.starting_xi);
            console.log('TeamData bench:', teamData.bench);

            // Load saved team or auto-select
            if (teamData.starting_xi && teamData.starting_xi.length > 0) {
                // Map saved IDs to player objects
                const savedPlayers = teamData.starting_xi.map(id => allPlayers.find(p => p.fpl_id === id)).filter(Boolean);

                // Validate team composition (must have exactly 1 GK and 10 outfield players)
                const gks = savedPlayers.filter(p => p.position === 'Goalkeeper');
                const outfield = savedPlayers.filter(p => p.position !== 'Goalkeeper');

                // If invalid composition, auto-select proper team
                if (gks.length !== 1 || outfield.length !== 10 || savedPlayers.length !== 11) {
                    console.warn('Invalid team composition, auto-selecting team');
                    autoSelectTeam();
                } else {
                    // Valid team composition
                    startingXI = savedPlayers;

                    // Load bench from saved bench IDs if available
                    if (teamData.bench && teamData.bench.length > 0) {
                        bench = teamData.bench.map(id => allPlayers.find(p => p.fpl_id === id)).filter(Boolean);
                        console.log('Loaded bench from saved data:', bench.length, 'players');
                    } else {
                        // Fallback: calculate bench as remaining players (but only take 4 max)
                        const startingXIIds = startingXI.map(p => p.fpl_id);
                        const remainingPlayers = allPlayers.filter(p => !startingXIIds.includes(p.fpl_id));

                        // Take exactly 4 bench players: 1 GK + 3 outfield
                        const benchGK = remainingPlayers.find(p => p.position === 'Goalkeeper');
                        const benchOutfield = remainingPlayers.filter(p => p.position !== 'Goalkeeper').slice(0, 3);

                        bench = benchGK ? [benchGK, ...benchOutfield] : benchOutfield.slice(0, 4);
                        console.log('Calculated bench from remaining players:', bench.length, 'players');
                    }

                    captain = teamData.captain_id;
                    viceCaptain = teamData.vice_captain_id;
                    selectedChip = teamData.active_chip;
                    document.getElementById('formation-select').value = teamData.formation || '4-4-2';
                }
            } else {
                autoSelectTeam();
            }

            console.log('Starting XI:', startingXI.length, startingXI.map(p => p.position));
            console.log('Bench:', bench.length);

            // Set initial state
            initialState = getCurrentState();

            // Render
            render();
            attachEventListeners();
        }

        function autoSelectTeam() {
            // Default to 4-4-2 if no formation set
            let formation = document.getElementById('formation-select').value || '4-4-2';
            let config = formations[formation];

            // Auto-select: 1 GK, then try to fill according to formation
            // If not enough players, adjust formation
            const availableGKs = squad.goalkeepers.length;
            const availableDefs = squad.defenders.length;
            const availableMids = squad.midfielders.length;
            const availableFwds = squad.forwards.length;

            // Try to build team with available players
            if (availableDefs < config.def || availableMids < config.mid || availableFwds < config.fwd) {
                // Not enough players for this formation, try 4-4-2
                formation = '4-4-2';
                config = formations[formation];
            }

            // Build starting XI
            startingXI = [
                squad.goalkeepers[0], // First GK only
                ...squad.defenders.slice(0, config.def),
                ...squad.midfielders.slice(0, config.mid),
                ...squad.forwards.slice(0, config.fwd)
            ].filter(Boolean);

            // Ensure we have exactly 11 players
            if (startingXI.length !== 11) {
                console.warn('Auto-select failed to get 11 players, adjusting...');
                // Fill remaining slots from available players
                const remaining = allPlayers.filter(p => !startingXI.includes(p) && p.position !== 'Goalkeeper');
                while (startingXI.length < 11 && remaining.length > 0) {
                    startingXI.push(remaining.shift());
                }
            }

            // Get starting XI player IDs
            const startingXIIds = startingXI.map(p => p.fpl_id);
            bench = allPlayers.filter(p => !startingXIIds.includes(p.fpl_id));

            // Update formation select
            document.getElementById('formation-select').value = formation;
        }

        function getCurrentState() {
            return JSON.stringify({
                starting_xi: startingXI.map(p => p.fpl_id).sort(),
                captain: captain,
                vice_captain: viceCaptain,
                formation: getCurrentFormation(),
                chip: selectedChip
            });
        }

        // Auto-detect formation based on current starting XI
        function getCurrentFormation() {
            const gkCount = startingXI.filter(p => p.position === 'Goalkeeper').length;
            const defCount = startingXI.filter(p => p.position === 'Defender').length;
            const midCount = startingXI.filter(p => p.position === 'Midfielder').length;
            const fwdCount = startingXI.filter(p => p.position === 'Forward').length;

            // Map to formation string
            if (gkCount === 1 && defCount + midCount + fwdCount === 10) {
                const formationKey = `${defCount}-${midCount}-${fwdCount}`;
                if (formations[formationKey]) {
                    return formationKey;
                }
            }

            // Default fallback
            return '4-4-2';
        }

        function updateFormationDisplay() {
            const formation = getCurrentFormation();
            document.getElementById('formation-select').value = formation;
            document.getElementById('formation-display').textContent = formation;
        }

        // ================== RENDERING ==================
        function render() {
            updateFormationDisplay();
            renderStartingXI();
            renderBench();
            updateUI();
        }

        function renderStartingXI() {
            const container = document.getElementById('starting-xi-container');
            const formation = getCurrentFormation();
            const config = formations[formation];

            // Get players by position
            const gk = startingXI.filter(p => p.position === 'Goalkeeper')[0];
            const defs = startingXI.filter(p => p.position === 'Defender').slice(0, config.def);
            const mids = startingXI.filter(p => p.position === 'Midfielder').slice(0, config.mid);
            const fwds = startingXI.filter(p => p.position === 'Forward').slice(0, config.fwd);

            container.innerHTML = `
                <!-- Goalkeeper Row -->
                <div class="absolute top-[50px] left-0 right-0 flex justify-center">
                    ${gk ? renderPitchPlayer(gk) : '<div class="text-white/50 text-sm">No GK</div>'}
                </div>

                <!-- Defenders Row -->
                <div class="absolute top-[180px] left-0 right-0 flex justify-center gap-6">
                    ${defs.map(p => renderPitchPlayer(p)).join('') || '<div class="text-white/50 text-sm">No Defenders</div>'}
                </div>

                <!-- Midfielders Row -->
                <div class="absolute top-[350px] left-0 right-0 flex justify-center gap-6">
                    ${mids.map(p => renderPitchPlayer(p)).join('') || '<div class="text-white/50 text-sm">No Midfielders</div>'}
                </div>

                <!-- Forwards Row -->
                <div class="absolute top-[520px] left-0 right-0 flex justify-center gap-6">
                    ${fwds.map(p => renderPitchPlayer(p)).join('') || '<div class="text-white/50 text-sm">No Forwards</div>'}
                </div>
            `;

            // Attach click handlers
            container.querySelectorAll('.pitch-player').forEach(el => {
                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showPlayerMenu(parseInt(el.dataset.playerId), e.clientX, e.clientY);
                });
            });
        }

        function renderPitchPlayer(player) {
            const isCaptain = captain === player.fpl_id;
            const isViceCaptain = viceCaptain === player.fpl_id;

            return `
                <div class="pitch-player" data-player-id="${player.fpl_id}">
                    ${isCaptain ? '<div class="captain-badge">C</div>' : ''}
                    ${isViceCaptain ? '<div class="vice-captain-badge">V</div>' : ''}
                    <div class="w-16 h-16 bg-white rounded-lg shadow-xl flex items-center justify-center mb-2 border-2 border-white">
                        <img src="${player.jersey_url}" alt="${player.web_name}" class="w-12 h-12 rounded object-contain">
                    </div>
                    <div class="bg-white rounded px-2 py-1 text-center shadow-lg">
                        <div class="text-xs font-bold text-gray-900 truncate max-w-[70px]">${player.web_name}</div>
                    </div>
                </div>
            `;
        }

        function renderBench() {
            const container = document.getElementById('bench-container');
            const benchPlayers = bench.slice(0, 4); // Show only 4 bench spots

            console.log('Rendering bench with', benchPlayers.length, 'players');

            container.innerHTML = benchPlayers.map((player, index) => `
                <div class="bench-player bg-white rounded-lg p-3 border-2 border-gray-300 hover:border-fpl-green transition-all" data-player-id="${player.fpl_id}">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="bg-gray-200 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">${index + 1}</span>
                        <img src="${player.jersey_url}" alt="${player.web_name}" class="w-10 h-10 rounded">
                    </div>
                    <div class="text-xs font-semibold text-gray-900 truncate">${player.web_name}</div>
                    <div class="text-xs text-gray-500">${player.position.slice(0, 3).toUpperCase()}</div>
                </div>
            `).join('');

            // Attach click handlers
            container.querySelectorAll('.bench-player').forEach(el => {
                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showSubstituteMenu(parseInt(el.dataset.playerId), e.clientX, e.clientY);
                });
            });
        }

        function updateUI() {
            // Update counts
            document.getElementById('starting-count').textContent = `${startingXI.length}/11`;

            // Update captain display
            const captainPlayer = allPlayers.find(p => p.fpl_id === captain);
            document.getElementById('captain-display').textContent = captainPlayer ? captainPlayer.web_name : 'None';

            // Update vice-captain display
            const viceCaptainPlayer = allPlayers.find(p => p.fpl_id === viceCaptain);
            document.getElementById('vice-captain-display').textContent = viceCaptainPlayer ? viceCaptainPlayer.web_name : 'None';

            // Update save button
            updateSaveButton();

            // Update chip selection
            document.querySelectorAll('.chip-btn').forEach(btn => {
                btn.classList.toggle('border-fpl-green', btn.dataset.chip === selectedChip);
                btn.classList.toggle('bg-fpl-green/10', btn.dataset.chip === selectedChip);
            });
        }

        function updateSaveButton() {
            const saveBtn = document.getElementById('save-team');
            const hasChanges = getCurrentState() !== initialState;
            const isValid = startingXI.length === 11;

            if (hasChanges && isValid) {
                saveBtn.disabled = false;
                saveBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                saveBtn.classList.add('bg-fpl-purple', 'hover:bg-purple-900', 'cursor-pointer');
            } else {
                saveBtn.disabled = true;
                saveBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                saveBtn.classList.remove('bg-fpl-purple', 'hover:bg-purple-900', 'cursor-pointer');
            }
        }

        // ================== MENUS ==================
        function showPlayerMenu(playerId, x, y) {
            const menu = document.getElementById('player-menu');
            const player = allPlayers.find(p => p.fpl_id === playerId);

            menu.innerHTML = `
                <button onclick="setCaptain(${playerId})">
                    <span class="inline-block w-6 h-6 bg-yellow-400 rounded-full text-white font-bold text-xs mr-2" style="line-height:24px;">C</span>
                    Set as Captain
                </button>
                <button onclick="setViceCaptain(${playerId})">
                    <span class="inline-block w-6 h-6 bg-gray-400 rounded-full text-white font-bold text-xs mr-2" style="line-height:24px;">V</span>
                    Set as Vice-Captain
                </button>
            `;

            menu.classList.add('active');
            menu.style.left = `${Math.min(x, window.innerWidth - 250)}px`;
            menu.style.top = `${Math.min(y, window.innerHeight - 150)}px`;
        }

        function showSubstituteMenu(playerId, x, y) {
            const menu = document.getElementById('substitute-menu');
            const benchPlayer = allPlayers.find(p => p.fpl_id === playerId);

            // Find valid substitutes from starting XI
            const validSubstitutes = startingXI.filter(p => {
                // Same position or flexible substitution
                return p.position === benchPlayer.position ||
                       (benchPlayer.position !== 'Goalkeeper' && p.position !== 'Goalkeeper');
            });

            menu.innerHTML = `
                <div class="p-3 border-b bg-gray-50 font-semibold text-sm">
                    Substitute ${benchPlayer.web_name}
                </div>
                <div class="substitute-list">
                    ${validSubstitutes.map(p => `
                        <button onclick="makeSubstitution(${playerId}, ${p.fpl_id})" class="flex items-center gap-3 w-full">
                            <img src="${p.jersey_url}" alt="${p.web_name}" class="w-10 h-10 rounded">
                            <div class="text-left flex-1">
                                <div class="font-semibold text-sm">${p.web_name}</div>
                                <div class="text-xs text-gray-500">${p.position}</div>
                            </div>
                        </button>
                    `).join('') || '<div class="p-4 text-sm text-gray-500">No valid substitutes</div>'}
                </div>
            `;

            menu.classList.add('active');
            menu.style.left = `${Math.min(x, window.innerWidth - 250)}px`;
            menu.style.top = `${Math.min(y, window.innerHeight - 400)}px`;
        }

        function hideMenus() {
            document.getElementById('player-menu').classList.remove('active');
            document.getElementById('substitute-menu').classList.remove('active');
        }

        // ================== ACTIONS ==================
        function setCaptain(playerId) {
            captain = playerId;
            hideMenus();
            render();
        }

        function setViceCaptain(playerId) {
            viceCaptain = playerId;
            hideMenus();
            render();
        }

        function makeSubstitution(benchPlayerId, startingPlayerId) {
            const benchPlayer = allPlayers.find(p => p.fpl_id === benchPlayerId);
            const startingPlayer = allPlayers.find(p => p.fpl_id === startingPlayerId);

            // Validate substitution
            if (!benchPlayer || !startingPlayer) {
                console.error('Invalid substitution: player not found');
                return;
            }

            // Prevent having 2 GKs in starting XI
            if (benchPlayer.position === 'Goalkeeper' && startingPlayer.position !== 'Goalkeeper') {
                const gksInStarting = startingXI.filter(p => p.position === 'Goalkeeper').length;
                if (gksInStarting >= 1) {
                    alert('You can only have 1 goalkeeper in your starting XI');
                    hideMenus();
                    return;
                }
            }

            // Swap players
            const startingIndex = startingXI.findIndex(p => p.fpl_id === startingPlayerId);
            const benchIndex = bench.findIndex(p => p.fpl_id === benchPlayerId);

            if (startingIndex === -1 || benchIndex === -1) {
                console.error('Invalid substitution: player index not found');
                return;
            }

            startingXI[startingIndex] = benchPlayer;
            bench[benchIndex] = startingPlayer;

            hideMenus();
            render();
        }

        function saveTeam() {
            if (startingXI.length !== 11) {
                alert(`Please select exactly 11 players. You have ${startingXI.length} selected.`);
                return;
            }

            if (!captain) {
                alert('Please select a captain');
                return;
            }

            if (!viceCaptain) {
                alert('Please select a vice-captain');
                return;
            }

            const data = {
                starting_xi: startingXI.map(p => p.fpl_id),
                bench: bench.map(p => p.fpl_id),
                captain: captain,
                vice_captain: viceCaptain,
                formation: getCurrentFormation(),
                chip: selectedChip,
                _token: document.querySelector('meta[name="csrf-token"]').content
            };

            // Validate before sending
            if (data.starting_xi.length !== 11) {
                alert(`Invalid starting XI: ${data.starting_xi.length} players (should be 11)`);
                return;
            }

            if (data.bench.length > 4) {
                alert(`Invalid bench: ${data.bench.length} players (should be max 4)`);
                return;
            }

            console.log('Saving team:', data);
            console.log('Starting XI count:', data.starting_xi.length);
            console.log('Bench count:', data.bench.length);
            console.log('Total players:', data.starting_xi.length + data.bench.length);

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
                    alert('Team saved successfully!');
                    initialState = getCurrentState();
                    updateSaveButton();
                } else {
                    alert('Error saving team: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving team');
            });
        }

        // ================== EVENT LISTENERS ==================
        function attachEventListeners() {
            // Save button
            document.getElementById('save-team').addEventListener('click', saveTeam);

            // Chip buttons
            document.querySelectorAll('.chip-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    selectedChip = selectedChip === btn.dataset.chip ? null : btn.dataset.chip;
                    updateUI();
                });
            });

            // Click outside to close menus
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.player-menu') && !e.target.closest('.pitch-player') && !e.target.closest('.bench-player')) {
                    hideMenus();
                }
            });

            // Prevent menu close when clicking inside
            document.getElementById('player-menu').addEventListener('click', (e) => e.stopPropagation());
            document.getElementById('substitute-menu').addEventListener('click', (e) => e.stopPropagation());
        }

        // ================== START ==================
        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>
