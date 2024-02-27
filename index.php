<?php
include 'inc/config.php';
include 'inc/include.php';

// Initialize the search term variable
$search = $_GET['search'] ?? '';

// Check if search is enabled and set the appropriate class
$setClass = $siteConfig['enable_search'] ? "" : "disabled";

// SQL query string
$sql = "SELECT * FROM lvl_base ORDER BY value DESC";

// Check if there is a search term
if ($search) {
    // Prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM lvl_base WHERE name LIKE ? ORDER BY value DESC");
    $searchTerm = "%$search%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // If no search query, perform standard query
    $result = $conn->query($sql);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteConfig['title']) ?></title>
    <script src="https://kit.fontawesome.com/54ff0c56ce.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1><?= htmlspecialchars($siteConfig['main_title_1']) ?> <span class="title2"><?= htmlspecialchars($siteConfig['main_title_2']) ?></span></h1>

<!-- Search form -->
<div class="search-container <?= $setClass ?>">
    <form action="" method="get">
        <input type="text" name="search" placeholder="Search a player..." value="<?= htmlspecialchars($search); ?>">
        <input type="submit" value="Search">
    </form>
</div>

<table class="container">
    <!-- Table Headers -->
    <thead>
		<tr>
        <th class="rank-header"><h1><i class="fa-solid fa-arrow-down-wide-short"></i> &nbsp;</h1></th>
        <th class="name-header"><h1>Name</h1></th>
        <th class="points-header"><h1>Points</h1></th>
        <th class="kills-header"><h1>Kills</h1></th>
        <th class="deaths-header"><h1>Deaths</h1></th>
        <th class="kd-header"><h1>K/D</h1></th>
        <th class="wl-header"><h1>W/L</h1></th>
        <th class="rank-header"><h1>Rank</h1></th>
        <th class="playtime-header"><h1>Playtime</h1></th>
        <th class="last-played-header"><h1>Last played</h1></th>
		</tr>
	</thead>
	<tbody>
        <?php
            if ($result->num_rows > 0) {
                $rank = 1;
                while($row = $result->fetch_assoc()) {
                    // Process and format data for each table row
                    $playtimeFormatted = formatPlaytime($row['playtime']);
                    $kdRatio = calculateKDRatio($row["kills"], $row["deaths"]);
                    $wlRatio = calculateWLRatio($row["round_win"], $row["round_lose"]);
                    $lastConnectDate = date("d-m-Y", $row['lastconnect']);

                    // Determine the CSS class for the row based on rank, only if there's no search query
                    $rowClass = empty($_GET['search']) ? getRowClass($rank) : '';

                    // Create the URL to the player's Steam profile
                    $steamProfileUrl = "https://steamcommunity.com/profiles/" . convertSteamIDToSteamID64($row['steam']);

                    echo "<tr class='$rowClass'>
                            <td>$rank</td>
                            <td class='bold'>{$row["name"]} <a href='$steamProfileUrl' target='_blank'><i class='fa-solid fa-up-right-from-square icon'></i></a></td>
                            <td>{$row["value"]}</td>
                            <td>{$row["kills"]}</td>
                            <td>{$row["deaths"]}</td>
                            <td>$kdRatio</td>
                            <td>$wlRatio</td>
                            <td><img src='" . getRankImage($row["rank"]) . "' alt='Rank Image' style='width: 100px; height: 40px;'></td>
                            <td>$playtimeFormatted</td>
                            <td>$lastConnectDate</td>
                          </tr>";
                    $rank++;
                }
            } else {
                echo "<tr><td colspan='10'>No records found</td></tr>";
            }
            $conn->close();
        ?>
    </tbody>
</table>

<?php if($siteConfig['enable_footer']): ?>
<footer>
    <h3>Created by <a href="https://steamcommunity.com/id/LeonKong" style="color: #FB667A; font-weight:bold">LeonKong ❤️</a>.</h3>
</footer>
<?php endif; ?>

</body>
</html>


