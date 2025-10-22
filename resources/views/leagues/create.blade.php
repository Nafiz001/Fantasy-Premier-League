<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create League - Fantasy Premier League</title>

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
    <!-- Navigation -->


    <!-- Page Header -->
    <div class="bg-gradient-to-r from-fpl-purple to-fpl-magenta py-12">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Create League</h1>
                    <p class="text-white/80">Set up your own Fantasy Premier League competition</p>
                </div>
                <a href="{{ route('leagues.index') }}" class="text-white hover:text-fpl-green">
                    ← Back to Leagues
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">
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

            <form action="{{ route('leagues.store') }}" method="POST" id="create-league-form">
                @csrf

                <!-- League Basic Info -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">League Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">League Name *</label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   maxlength="100"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fpl-green focus:border-fpl-green {{ $errors->has('name') ? 'border-red-500' : '' }}"
                                   placeholder="My FPL League"
                                   required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="max_entries" class="block text-sm font-medium text-gray-700 mb-2">Max Members *</label>
                            <select id="max_entries"
                                    name="max_entries"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fpl-green focus:border-fpl-green {{ $errors->has('max_entries') ? 'border-red-500' : '' }}"
                                <option value="10" {{ old('max_entries') == '10' ? 'selected' : '' }}>10 members</option>
                                <option value="20" {{ old('max_entries') == '20' ? 'selected' : '' }}>20 members</option>
                                <option value="50" {{ old('max_entries', '50') == '50' ? 'selected' : '' }}>50 members</option>
                                <option value="100" {{ old('max_entries') == '100' ? 'selected' : '' }}>100 members</option>
                            </select>
                            @error('max_entries')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description"
                                  name="description"
                                  rows="3"
                                  maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fpl-green focus:border-fpl-green {{ $errors->has('description') ? 'border-red-500' : '' }}"
                                  placeholder="Tell others what this league is about...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- League Type -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">League Type</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-fpl-green transition-colors">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="radio"
                                       name="type"
                                       value="classic"
                                       {{ old('type', 'classic') == 'classic' ? 'checked' : '' }}
                                       class="mt-1">
                                <div>
                                    <h3 class="font-semibold text-gray-900">Classic League</h3>
                                    <p class="text-sm text-gray-600">Managers ranked by total points across the season</p>
                                </div>
                            </label>
                        </div>

                        <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-fpl-green transition-colors">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="radio"
                                       name="type"
                                       value="head_to_head"
                                       {{ old('type') == 'head_to_head' ? 'checked' : '' }}
                                       class="mt-1">
                                <div>
                                    <h3 class="font-semibold text-gray-900">Head-to-Head</h3>
                                    <p class="text-sm text-gray-600">Managers face off each gameweek (wins/draws/losses)</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    @error('type')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Privacy Settings -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Privacy Settings</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-fpl-green transition-colors">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="radio"
                                       name="privacy"
                                       value="private"
                                       {{ old('privacy', 'private') == 'private' ? 'checked' : '' }}
                                       class="mt-1">
                                <div>
                                    <h3 class="font-semibold text-gray-900">Private League</h3>
                                    <p class="text-sm text-gray-600">Only people with the league code can join</p>
                                </div>
                            </label>
                        </div>

                        <div class="border-2 border-gray-200 rounded-lg p-4 hover:border-fpl-green transition-colors">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="radio"
                                       name="privacy"
                                       value="public"
                                       {{ old('privacy') == 'public' ? 'checked' : '' }}
                                       class="mt-1">
                                <div>
                                    <h3 class="font-semibold text-gray-900">Public League</h3>
                                    <p class="text-sm text-gray-600">Anyone can discover and join this league</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    @error('privacy')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('leagues.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="button" id="create-league-btn" class="px-6 py-3 bg-fpl-purple text-white rounded-lg font-semibold hover:bg-opacity-90 transition-colors">
                        Create League
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// League creation functionality with proper event handling
function createLeague(event) {
    console.log('createLeague called');
    console.log('DOM ready state:', document.readyState);

    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    // Get the form
    const form = document.getElementById('create-league-form');
    console.log('Form element:', form);

    if (!form) {
        alert('Form not found. Please check the page source.');
        console.error('Form with id "create-league-form" not found in DOM');
        console.log('Page HTML:', document.body.innerHTML.substring(0, 1000));
        return;
    }

    // Validate required fields
    const name = form.querySelector('#name').value.trim();
    const maxEntries = form.querySelector('#max_entries').value;
    const type = form.querySelector('input[name="type"]:checked');
    const privacy = form.querySelector('input[name="privacy"]:checked');

    if (!name) {
        alert('Please enter a league name.');
        form.querySelector('#name').focus();
        return;
    }

    if (!type) {
        alert('Please select a league type.');
        return;
    }

    if (!privacy) {
        alert('Please select privacy settings.');
        return;
    }

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('CSRF token not found. Please refresh the page.');
        return;
    }

    // Show loading state
    const submitBtn = document.getElementById('create-league-btn');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Creating...';
    submitBtn.disabled = true;

    // Prepare form data
    const formData = new FormData(form);
    formData.set('_token', csrfToken.getAttribute('content'));

    // Submit via fetch
    console.log('Starting fetch request...');
    const startTime = Date.now();

    fetch('{{ route("leagues.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
        },
        credentials: 'same-origin',
        redirect: 'follow' // Follow redirects
    })
    .then(response => {
        console.log('Fetch response received after', Date.now() - startTime, 'ms');
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);

        // Check for session expiry
        if (response.status === 401 || response.status === 419) {
            alert('Your session has expired. Please log in again.');
            window.location.href = '{{ route("login") }}';
            return;
        }

        return response.text();
    })
    .then(html => {
        console.log('Response text processed after', Date.now() - startTime, 'ms total');
        console.log('HTML length:', html ? html.length : 'null');
        console.log('Response HTML preview:', html ? html.substring(0, 500) : 'null');

        if (html) {
            // Check if we got redirected to login page
            if (html.includes('login') || html.includes('welcome') || html.includes('Please log in')) {
                console.log('Detected login redirect');
                alert('Your session has expired. Please log in again.');
                window.location.href = '{{ route("login") }}';
                return;
            }

            // Check if we got redirected to the league show page (success)
            if (html.includes('League code') && html.includes('members') && html.includes('Classic League')) {
                console.log('Detected league show page - success!');
                alert('League created successfully!');
                // Don't redirect since we're already on the show page
                return;
            }

            // Check for success indicators in flash messages or other content
            if (html.includes('Successfully created') || html.includes('created successfully') || html.includes('League created')) {
                console.log('Detected success message');
                alert('League created successfully!');
                window.location.href = '{{ route("leagues.index") }}';
                return;
            }

            // Check for validation errors
            if (html.includes('The name field is required') || html.includes('validation')) {
                console.log('Detected validation errors');
                alert('Please check the form for errors and try again.');
                // Reload the page to show validation errors
                window.location.reload();
                return;
            }

            // Check for specific error messages (but not console.error)
            if ((html.includes('error') || html.includes('Error')) && !html.includes('console.error')) {
                console.log('Detected error message');
                alert('Failed to create league. Please try again.');
                return;
            }

            // If we can't determine the result but got a 200 response, assume success
            console.log('No clear indicators found, but response OK - assuming success');
            alert('League created successfully!');
            window.location.href = '{{ route("leagues.index") }}';
        }
    })
    .catch(error => {
        console.error('League creation error after', Date.now() - startTime, 'ms:', error);
        alert('An error occurred while creating the league: ' + error.message);
    })
    .finally(() => {
        // Reset button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

// Add visual feedback for radio button selections
document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove selected state from all cards in this group
        const groupName = this.name;
        document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
            r.closest('div.border-2').classList.remove('border-fpl-green', 'bg-fpl-green/5');
            r.closest('div.border-2').classList.add('border-gray-200');
        });

        // Add selected state to this card
        this.closest('div.border-2').classList.remove('border-gray-200');
        this.closest('div.border-2').classList.add('border-fpl-green', 'bg-fpl-green/5');
    });

    // Apply initial state
    if (radio.checked) {
        radio.closest('div.border-2').classList.remove('border-gray-200');
        radio.closest('div.border-2').classList.add('border-fpl-green', 'bg-fpl-green/5');
    }
});

// Event listener for create league button
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded fired');
    const createBtn = document.getElementById('create-league-btn');
    console.log('Create button found:', createBtn);

    if (createBtn) {
        createBtn.addEventListener('click', function(e) {
            console.log('Button clicked');
            e.preventDefault();
            e.stopPropagation();
            createLeague(e);
        });
    } else {
        console.error('Create league button not found');
    }
});
</script>
</body>
</html>
