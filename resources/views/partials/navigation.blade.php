<!-- Navigation Header -->
<header class="bg-white/95 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-fpl-purple rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-lg">F</span>
                </div>
                <span class="text-lg font-bold text-fpl-purple">Fantasy</span>
            </div>

            <!-- Navigation Menu -->
            <nav class="hidden md:flex items-center space-x-8">
                <a href="{{ route('dashboard') }}"
                   class="{{ request()->routeIs('dashboard') ? 'text-fpl-purple border-b-2 border-fpl-purple' : 'text-gray-700 hover:text-fpl-purple' }} font-medium transition-colors">
                   My Team
                </a>
                <a href="{{ route('pick.team') }}"
                   class="{{ request()->routeIs('pick.team') ? 'text-fpl-purple border-b-2 border-fpl-purple' : 'text-gray-700 hover:text-fpl-purple' }} font-medium transition-colors">
                   Pick Team
                </a>
                <a href="{{ route('transfers') }}"
                   class="{{ request()->routeIs('transfers*') ? 'text-fpl-purple border-b-2 border-fpl-purple' : 'text-gray-700 hover:text-fpl-purple' }} font-medium transition-colors">
                   Transfers
                </a>
                <a href="{{ route('points') }}"
                   class="{{ request()->routeIs('points*') ? 'text-fpl-purple border-b-2 border-fpl-purple' : 'text-gray-700 hover:text-fpl-purple' }} font-medium transition-colors">
                   Points
                </a>
                <a href="{{ route('fixtures') }}"
                   class="{{ request()->routeIs('fixtures*') ? 'text-fpl-purple border-b-2 border-fpl-purple' : 'text-gray-700 hover:text-fpl-purple' }} font-medium transition-colors">
                   Fixtures
                </a>
                <a href="{{ route('leagues.index') }}"
                   class="{{ request()->routeIs('leagues*') ? 'text-fpl-purple border-b-2 border-fpl-purple' : 'text-gray-700 hover:text-fpl-purple' }} font-medium transition-colors">
                   Leagues
                </a>
                <a href="#" class="text-gray-700 hover:text-fpl-purple font-medium transition-colors">More</a>
            </nav>

            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-semibold text-gray-900">{{ auth()->user()->team_name ?? 'My Team' }}</div>
                    <div class="text-xs text-gray-500">{{ auth()->user()->name }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Logout</button>
                </form>
            </div>
        </div>
    </div>
</header>
