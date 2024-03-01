<?php

// Defining the paths of rank images
const RANK_IMAGES = [
    0 => 'ranks/silver_1.png',
    1 => 'ranks/silver_2.png',
    2 => 'ranks/silver_3.png',
    3 => 'ranks/silver_4.png',
    4 => 'ranks/silver_elite.png',
    5 => 'ranks/silver_elite_master.png',
    6 => 'ranks/gold_nova_1.png',
    7 => 'ranks/gold_nova_2.png',
    8 => 'ranks/gold_nova_3.png',
    9 => 'ranks/gold_nova_master.png',
    10 => 'ranks/master_guardian_1.png',
    11 => 'ranks/master_guardian_2.png',
    12 => 'ranks/master_guardian_elite.png',
    13 => 'ranks/dmg.png',
    14 => 'ranks/le.png',
    15 => 'ranks/lem.png',
    16 => 'ranks/supreme.png',
    17 => 'ranks/global.png'
];

/**
 * Get the file path to the image for a given rank ID.
 * 
 * @param int $rankId The ID of the rank.
 * @return string The file path to the rank image.
 */
function getRankImage(int $rankId): string {
    return RANK_IMAGES[$rankId] ?? 'ranks/none.png'; // Standard picture if no rank is found.
}

/**
 * Converts various Steam ID formats to SteamID64.
 * 
 * @param string $steamID The Steam ID to convert.
 * @return string The converted SteamID64, or the original ID if it's already in that format.
 */
function convertSteamIDToSteamID64(string $steamID): string {
    if (preg_match('/^STEAM_/', $steamID)) {
        $parts = explode(':', $steamID);
        return bcadd(bcadd(bcmul($parts[2], '2'), '76561197960265728'), $parts[1]);
    } elseif (is_numeric($steamID) && strlen($steamID) < 16) {
        return bcadd($steamID, '76561197960265728');
    } else {
        // No idea what it is, so just return it.
        return $steamID;
    }
}

/**
 * Formats the given playtime in seconds into a human-readable format.
 *
 * @param int $seconds The playtime in seconds.
 * @return string Formatted playtime as days, hours, and minutes.
 */
function formatPlaytime($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds - ($days * 86400)) / 3600);
    $minutes = floor(($seconds - ($days * 86400) - ($hours * 3600)) / 60);
    return $days . "d " . $hours . "h " . $minutes . "m";
}

/**
 * Calculates the Kill/Death (K/D) ratio.
 *
 * @param int $kills Number of kills.
 * @param int $deaths Number of deaths.
 * @return string The K/D ratio, formatted to two decimal places.
 */
function calculateKDRatio($kills, $deaths) {
    return number_format(($deaths == 0 ? $kills : $kills / $deaths), 2, '.', '');
}

/**
 * Calculates the Win/Loss (W/L) ratio.
 *
 * @param int $wins Number of wins.
 * @param int $losses Number of losses.
 * @return string The W/L ratio, formatted to two decimal places.
 */
function calculateWLRatio($wins, $losses) {
    return number_format(($losses == 0 ? $wins : $wins / $losses), 2, '.', '');
}

/**
 * Determines the CSS class for a row based on the player's rank.
 *
 * @param int $rank The rank of the player.
 * @return string The corresponding CSS class name.
 */
function getRowClass($rank) {
    switch ($rank) {
        case 1: return "gold";
        case 2: return "silver";
        case 3: return "bronze";
        default: return "";
    }
}

/**
 * Retrieves the full URL of a user's Steam profile picture using the Steam Web API.
 * Caches the URL to avoid repeated API calls.
 *
 * @param string $steamId The SteamID64 of the user whose profile picture is being requested.
 * @param string $apiKey Your personal Steam Web API key.
 * @param int $cacheDuration The duration in seconds for how long to cache the profile picture URL.
 * @return string The URL of the full-sized profile picture or a default image URL if not available.
 */
function getSteamUserProfilePicture($steamId, $apiKey, $cacheDuration = 86400) { // Default cache duration is 1 day
    $cacheFile = "cache/{$steamId}.json"; // Ensure the 'cache' directory exists and is writable
    // If cache file exists and is still valid, use it
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheDuration)) {
        $data = json_decode(file_get_contents($cacheFile), true);
    } else {
        $url = "http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=$apiKey&steamids=$steamId";
        
        // Use cURL to fetch data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        
        // Decode the JSON response
        $data = json_decode($result, true);
        
        // Cache the data
        file_put_contents($cacheFile, json_encode($data));
    }

    // Check if the response is valid and contains the avatar URL
    if (isset($data['response']['players'][0]['avatarfull'])) {
        return $data['response']['players'][0]['avatarfull'];
    } else {
        // Return a default image if no avatar is found
        return "ranks/no_pic.jpg";
    }
}

?>
