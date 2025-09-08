<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - Fantasy Premier League</title>
    
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
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full space-y-8 p-8">
        <div>
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-fpl-purple rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-xl">PL</span>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Already have an account?
                <a href="/login" class="font-medium text-fpl-purple hover:text-fpl-magenta">
                    Sign in here
                </a>
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="#" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="first-name" class="block text-sm font-medium text-gray-700">First Name</label>
                    <input id="first-name" name="first-name" type="text" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-fpl-purple focus:border-fpl-purple">
                </div>
                <div>
                    <label for="last-name" class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input id="last-name" name="last-name" type="text" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-fpl-purple focus:border-fpl-purple">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input id="email" name="email" type="email" required 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-fpl-purple focus:border-fpl-purple">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-fpl-purple focus:border-fpl-purple">
                </div>
                <div>
                    <label for="confirm-password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input id="confirm-password" name="confirm-password" type="password" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-fpl-purple focus:border-fpl-purple">
                </div>
            </div>

            <div class="flex items-center">
                <input id="terms" name="terms" type="checkbox" required class="h-4 w-4 text-fpl-purple focus:ring-fpl-purple border-gray-300 rounded">
                <label for="terms" class="ml-2 block text-sm text-gray-900">
                    I agree to the <a href="#" class="text-fpl-purple hover:text-fpl-magenta">Terms and Conditions</a>
                </label>
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-fpl-green text-fpl-purple hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fpl-green">
                    Create Account
                </button>
            </div>
        </form>
    </div>
</body>
</html>
