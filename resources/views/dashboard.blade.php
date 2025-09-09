<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fantasy Premier League - Dashboard</title>
    
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
                        'fpl-cyan': '#37c2c2',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .nav-gradient { background: linear-gradient(90deg, #00ff85 0%, #37c2c2 50%, #e90052 100%); }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Main Navigation Bar with Gradient -->
    <nav class="nav-gradient text-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-12">
                <div class="flex items-center space-x-8">
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Status</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Points</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Pick Team</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Transfers</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Leagues & Cups</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Fixtures</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">The Scout</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Podcast</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Stats</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Prizes</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">Help</a>
                    <a href="#" class="text-sm font-medium hover:text-gray-200 transition-colors">FPL Challenge</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, {{ session('user.name') }}!</span>
                    <a href="/logout" class="text-sm font-medium hover:text-gray-200 transition-colors">Sign Out</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header with Logo -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <img src="/logo.png" alt="Premier League Logo" class="w-10 h-10">
                    <span class="text-lg font-bold text-fpl-purple">Fantasy Premier League</span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-600">
                        Gameweek 1
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Welcome to your FPL Dashboard!</h1>
            <p class="text-gray-600 mb-6">You have successfully signed in. Your team management tools are now available.</p>
            
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-r from-fpl-purple to-fpl-magenta text-white p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">Your Team</h3>
                    <p class="text-sm opacity-90">Build and manage your fantasy team</p>
                </div>
                <div class="bg-gradient-to-r from-fpl-cyan to-fpl-green text-white p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">Transfers</h3>
                    <p class="text-sm opacity-90">Make player transfers and changes</p>
                </div>
                <div class="bg-gradient-to-r from-fpl-green to-fpl-magenta text-white p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-2">Leagues</h3>
                    <p class="text-sm opacity-90">Join and create leagues with friends</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
