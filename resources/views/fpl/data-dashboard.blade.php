<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FPL Data Management Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">
                            <i class="fas fa-database mr-3 text-green-600"></i>
                            FPL Data Management Dashboard
                        </h1>
                        <p class="text-gray-600 mt-2">Manage and monitor Fantasy Premier League data from Elo Insights repository</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="refreshStatus()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-sync-alt mr-2"></i>Refresh Status
                        </button>
                        <a href="{{ route('fpl.dashboard') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-chart-line mr-2"></i>View Analysis Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Data Status -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-2xl {{ $isDataUpToDate ? 'text-green-500' : 'text-red-500' }}"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Data Status</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $isDataUpToDate ? 'Up to Date' : 'Needs Update' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Gameweek -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-futbol text-2xl text-blue-500"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Current Gameweek</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $currentGameweek ? $currentGameweek->name : 'N/A' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Gameweek -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-forward text-2xl text-purple-500"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Next Gameweek</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $nextGameweek ? $nextGameweek->name : 'N/A' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Last Update -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-history text-2xl text-orange-500"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Last Update</dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $lastUpdate ? \Carbon\Carbon::parse($lastUpdate)->diffForHumans() : 'Never' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Summary -->
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-chart-bar mr-2 text-blue-600"></i>
                    Database Summary
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach($dataSummary as $table => $count)
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ number_format($count) }}</div>
                        <div class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $table) }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Import All Data -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-download mr-2 text-green-600"></i>
                        Import All Data
                    </h3>
                    <p class="text-gray-600 mb-4">Import all FPL data from Elo Insights repository</p>
                    <div class="space-y-3">
                        <button onclick="importAllData()" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-download mr-2"></i>Import All Data
                        </button>
                        <button onclick="importAllData(true)" class="w-full bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-force mr-2"></i>Force Import (Override)
                        </button>
                    </div>
                </div>

                <!-- Specific Data Import -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-list mr-2 text-blue-600"></i>
                        Import Specific Data
                    </h3>
                    <div class="space-y-2">
                        @foreach($availableFiles as $type => $file)
                        <button onclick="importSpecific('{{ $type }}')" 
                                class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm">
                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Gameweek Update -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-calendar-week mr-2 text-purple-600"></i>
                        Update Gameweek
                    </h3>
                    <p class="text-gray-600 mb-4">Update data for specific gameweek</p>
                    <div class="space-y-3">
                        <input type="number" id="gameweekInput" min="1" max="38" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Gameweek number (1-38)">
                        <button onclick="updateGameweek()" class="w-full bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            <i class="fas fa-sync-alt mr-2"></i>Update Gameweek
                        </button>
                    </div>
                </div>
            </div>

            <!-- Command Line Interface -->
            <div class="bg-white shadow rounded-lg p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-terminal mr-2 text-gray-600"></i>
                    Command Line Interface
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Available Commands:</h4>
                        <div class="bg-gray-100 p-3 rounded text-sm font-mono space-y-1">
                            <div><span class="text-blue-600">php artisan fpl:import</span> - Import all data</div>
                            <div><span class="text-blue-600">php artisan fpl:import --type=teams</span> - Import specific data</div>
                            <div><span class="text-blue-600">php artisan fpl:update</span> - Check and update data</div>
                            <div><span class="text-blue-600">php artisan fpl:update --check-only</span> - Check status only</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Scheduled Updates:</h4>
                        <div class="bg-green-50 p-3 rounded text-sm space-y-1">
                            <div><i class="fas fa-clock mr-2 text-green-600"></i>5:00 AM UTC - Automatic update</div>
                            <div><i class="fas fa-clock mr-2 text-green-600"></i>5:00 PM UTC - Automatic update</div>
                            <div><i class="fas fa-calendar mr-2 text-green-600"></i>Sunday 2:00 AM - Weekly refresh</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repository Information -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fab fa-github mr-2 text-gray-800"></i>
                    FPL Elo Insights Repository
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Data Source:</h4>
                        <p class="text-gray-600 mb-2">
                            <a href="https://github.com/olbauday/FPL-Elo-Insights" target="_blank" class="text-blue-600 hover:underline">
                                https://github.com/olbauday/FPL-Elo-Insights
                            </a>
                        </p>
                        <p class="text-sm text-gray-500">
                            Comprehensive FPL dataset with official API data, match stats, and team Elo ratings.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Update Schedule:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li><i class="fas fa-circle text-green-500 mr-2" style="font-size: 6px;"></i>Twice daily at 5:00 AM & 5:00 PM UTC</li>
                            <li><i class="fas fa-circle text-green-500 mr-2" style="font-size: 6px;"></i>CSV format for easy integration</li>
                            <li><i class="fas fa-circle text-green-500 mr-2" style="font-size: 6px;"></i>Automatic data refresh</li>
                            <li><i class="fas fa-circle text-green-500 mr-2" style="font-size: 6px;"></i>Match-aligned with official FPL IDs</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl">
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mr-4"></div>
                <span class="text-lg font-medium">Processing...</span>
            </div>
            <p class="text-gray-600 mt-2" id="loadingMessage">Please wait while we process your request.</p>
        </div>
    </div>

    <!-- Success/Error Toast -->
    <div id="toast" class="fixed top-4 right-4 z-50 hidden">
        <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 max-w-sm">
            <div class="flex items-center">
                <div id="toastIcon" class="flex-shrink-0 mr-3"></div>
                <div>
                    <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
                </div>
                <button onclick="hideToast()" class="ml-4 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // CSRF token setup
        window.csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Show loading modal
        function showLoading(message = 'Processing...') {
            document.getElementById('loadingMessage').textContent = message;
            document.getElementById('loadingModal').classList.remove('hidden');
            document.getElementById('loadingModal').classList.add('flex');
        }

        // Hide loading modal
        function hideLoading() {
            document.getElementById('loadingModal').classList.add('hidden');
            document.getElementById('loadingModal').classList.remove('flex');
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const icon = document.getElementById('toastIcon');
            const messageEl = document.getElementById('toastMessage');

            messageEl.textContent = message;
            
            if (type === 'success') {
                icon.innerHTML = '<i class="fas fa-check-circle text-green-500 text-xl"></i>';
            } else {
                icon.innerHTML = '<i class="fas fa-exclamation-circle text-red-500 text-xl"></i>';
            }

            toast.classList.remove('hidden');
            
            setTimeout(() => {
                hideToast();
            }, 5000);
        }

        // Hide toast
        function hideToast() {
            document.getElementById('toast').classList.add('hidden');
        }

        // Import all data
        async function importAllData(force = false) {
            showLoading('Importing all FPL data... This may take a few minutes.');
            
            try {
                const response = await fetch('{{ route("fpl.data.import.all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrf_token
                    },
                    body: JSON.stringify({ force: force })
                });

                const result = await response.json();
                hideLoading();

                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showToast('Import failed: ' + error.message, 'error');
            }
        }

        // Import specific data type
        async function importSpecific(type) {
            showLoading(`Importing ${type.replace('_', ' ')} data...`);
            
            try {
                const response = await fetch('{{ route("fpl.data.import.specific") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrf_token
                    },
                    body: JSON.stringify({ type: type })
                });

                const result = await response.json();
                hideLoading();

                if (result.success) {
                    showToast(result.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showToast('Import failed: ' + error.message, 'error');
            }
        }

        // Update gameweek
        async function updateGameweek() {
            const gameweek = document.getElementById('gameweekInput').value;
            
            if (!gameweek || gameweek < 1 || gameweek > 38) {
                showToast('Please enter a valid gameweek number (1-38)', 'error');
                return;
            }

            showLoading(`Updating gameweek ${gameweek} data...`);
            
            try {
                const response = await fetch('{{ route("fpl.data.update.gameweek") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrf_token
                    },
                    body: JSON.stringify({ gameweek: parseInt(gameweek) })
                });

                const result = await response.json();
                hideLoading();

                if (result.success) {
                    showToast(result.message, 'success');
                    document.getElementById('gameweekInput').value = '';
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                hideLoading();
                showToast('Update failed: ' + error.message, 'error');
            }
        }

        // Refresh status
        async function refreshStatus() {
            showLoading('Refreshing data status...');
            
            try {
                await new Promise(resolve => setTimeout(resolve, 1000)); // Small delay for UX
                location.reload();
            } catch (error) {
                hideLoading();
                showToast('Failed to refresh status', 'error');
            }
        }
    </script>
</body>
</html>
