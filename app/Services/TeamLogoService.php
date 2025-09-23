<?php

namespace App\Services;

class TeamLogoService
{
    private static $teamLogoMapping = [
        // Map team short names to correct logo identifiers
        'ARS' => 'arsenal',
        'AVL' => 'aston-villa', 
        'BOU' => 'bournemouth',
        'BRE' => 'brentford',
        'BHA' => 'brighton',
        'CHE' => 'chelsea',
        'CRY' => 'crystal-palace',
        'EVE' => 'everton',
        'FUL' => 'fulham',
        'IPS' => 'ipswich',
        'LEI' => 'leicester',
        'LIV' => 'liverpool',
        'MCI' => 'manchester-city',
        'MUN' => 'manchester-united',
        'NEW' => 'newcastle',
        'NFO' => 'nottingham-forest',
        'SOU' => 'southampton',
        'TOT' => 'tottenham',
        'WHU' => 'west-ham',
        'WOL' => 'wolverhampton'
    ];

    private static $fallbackLogos = [
        'ARS' => 'https://logos-world.net/wp-content/uploads/2020/06/Arsenal-Logo.png',
        'AVL' => 'https://logos-world.net/wp-content/uploads/2020/06/Aston-Villa-Logo.png',
        'BOU' => 'https://logos-world.net/wp-content/uploads/2020/06/Bournemouth-Logo.png',
        'BRE' => 'https://logos-world.net/wp-content/uploads/2020/12/Brentford-Logo.png',
        'BHA' => 'https://logos-world.net/wp-content/uploads/2020/06/Brighton-Logo.png',
        'CHE' => 'https://logos-world.net/wp-content/uploads/2020/06/Chelsea-Logo.png',
        'CRY' => 'https://logos-world.net/wp-content/uploads/2020/06/Crystal-Palace-Logo.png',
        'EVE' => 'https://logos-world.net/wp-content/uploads/2020/06/Everton-Logo.png',
        'FUL' => 'https://logos-world.net/wp-content/uploads/2020/06/Fulham-Logo.png',
        'IPS' => 'https://logos-world.net/wp-content/uploads/2020/06/Ipswich-Town-Logo.png',
        'LEI' => 'https://logos-world.net/wp-content/uploads/2020/06/Leicester-City-Logo.png',
        'LIV' => 'https://logos-world.net/wp-content/uploads/2020/06/Liverpool-Logo.png',
        'MCI' => 'https://logos-world.net/wp-content/uploads/2020/06/Manchester-City-Logo.png',
        'MUN' => 'https://logos-world.net/wp-content/uploads/2020/06/Manchester-United-Logo.png',
        'NEW' => 'https://logos-world.net/wp-content/uploads/2020/06/Newcastle-United-Logo.png',
        'NFO' => 'https://logos-world.net/wp-content/uploads/2020/06/Nottingham-Forest-Logo.png',
        'SOU' => 'https://logos-world.net/wp-content/uploads/2020/06/Southampton-Logo.png',
        'TOT' => 'https://logos-world.net/wp-content/uploads/2020/06/Tottenham-Logo.png',
        'WHU' => 'https://logos-world.net/wp-content/uploads/2020/06/West-Ham-United-Logo.png',
        'WOL' => 'https://logos-world.net/wp-content/uploads/2020/06/Wolverhampton-Wanderers-Logo.png'
    ];

    /**
     * Get team logo URL for the given team short name
     */
    public static function getLogoUrl($teamShort, $source = 'api-football')
    {
        $teamShort = strtoupper($teamShort);
        
        switch ($source) {
            case 'api-football':
                return self::getApiFootballLogo($teamShort);
            case 'logos-world':
                return self::getLogosWorldLogo($teamShort);
            case 'football-data':
                return self::getFootballDataLogo($teamShort);
            default:
                return self::getFallbackLogo($teamShort);
        }
    }

    /**
     * Get logo from API-Football (free tier available)
     */
    private static function getApiFootballLogo($teamShort)
    {
        $teamMapping = [
            'ARS' => 42, // Arsenal
            'AVL' => 66, // Aston Villa
            'BOU' => 35, // Bournemouth
            'BRE' => 55, // Brentford
            'BHA' => 51, // Brighton
            'CHE' => 49, // Chelsea
            'CRY' => 52, // Crystal Palace
            'EVE' => 45, // Everton
            'FUL' => 36, // Fulham
            'IPS' => 40, // Ipswich
            'LEI' => 46, // Leicester
            'LIV' => 40, // Liverpool
            'MCI' => 50, // Manchester City
            'MUN' => 33, // Manchester United
            'NEW' => 34, // Newcastle
            'NFO' => 65, // Nottingham Forest
            'SOU' => 41, // Southampton
            'TOT' => 47, // Tottenham
            'WHU' => 48, // West Ham
            'WOL' => 39  // Wolverhampton
        ];

        $teamId = $teamMapping[$teamShort] ?? null;
        if ($teamId) {
            return "https://media.api-sports.io/football/teams/{$teamId}.png";
        }

        return self::getFallbackLogo($teamShort);
    }

    /**
     * Get logo from Football-Data.org (free tier available)
     */
    private static function getFootballDataLogo($teamShort)
    {
        $teamMapping = [
            'ARS' => 57, // Arsenal
            'AVL' => 58, // Aston Villa
            'BOU' => 1044, // Bournemouth
            'BRE' => 402, // Brentford
            'BHA' => 397, // Brighton
            'CHE' => 61, // Chelsea
            'CRY' => 354, // Crystal Palace
            'EVE' => 62, // Everton
            'FUL' => 63, // Fulham
            'IPS' => 349, // Ipswich
            'LEI' => 338, // Leicester
            'LIV' => 64, // Liverpool
            'MCI' => 65, // Manchester City
            'MUN' => 66, // Manchester United
            'NEW' => 67, // Newcastle
            'NFO' => 351, // Nottingham Forest
            'SOU' => 340, // Southampton
            'TOT' => 73, // Tottenham
            'WHU' => 563, // West Ham
            'WOL' => 76  // Wolverhampton
        ];

        $teamId = $teamMapping[$teamShort] ?? null;
        if ($teamId) {
            return "https://crests.football-data.org/{$teamId}.png";
        }

        return self::getFallbackLogo($teamShort);
    }

    /**
     * Get logo from Logos-World (direct image links)
     */
    private static function getLogosWorldLogo($teamShort)
    {
        return self::$fallbackLogos[$teamShort] ?? self::getFallbackLogo($teamShort);
    }

    /**
     * Get fallback logo (team initials)
     */
    private static function getFallbackLogo($teamShort)
    {
        return self::$fallbackLogos[$teamShort] ?? null;
    }

    /**
     * Get all Premier League teams with their logo URLs
     */
    public static function getAllTeamLogos($source = 'api-football')
    {
        $logos = [];
        foreach (array_keys(self::$teamLogoMapping) as $teamShort) {
            $logos[$teamShort] = self::getLogoUrl($teamShort, $source);
        }
        return $logos;
    }

    /**
     * Check if a team logo URL is valid
     */
    public static function isValidLogoUrl($url)
    {
        if (!$url) return false;
        
        $headers = @get_headers($url, 1);
        return $headers && strpos($headers[0], '200') !== false;
    }
}