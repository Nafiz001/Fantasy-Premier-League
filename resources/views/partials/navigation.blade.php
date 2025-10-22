<!-- Navigation Header -->
<header class="bg-white/95 backdrop-blur-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-3">
                <img src="/logo.png" alt="Fantasy Premier League Logo" class="w-10 h-10 rounded-full">
                <span class="text-lg font-bold text-fpl-purple">Fantasy League</span>
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
            </nav>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-button" class="md:hidden p-2 rounded-md text-gray-700 hover:text-fpl-purple hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-fpl-purple">
                <span class="sr-only">Open main menu</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Mobile Navigation Menu -->
            <div id="mobile-menu" class="hidden md:hidden absolute top-16 left-0 right-0 bg-white border-b border-gray-200 shadow-lg z-40">
                <div class="px-4 py-4 space-y-4">
                    <a href="{{ route('dashboard') }}"
                       class="{{ request()->routeIs('dashboard') ? 'text-fpl-purple bg-fpl-purple/10' : 'text-gray-700 hover:text-fpl-purple hover:bg-gray-50' }} block px-3 py-2 rounded-md font-medium transition-colors">
                       My Team
                    </a>
                    <a href="{{ route('pick.team') }}"
                       class="{{ request()->routeIs('pick.team') ? 'text-fpl-purple bg-fpl-purple/10' : 'text-gray-700 hover:text-fpl-purple hover:bg-gray-50' }} block px-3 py-2 rounded-md font-medium transition-colors">
                       Pick Team
                    </a>
                    <a href="{{ route('transfers') }}"
                       class="{{ request()->routeIs('transfers*') ? 'text-fpl-purple bg-fpl-purple/10' : 'text-gray-700 hover:text-fpl-purple hover:bg-gray-50' }} block px-3 py-2 rounded-md font-medium transition-colors">
                       Transfers
                    </a>
                    <a href="{{ route('points') }}"
                       class="{{ request()->routeIs('points*') ? 'text-fpl-purple bg-fpl-purple/10' : 'text-gray-700 hover:text-fpl-purple hover:bg-gray-50' }} block px-3 py-2 rounded-md font-medium transition-colors">
                       Points
                    </a>
                    <a href="{{ route('fixtures') }}"
                       class="{{ request()->routeIs('fixtures*') ? 'text-fpl-purple bg-fpl-purple/10' : 'text-gray-700 hover:text-fpl-purple hover:bg-gray-50' }} block px-3 py-2 rounded-md font-medium transition-colors">
                       Fixtures
                    </a>
                    <a href="{{ route('leagues.index') }}"
                       class="{{ request()->routeIs('leagues*') ? 'text-fpl-purple bg-fpl-purple/10' : 'text-gray-700 hover:text-fpl-purple hover:bg-gray-50' }} block px-3 py-2 rounded-md font-medium transition-colors">
                       Leagues
                    </a>

                    <!-- Mobile User Info -->
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="px-3 py-2">
                            <div class="text-sm font-semibold text-gray-900">{{ auth()->user()->team_name ?? 'My Team' }}</div>
                            <div class="text-xs text-gray-500">{{ auth()->user()->name }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-md transition-colors">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-semibold text-gray-900">{{ auth()->user()->team_name ?? 'My Team' }}</div>
                    <div class="text-xs text-gray-500">{{ auth()->user()->name }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Logout</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');

                    // Update button icon (hamburger to X)
                    const icon = mobileMenuButton.querySelector('svg');
                    if (mobileMenu.classList.contains('hidden')) {
                        // Show hamburger
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                    } else {
                        // Show X
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
                    }
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.add('hidden');
                        // Reset button icon
                        const icon = mobileMenuButton.querySelector('svg');
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                    }
                });

                // Close mobile menu when clicking on a link
                mobileMenu.addEventListener('click', function(event) {
                    if (event.target.tagName === 'A') {
                        mobileMenu.classList.add('hidden');
                        // Reset button icon
                        const icon = mobileMenuButton.querySelector('svg');
                        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                    }
                });
            }
        });
    </script>
</header>
