<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class FPLNewsService
{
    /**
     * Fetch latest FPL/Premier League news
     *
     * @param int $limit Number of news items to return
     * @return array
     */
    public function getLatestNews($limit = 6)
    {
        // Cache news for 1 hour to avoid excessive API calls
        return Cache::remember('fpl_news', 3600, function () use ($limit) {
            try {
                // Fetch from RSS feeds via rss2json
                $news = $this->fetchFromRSSFeeds();

                // Return requested number of items
                return array_slice($news, 0, $limit);
            } catch (\Exception $e) {
                Log::error('Failed to fetch FPL news: ' . $e->getMessage());
                return $this->getFallbackNews($limit);
            }
        });
    }

    /**
     * Fetch news from RSS feeds using rss2json.com
     *
     * @return array
     */
    private function fetchFromRSSFeeds()
    {
        $news = [];

        // List of RSS feeds to try
        $rssFeeds = [
            'BBC' => 'http://feeds.bbci.co.uk/sport/football/premier-league/rss.xml',
            'Sky Sports' => 'https://www.skysports.com/rss/12040',
            'Guardian' => 'https://www.theguardian.com/football/premierleague/rss',
        ];

        foreach ($rssFeeds as $source => $feedUrl) {
            try {
                // URL encode the RSS feed URL for rss2json
                $encodedUrl = urlencode($feedUrl);

                // Use rss2json.com API to convert RSS to JSON
                $response = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                    ])
                    ->get('https://api.rss2json.com/v1/api.json', [
                        'rss_url' => $feedUrl,
                        'api_key' => env('RSS2JSON_API_KEY', ''),
                        'count' => 10,
                        'order_by' => 'pubDate',
                        'order_dir' => 'desc'
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['status']) && $data['status'] === 'ok' && isset($data['items'])) {
                        foreach ($data['items'] as $item) {
                            // Skip if no valid link
                            if (empty($item['link']) || $item['link'] === '#') {
                                continue;
                            }

                            // Extract image from content or use placeholder
                            $image = $this->extractImageFromContent($item);

                            // Create news item with actual article link
                            $news[] = [
                                'title' => $item['title'] ?? 'Premier League News',
                                'description' => $this->cleanDescription($item['description'] ?? ''),
                                'link' => $item['link'], // Use actual article link from RSS
                                'image' => $image,
                                'date' => isset($item['pubDate']) ? date('M j, Y', strtotime($item['pubDate'])) : date('M j, Y'),
                                'source' => $source,
                            ];

                            // Log successful fetch
                            if (count($news) === 1) {
                                Log::info("Successfully fetched news from {$source}");
                            }
                        }
                    }
                } else {
                    // If rss2json fails, try direct XML parsing
                    Log::warning("rss2json failed for {$source} with status " . $response->status() . ", trying direct XML");
                    $directNews = $this->fetchDirectFromRSS($feedUrl, $source);
                    $news = array_merge($news, $directNews);
                }

                // If we got enough news, break
                if (count($news) >= 10) {
                    break;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to fetch from {$source}: " . $e->getMessage());
                // Try direct XML as fallback
                try {
                    $directNews = $this->fetchDirectFromRSS($feedUrl, $source);
                    $news = array_merge($news, $directNews);
                } catch (\Exception $e2) {
                    Log::error("Direct RSS also failed for {$source}: " . $e2->getMessage());
                }
                continue;
            }
        }

        // If no news fetched, return fallback
        if (empty($news)) {
            Log::warning("No news fetched from any source, using fallback");
            return $this->getFallbackNews(6);
        }

        return $news;
    }

    /**
     * Fetch news directly from RSS feed (fallback method)
     *
     * @param string $feedUrl
     * @param string $source
     * @return array
     */
    private function fetchDirectFromRSS($feedUrl, $source)
    {
        $news = [];

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ])
                ->get($feedUrl);

            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());

                if ($xml === false) {
                    return $news;
                }

                // Parse RSS feed
                $items = $xml->channel->item ?? $xml->item ?? [];

                foreach ($items as $item) {
                    // Get link
                    $link = (string)$item->link;
                    if (empty($link)) continue;

                    // Get title
                    $title = (string)$item->title;

                    // Get description
                    $description = (string)($item->description ?? '');

                    // Try to extract image from media:thumbnail or media:content
                    $image = null;

                    // Check for media namespace
                    $media = $item->children('http://search.yahoo.com/mrss/');
                    if (isset($media->thumbnail)) {
                        $image = (string)$media->thumbnail->attributes()->url;
                    } elseif (isset($media->content)) {
                        $image = (string)$media->content->attributes()->url;
                    }

                    // Try to extract image from description HTML
                    if (!$image) {
                        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $description, $matches);
                        if (isset($matches[1])) {
                            $image = $matches[1];
                        }
                    }

                    // Use placeholder if no image found
                    if (!$image) {
                        $image = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect width="400" height="300" fill="%2338003c"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="60" font-weight="bold"%3EFPL%3C/text%3E%3C/svg%3E';
                    }

                    // Get publication date
                    $pubDate = (string)($item->pubDate ?? '');

                    $news[] = [
                        'title' => $title,
                        'description' => $this->cleanDescription($description),
                        'link' => $link,
                        'image' => $image,
                        'date' => $pubDate ? date('M j, Y', strtotime($pubDate)) : date('M j, Y'),
                        'source' => $source,
                    ];

                    // Limit to 10 items per feed
                    if (count($news) >= 10) {
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Direct RSS fetch failed: " . $e->getMessage());
        }

        return $news;
    }

    /**
     * Extract image from RSS content
     *
     * @param array $item
     * @return string
     */
    private function extractImageFromContent($item)
    {
        // Try to get thumbnail first
        if (isset($item['thumbnail']) && !empty($item['thumbnail'])) {
            return $item['thumbnail'];
        }

        // Try to get enclosure (media)
        if (isset($item['enclosure']) && isset($item['enclosure']['link'])) {
            return $item['enclosure']['link'];
        }

        // Try to extract image from description/content
        if (isset($item['description'])) {
            preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $item['description'], $matches);
            if (isset($matches[1])) {
                return $matches[1];
            }
        }

        if (isset($item['content'])) {
            preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', $item['content'], $matches);
            if (isset($matches[1])) {
                return $matches[1];
            }
        }

        // Use placeholder image (FPL badge) - larger version for news cards
        return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect width="400" height="300" fill="%2338003c"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="60" font-weight="bold"%3EFPL%3C/text%3E%3C/svg%3E';
    }

    /**
     * Clean and truncate description
     *
     * @param string $description
     * @return string
     */
    private function cleanDescription($description)
    {
        // Remove HTML tags
        $text = strip_tags($description);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Trim whitespace
        $text = trim($text);

        // Truncate to 150 characters
        if (strlen($text) > 150) {
            $text = substr($text, 0, 150);
            $text = substr($text, 0, strrpos($text, ' ')) . '...';
        }

        return $text;
    }

    /**
     * Get fallback news when API fails
     *
     * @param int $limit
     * @return array
     */
    private function getFallbackNews($limit = 6)
    {
        $placeholderImage = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect width="400" height="300" fill="%2338003c"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" fill="white" font-size="60" font-weight="bold"%3EFPL%3C/text%3E%3C/svg%3E';

        $fallbackNews = [
            [
                'title' => 'FPL Tips: Captain Picks for This Gameweek',
                'description' => 'Explore the best captain options for the upcoming gameweek. Analyze fixtures, form, and statistics to make an informed decision.',
                'link' => 'https://www.premierleague.com/news',
                'image' => $placeholderImage,
                'date' => date('M j, Y'),
                'source' => 'FPL',
            ],
            [
                'title' => 'Injury News and Team Updates',
                'description' => 'Stay updated with the latest injury news and team selections. Make informed transfer decisions based on the latest team news.',
                'link' => 'https://www.premierleague.com/news',
                'image' => $placeholderImage,
                'date' => date('M j, Y'),
                'source' => 'FPL',
            ],
            [
                'title' => 'Differential Picks: Under the Radar Players',
                'description' => 'Discover hidden gems and differential picks that could give you an edge over your rivals in mini-leagues.',
                'link' => 'https://www.premierleague.com/news',
                'image' => $placeholderImage,
                'date' => date('M j, Y'),
                'source' => 'FPL',
            ],
            [
                'title' => 'Fixture Analysis: Plan Your Transfers',
                'description' => 'Look ahead at upcoming fixtures and plan your transfers accordingly. Target players with favorable fixture runs.',
                'link' => 'https://www.premierleague.com/news',
                'image' => $placeholderImage,
                'date' => date('M j, Y'),
                'source' => 'FPL',
            ],
            [
                'title' => 'Budget Players: Best Value Picks',
                'description' => 'Find the best budget players who offer great value for money. Build a balanced squad without breaking the bank.',
                'link' => 'https://www.premierleague.com/news',
                'image' => $placeholderImage,
                'date' => date('M j, Y'),
                'source' => 'FPL',
            ],
            [
                'title' => 'Chip Strategy: When to Use Your Chips',
                'description' => 'Learn the optimal times to activate your FPL chips like Wildcard, Triple Captain, and Bench Boost for maximum points.',
                'link' => 'https://www.premierleague.com/news',
                'image' => $placeholderImage,
                'date' => date('M j, Y'),
                'source' => 'FPL',
            ],
        ];

        return array_slice($fallbackNews, 0, $limit);
    }
}
