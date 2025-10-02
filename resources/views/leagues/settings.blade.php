<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>League Settings - {{ $league->name }} - Fantasy Premier League</title>

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

    <div class="min-h-screen py-8">
        <!-- Header -->
        <div class="max-w-4xl mx-auto px-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">League Settings</h1>
                    <p class="text-white/80">Manage your league: {{ $league->name }}</p>
                </div>
                <a href="{{ route('leagues.show', $league) }}" class="text-white hover:text-fpl-green transition-colors">
                    ← Back to League
                </a>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4">
            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-fpl-green/20 border border-fpl-green text-white rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✓</span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Error Messages -->
            @if($errors->any())
                <div class="bg-red-500/20 border border-red-500 text-white rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-xl">⚠</span>
                        <span class="font-semibold">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside ml-6">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Settings Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">General Settings</h2>

                        <form action="{{ route('leagues.update-settings', $league) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- League Name -->
                            <div class="mb-6">
                                <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">
                                    League Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name', $league->name) }}"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-fpl-purple focus:border-transparent"
                                    maxlength="100"
                                    required
                                >
                                <p class="text-xs text-gray-500 mt-1">Maximum 100 characters</p>
                            </div>

                            <!-- Description -->
                            <div class="mb-6">
                                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-fpl-purple focus:border-transparent"
                                    maxlength="500"
                                >{{ old('description', $league->description) }}</textarea>
                                <p class="text-xs text-gray-500 mt-1">Maximum 500 characters (optional)</p>
                            </div>

                            <!-- Max Entries -->
                            <div class="mb-6">
                                <label for="max_entries" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Maximum Members <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="max_entries"
                                    name="max_entries"
                                    value="{{ old('max_entries', $league->max_entries) }}"
                                    min="{{ $league->current_entries }}"
                                    max="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-fpl-purple focus:border-transparent"
                                    required
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    Current members: {{ $league->current_entries }}. Must be at least {{ $league->current_entries }}.
                                </p>
                            </div>

                            <!-- Fixed Fields (Read-only) -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3">Fixed Settings</h3>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">League Code:</span>
                                        <span class="font-mono font-bold text-gray-900">{{ $league->league_code }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">League Type:</span>
                                        <span class="font-semibold text-gray-900">{{ ucfirst($league->type) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Privacy:</span>
                                        <span class="font-semibold text-gray-900">{{ ucfirst($league->privacy) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Created:</span>
                                        <span class="font-semibold text-gray-900">{{ $league->created_at->format('M j, Y') }}</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-3">
                                    <em>These settings cannot be changed after league creation.</em>
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-3">
                                <button
                                    type="submit"
                                    class="flex-1 bg-fpl-purple text-white py-3 px-6 rounded-lg font-bold hover:bg-opacity-90 transition-all"
                                >
                                    Save Changes
                                </button>
                                <a
                                    href="{{ route('leagues.show', $league) }}"
                                    class="flex-1 bg-gray-200 text-gray-800 py-3 px-6 rounded-lg font-bold text-center hover:bg-gray-300 transition-all"
                                >
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- League Info -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">League Info</h3>
                        <div class="space-y-3 text-sm">
                            <div>
                                <span class="text-gray-600">Current Members</span>
                                <div class="font-bold text-lg text-fpl-purple">
                                    {{ $league->current_entries }}/{{ $league->max_entries }}
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-600">Admin</span>
                                <div class="font-semibold text-gray-900">{{ $league->admin->name }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Status</span>
                                <div class="font-semibold">
                                    @if($league->is_active)
                                        <span class="text-fpl-green">● Active</span>
                                    @else
                                        <span class="text-red-500">● Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6 border-2 border-red-500/20">
                        <h3 class="text-lg font-bold text-red-600 mb-4">Danger Zone</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Deleting a league is permanent and cannot be undone. All members will be removed.
                        </p>
                        <form action="{{ route('leagues.destroy', $league) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this league? This action cannot be undone!');">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="w-full bg-red-500 text-white py-2 px-4 rounded-lg font-semibold hover:bg-red-600 transition-all"
                            >
                                Delete League
                            </button>
                        </form>
                    </div>

                    <!-- Help -->
                    <div class="bg-white/95 backdrop-blur-sm rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">Need Help?</h3>
                        <p class="text-sm text-gray-600 mb-3">
                            Learn more about managing your league and customizing settings.
                        </p>
                        <a href="#" class="text-sm text-fpl-purple font-semibold hover:text-fpl-magenta">
                            View League Guide →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
