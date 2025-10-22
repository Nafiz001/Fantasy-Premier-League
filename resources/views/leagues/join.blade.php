<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Join League - Fantasy Premier League</title>

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
        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Join League</h1>
                    <p class="text-white/80">Enter a league code to join the competition</p>
                </div>
                <a href="{{ route('leagues.index') }}" class="text-white hover:text-fpl-green">
                    ← Back to Leagues
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-md mx-auto px-4 py-16">
        <div class="bg-white/95 backdrop-blur-sm rounded-lg p-8">
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

            <div class="text-center mb-8">
                <div class="text-6xl mb-4">🏆</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Join a League</h2>
                <p class="text-gray-600">Enter the 6-character league code to join</p>
            </div>

            <form action="{{ route('leagues.join-code') }}" method="POST" id="join-league-form">
                @csrf

                <div class="mb-6">
                    <label for="league_code" class="block text-sm font-medium text-gray-700 mb-2">League Code</label>
                    <input type="text"
                           id="league_code"
                           name="league_code"
                           value="{{ old('league_code') }}"
                           maxlength="6"
                           class="w-full px-4 py-3 text-center border border-gray-300 rounded-lg focus:ring-fpl-green focus:border-fpl-green font-mono text-2xl tracking-widest uppercase"
                           placeholder="ABC123"
                           style="text-transform: uppercase;"
                           required>
                </div>

                <div id="join-league-errors" class="hidden mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p id="join-league-error-text" class="text-red-600 text-sm"></p>
                </div>

                <button type="button" id="join-league-btn" class="w-full bg-fpl-purple text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-colors">
                    Join League
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-600 text-sm mb-4">Don't have a code?</p>
                <a href="{{ route('leagues.create') }}" class="text-fpl-purple hover:text-fpl-magenta font-semibold">
                    Create Your Own League
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-uppercase and format league code input
document.getElementById('league_code').addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
});

// League join functionality with proper event handling
function joinLeague(event) {
    console.log('joinLeague called');

    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    // Helper function to show errors
    function showJoinError(message) {
        const errorDiv = document.getElementById('join-league-errors');
        const errorText = document.getElementById('join-league-error-text');
        errorText.textContent = message;
        errorDiv.classList.remove('hidden');
    }

    // Helper function to hide errors
    function hideJoinError() {
        const errorDiv = document.getElementById('join-league-errors');
        errorDiv.classList.add('hidden');
    }

    // Hide any previous errors
    hideJoinError();

    // Get league code from form input
    const codeInput = document.getElementById('league_code');
    const code = codeInput.value.trim().toUpperCase();

    if (!code) {
        showJoinError('Please enter a league code.');
        codeInput.focus();
        return;
    }

    if (code.length !== 6) {
        showJoinError('League code must be 6 characters.');
        codeInput.focus();
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page.');
        return;
    }

    // Show loading state
    const submitBtn = document.getElementById('join-league-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Joining...';
    submitBtn.disabled = true;

    // Prepare form data
    const formData = new FormData();
    formData.set('_token', csrfToken.getAttribute('content'));
    formData.set('league_code', code);

    // Submit via fetch
    console.log('Submitting join request...');
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
        console.log('Join response received:', response.status);

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

            // Check if we got redirected to the league show page (success)
            if (html.includes('League code') && html.includes('members')) {
                console.log('Detected league show page - join success!');
                alert('Successfully joined the league!');
                return;
            }

            // Check for success indicators
            if (html.includes('Successfully joined') || html.includes('joined successfully')) {
                console.log('Detected success message');
                alert('Successfully joined the league!');
                window.location.href = '{{ route("leagues.index") }}';
                return;
            }

            // Check for validation errors
            if (html.includes('League not found') || html.includes('league not found')) {
                console.log('Detected league not found error');
                showJoinError('League not found. Please check the code and try again.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                return;
            }

            if (html.includes('already a member')) {
                console.log('Detected already member error');
                showJoinError('You are already a member of this league.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                return;
            }

            if (html.includes('league is full')) {
                console.log('Detected league full error');
                showJoinError('This league is full and cannot accept new members.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                return;
            }

            if (html.includes('validation')) {
                console.log('Detected validation error');
                showJoinError('Invalid league code. Please check and try again.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                return;
            }

            // Check for specific error messages
            if ((html.includes('error') || html.includes('Error')) && !html.includes('console.error')) {
                console.log('Detected error message');
                showJoinError('Failed to join league. Please try again.');
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
        console.error('League join error:', error);
        showJoinError('An error occurred while joining the league. Please try again.');
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    })
    .finally(() => {
        // Reset button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Event listener for join league button
document.addEventListener('DOMContentLoaded', function() {
    const joinBtn = document.getElementById('join-league-btn');
    if (joinBtn) {
        joinBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            joinLeague(e);
        });
    }

    // Focus on the input when page loads
    document.getElementById('league_code').focus();
});
</script>
</body>
</html>
