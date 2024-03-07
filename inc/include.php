<?php

// Defining the paths of rank images
const RANK_IMAGES = [
    0 => 'img/icons/silver_1.png',
    1 => 'img/icons/silver_2.png',
    2 => 'img/icons/silver_3.png',
    3 => 'img/icons/silver_4.png',
    4 => 'img/icons/silver_elite.png',
    5 => 'img/icons/silver_elite_master.png',
    6 => 'img/icons/gold_nova_1.png',
    7 => 'img/icons/gold_nova_2.png',
    8 => 'img/icons/gold_nova_3.png',
    9 => 'img/icons/gold_nova_master.png',
    10 => 'img/icons/master_guardian_1.png',
    11 => 'img/icons/master_guardian_2.png',
    12 => 'img/icons/master_guardian_elite.png',
    13 => 'img/icons/dmg.png',
    14 => 'img/icons/le.png',
    15 => 'img/icons/lem.png',
    16 => 'img/icons/supreme.png',
    17 => 'img/icons/global.png'
];

/**
 * Get the file path to the image for a given rank ID.
 * 
 * @param int $rankId The ID of the rank.
 * @return string The file path to the rank image.
 */
function getRankImage(int $rankId): string {
    return RANK_IMAGES[$rankId] ?? 'img/icons/none.png'; // Standard picture if no rank is found.
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
 * Converts a SteamID64 to a SteamID.
 *
 * This function converts a SteamID64 to the traditional SteamID format (STEAM_X:Y:Z).
 * The formula for this conversion is based on the standard SteamID64 conversion method.
 *
 * @param string $steamID64 The SteamID64 to be converted.
 * @return string The converted SteamID if the input is a valid SteamID64; otherwise, the original input.
 */
function convertSteamID64ToSteamID(string $steamID64): string {
    if (is_numeric($steamID64) && strlen($steamID64) >= 16) {
        $steamID64 = bcsub($steamID64, '76561197960265728');
        $steamID1 = bcmod($steamID64, '2'); 
        $steamID64 = bcsub($steamID64, $steamID1); 
        $steamID64 = bcdiv($steamID64, '2');

        // Assuming the universe is '0' for individual accounts
        $universe = 1; // Replace with logic to determine universe if available

        return "STEAM_$universe:" . $steamID1 . ":" . $steamID64;
    } else {
        // The input is not a valid SteamID64, return it as is.
        return $steamID64;
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
    // Ensure that $wins and $losses are not null, default to 0
    $wins = $wins ?? 0;
    $losses = $losses ?? 0;

    // Calculate the win/loss ratio
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
        return "img/no_pic.jpg";
    }
}

?>
