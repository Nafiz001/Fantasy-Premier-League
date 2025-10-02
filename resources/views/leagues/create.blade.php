<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <!-- Header -->
    <header class="bg-white/10 backdrop-blur-md border-b border-white/20">
        <div class="max-w-4xl mx-auto px-4 py-6">
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
    </header>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white/95 backdrop-blur-sm rounded-lg p-8">
            <form action="{{ route('leagues.store') }}" method="POST">
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
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fpl-green focus:border-fpl-green @error('name') border-red-500 @enderror"
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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fpl-green focus:border-fpl-green @error('max_entries') border-red-500 @enderror">
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
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-fpl-green focus:border-fpl-green @error('description') border-red-500 @enderror"
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
                    <button type="submit" class="px-6 py-3 bg-fpl-purple text-white rounded-lg font-semibold hover:bg-opacity-90 transition-colors">
                        Create League
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
</script>
</body>
</html>
