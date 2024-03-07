<?php
define('ROOT_DIR', realpath(__DIR__ . '/..'));
require_once ROOT_DIR . '/config.php';

if (empty($_SESSION['steam_uptodate']) || empty($_SESSION['steam_personaname'])) {
    require 'SteamConfig.php';
    $url = file_get_contents("https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$siteConfig['api_key']."&steamids=".$_SESSION['steamid']); 
    $content = json_decode($url, true);

    $_SESSION['steam_steamid'] = $content['response']['players'][0]['steamid'];
    $_SESSION['steam_communityvisibilitystate'] = $content['response']['players'][0]['communityvisibilitystate'];
    $_SESSION['steam_profilestate'] = $content['response']['players'][0]['profilestate'];
    $_SESSION['steam_personaname'] = $content['response']['players'][0]['personaname'];
    $_SESSION['steam_lastlogoff'] = $content['response']['players'][0]['lastlogoff'];
    $_SESSION['steam_profileurl'] = $content['response']['players'][0]['profileurl'];
    $_SESSION['steam_avatar'] = $content['response']['players'][0]['avatar'];
    $_SESSION['steam_avatarmedium'] = $content['response']['players'][0]['avatarmedium'];
    $_SESSION['steam_avatarfull'] = $content['response']['players'][0]['avatarfull'];
    $_SESSION['steam_personastate'] = $content['response']['players'][0]['personastate'];

    if (isset($content['response']['players'][0]['realname'])) { 
        $_SESSION['steam_realname'] = $content['response']['players'][0]['realname'];
    } else {
        $_SESSION['steam_realname'] = "Real name not given";
    }

    $_SESSION['steam_primaryclanid'] = $content['response']['players'][0]['primaryclanid'];
    $_SESSION['steam_timecreated'] = $content['response']['players'][0]['timecreated'];
    $_SESSION['steam_uptodate'] = time();

    // New data
    $_SESSION['steam_comments_allowed'] = $content['response']['players'][0]['commentpermission'];

    // Private data - user must be logged in
    $_SESSION['steam_clanid'] = $content['response']['players'][0]['primaryclanid'];
    $_SESSION['steam_gameserverip'] = $content['response']['players'][0]['gameserverip'];
    $_SESSION['steam_gameextrainfo'] = $content['response']['players'][0]['gameextrainfo'];
    $_SESSION['steam_cityid'] = $content['response']['players'][0]['cityid'];
    $_SESSION['steam_loccountrycode'] = $content['response']['players'][0]['loccountrycode'];
    $_SESSION['steam_locstatecode'] = $content['response']['players'][0]['locstatecode'];
    $_SESSION['steam_loccityid'] = $content['response']['players'][0]['loccityid'];
}

$steamprofile['steamid'] = $_SESSION['steam_steamid'];
$steamprofile['communityvisibilitystate'] = $_SESSION['steam_communityvisibilitystate'];
$steamprofile['profilestate'] = $_SESSION['steam_profilestate'];
$steamprofile['personaname'] = $_SESSION['steam_personaname'];
$steamprofile['lastlogoff'] = $_SESSION['steam_lastlogoff'];
$steamprofile['profileurl'] = $_SESSION['steam_profileurl'];
$steamprofile['avatar'] = $_SESSION['steam_avatar'];
$steamprofile['avatarmedium'] = $_SESSION['steam_avatarmedium'];
$steamprofile['avatarfull'] = $_SESSION['steam_avatarfull'];
$steamprofile['personastate'] = $_SESSION['steam_personastate'];
$steamprofile['realname'] = $_SESSION['steam_realname'];
$steamprofile['primaryclanid'] = $_SESSION['steam_primaryclanid'];
$steamprofile['timecreated'] = $_SESSION['steam_timecreated'];
$steamprofile['uptodate'] = $_SESSION['steam_uptodate'];

// New
$steamprofile['commentpermission'] = $_SESSION['steam_comments_allowed'];

// Private data - user must be logged in
$steamprofile['primaryclanid'] = $_SESSION['steam_clanid'];
$steamprofile['gameserverip'] = $_SESSION['steam_gameserverip'];
$steamprofile['gameextrainfo'] = $_SESSION['steam_gameextrainfo'];
$steamprofile['cityid'] = $_SESSION['steam_cityid'];
$steamprofile['loccountrycode'] = $_SESSION['steam_loccountrycode'];
$steamprofile['locstatecode'] = $_SESSION['steam_locstatecode'];
$steamprofile['loccityid'] = $_SESSION['steam_loccityid'];
?>
