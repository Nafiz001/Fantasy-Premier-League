<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <div class="text-center mb-8">
                <div class="text-6xl mb-4">🏆</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Join a League</h2>
                <p class="text-gray-600">Enter the 6-character league code to join</p>
            </div>

            <form action="{{ route('leagues.join-code') }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label for="league_code" class="block text-sm font-medium text-gray-700 mb-2">League Code</label>
                    <input type="text"
                           id="league_code"
                           name="league_code"
                           value="{{ old('league_code') }}"
                           maxlength="6"
                           class="w-full px-4 py-3 text-center border border-gray-300 rounded-lg focus:ring-fpl-green focus:border-fpl-green font-mono text-2xl tracking-widest uppercase @error('league_code') border-red-500 @enderror"
                           placeholder="ABC123"
                           style="text-transform: uppercase;"
                           required>
                    @error('league_code')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-fpl-purple text-white py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-colors">
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

// Focus on the input when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('league_code').focus();
});
</script>
</body>
</html>
