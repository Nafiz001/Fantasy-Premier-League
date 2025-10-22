<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        <!-- Error Messages -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            There were some errors:
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul role="list" class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

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
                                        <form action="{{ route('leagues.join-code') }}" method="POST" class="inline league-join-form">
                                            @csrf
                                            <input type="hidden" name="league_code" value="{{ $league->league_code }}">
                                            <button type="button" class="text-xs bg-fpl-green text-fpl-purple px-3 py-1 rounded font-semibold hover:bg-opacity-90 join-league-btn">
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
                    <form action="{{ route('leagues.join-code') }}" method="POST" id="quick-join-form">
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

                        <div id="quick-join-errors" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p id="quick-join-error-text" class="text-red-600 text-sm"></p>
                        </div>

                        <button type="button" id="quick-join-btn" class="w-full bg-fpl-purple text-white py-2 rounded-lg font-semibold hover:bg-opacity-90">
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

    // League join functionality with proper event handling
    function joinLeague(event, leagueCode = null) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        // Helper function to show errors
        function showQuickJoinError(message) {
            const errorDiv = document.getElementById('quick-join-errors');
            const errorText = document.getElementById('quick-join-error-text');
            errorText.textContent = message;
            errorDiv.classList.remove('hidden');
        }

        // Helper function to hide errors
        function hideQuickJoinError() {
            const errorDiv = document.getElementById('quick-join-errors');
            errorDiv.classList.add('hidden');
        }

        // Hide any previous errors
        hideQuickJoinError();

        // Get league code from parameter or form input
        let code = leagueCode;
        if (!code) {
            code = document.getElementById('league_code').value.trim().toUpperCase();
            if (!code) {
                showQuickJoinError('Please enter a league code.');
                document.getElementById('league_code').focus();
                return;
            }
        }

        if (code.length !== 6) {
            showQuickJoinError('League code must be 6 characters.');
            document.getElementById('league_code').focus();
            return;
        }

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            showQuickJoinError('CSRF token not found. Please refresh the page.');
            return;
        }

        // Show loading state
        const submitBtn = leagueCode ?
            event.target :
            document.getElementById('quick-join-btn');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Joining...';
        submitBtn.disabled = true;

        // Prepare form data
        const formData = new FormData();
        formData.set('_token', csrfToken.getAttribute('content'));
        formData.set('league_code', code);

        // Submit via fetch
        console.log('Submitting quick join request...');
        fetch('{{ route("leagues.join-code") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
            },
            credentials: 'same-origin',
            redirect: 'follow'
        })
        .then(response => {
            console.log('Quick join response received:', response.status);

            // Check for session expiry
            if (response.status === 401 || response.status === 419) {
                alert('Your session has expired. Please log in again.');
                window.location.href = '{{ route("login") }}';
                return;
            }

            return response.text();
        })
        .then(html => {
            if (html) {
                console.log('Response HTML length:', html.length);

                // Check if we got redirected to login page
                if (html.includes('login') || html.includes('welcome') || html.includes('Please log in')) {
                    alert('Your session has expired. Please log in again.');
                    window.location.href = '{{ route("login") }}';
                    return;
                }

                // Check for validation errors
                if (html.includes('League not found') || html.includes('league not found')) {
                    console.log('Detected league not found error');
                    showQuickJoinError('League not found. Please check the code and try again.');
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                if (html.includes('already a member')) {
                    console.log('Detected already member error');
                    showQuickJoinError('You are already a member of this league.');
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                if (html.includes('league is full')) {
                    console.log('Detected league full error');
                    showQuickJoinError('This league is full and cannot accept new members.');
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                if (html.includes('validation')) {
                    console.log('Detected validation error');
                    showQuickJoinError('Invalid league code. Please check and try again.');
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Check for specific error messages
                if ((html.includes('error') || html.includes('Error')) && !html.includes('console.error')) {
                    console.log('Detected error message');
                    showQuickJoinError('Failed to join league. Please try again.');
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    return;
                }

                // Check if we got redirected to the league show page (success)
                if (html.includes('League code') && html.includes('members')) {
                    console.log('Detected league show page - join success!');
                    alert('Successfully joined the league!');
                    window.location.href = '{{ route("leagues.show", ":id") }}'.replace(':id', 'current'); // Will be handled by backend
                    return;
                }

                // Check for success indicators
                if (html.includes('Successfully joined') || html.includes('joined successfully')) {
                    console.log('Detected success message');
                    alert('Successfully joined the league!');
                    window.location.href = '{{ route("leagues.index") }}';
                    return;
                }

                // If we can't determine the result, assume success
                console.log('No specific indicators found, assuming success');
                alert('Successfully joined the league!');
                window.location.href = '{{ route("leagues.index") }}';
            }
        })
        .catch(error => {
            console.error('Quick join error:', error);
            showQuickJoinError('An error occurred while joining the league. Please try again.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    // Event listeners for league join buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Individual league join buttons
        document.querySelectorAll('.join-league-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Get league code from the form
                const form = this.closest('.league-join-form');
                const leagueCodeInput = form.querySelector('input[name="league_code"]');
                const leagueCode = leagueCodeInput ? leagueCodeInput.value : null;

                joinLeague(e, leagueCode);
            });
        });

        // Quick join button
        const quickJoinBtn = document.getElementById('quick-join-btn');
        if (quickJoinBtn) {
            quickJoinBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                joinLeague(e);
            });
        }
    });
    </script>
</body>
</html>
