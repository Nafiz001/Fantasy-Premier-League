<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fantasy Premier League</title>

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
        .hero-gradient { background: linear-gradient(135deg, #38003c 0%, #e90052 50%, #00ff85 100%); }
        .card-hover:hover { transform: translateY(-8px); transition: all 0.4s ease; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 20px;
            width: 90%;
            max-width: 900px;
            max-height: 90%;
            position: relative;
            transform: scale(0.7);
            transition: transform 0.3s ease;
        }

        .modal-overlay.active .modal-content {
            transform: scale(1);
        }

        .modal-close {
            position: absolute;
            top: -15px;
            right: -15px;
            width: 40px;
            height: 40px;
            background: #38003c;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            border: none;
            z-index: 1001;
        }

        .video-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            background: #000;
            border-radius: 8px;
            overflow: hidden;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100% !important;
            border: none;
            border-radius: 8px;
            display: block;
        }

        .video-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 18px;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <img src="/logo.png" alt="Premier League Logo" class="w-10 h-10 rounded-full">
                    <span class="text-lg font-bold text-fpl-purple">Premier League</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/login" class="bg-fpl-purple text-white px-6 py-2 rounded-full hover:bg-opacity-90 font-semibold transition-all duration-200">
                        Play Fantasy
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-5xl md:text-7xl font-black mb-6">
                FANTASY<br>
                <span class="text-fpl-green">PREMIER LEAGUE</span>
            </h1>
            <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
                The world's biggest fantasy football game. Create your team and compete with millions.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="/signup" class="bg-fpl-green text-fpl-purple px-8 py-4 rounded-full font-bold text-lg hover:bg-opacity-90 transition-all duration-200">
                    Start Playing Now
                </a>
                <button id="watch-how-to-play-btn" class="border-2 border-white text-white px-8 py-4 rounded-full font-bold text-lg hover:bg-white hover:text-fpl-purple transition-all duration-200">
                    Watch How to Play
                </button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Play Fantasy Premier League?</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Join millions of managers in the ultimate fantasy football experience
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-fpl-purple to-fpl-magenta rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-white font-bold text-2xl">✓</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Free to Play</h3>
                    <p class="text-gray-600">Join the world's biggest fantasy football game for free. No hidden costs.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-fpl-purple to-fpl-magenta rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-white font-bold text-2xl">⚽</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Official Game</h3>
                    <p class="text-gray-600">The only official fantasy game of the Premier League with real stats.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-r from-fpl-purple to-fpl-magenta rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-white font-bold text-2xl">🏆</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Win Prizes</h3>
                    <p class="text-gray-600">Compete for amazing prizes and bragging rights. Monthly rewards await.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center mb-4">
                <div class="w-8 h-8 bg-fpl-purple rounded-full flex items-center justify-center mr-2">
                    <span class="text-white font-bold text-xs">PL</span>
                </div>
                <span class="font-bold">Premier League</span>
            </div>
            <p class="text-gray-400 text-sm mb-8">The official fantasy football game of the Premier League.</p>
            <p class="text-gray-400 text-xs">&copy; 2024 Fantasy Premier League. All rights reserved.</p>
        </div>
    </footer>

    <!-- YouTube Video Modal -->
    <div id="video-modal" class="modal-overlay">
        <div class="modal-content">
            <button id="modal-close" class="modal-close">&times;</button>
            <div class="video-container">
                <div id="video-loading" class="video-loading">Loading video...</div>
                <iframe id="youtube-video"
                        src=""
                        title="How to Play Fantasy Premier League"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin">
                </iframe>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('video-modal');
        const modalClose = document.getElementById('modal-close');
        const watchBtn = document.getElementById('watch-how-to-play-btn');
        const youtubeVideo = document.getElementById('youtube-video');
        const videoLoading = document.getElementById('video-loading');

        // YouTube video ID
        const videoId = 'xYWmhnsOqs0';

        // Open modal and load video
        watchBtn.addEventListener('click', function() {
            // Show loading indicator
            videoLoading.style.display = 'block';

            // Load video
            const embedUrl = `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1&showinfo=0&fs=1&iv_load_policy=3&cc_load_policy=0&disablekb=1`;
            youtubeVideo.src = embedUrl;

            // Show modal
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Hide loading after video loads
            youtubeVideo.onload = function() {
                videoLoading.style.display = 'none';
            };

            // Fallback: hide loading after 3 seconds
            setTimeout(() => {
                videoLoading.style.display = 'none';
            }, 3000);
        });

        // Close modal
        modalClose.addEventListener('click', function() {
            closeModal();
        });

        // Close modal when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Close modal function
        function closeModal() {
            modal.classList.remove('active');
            youtubeVideo.src = ''; // Stop video
            videoLoading.style.display = 'none'; // Hide loading
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });
    </script>
</body>
</html>
