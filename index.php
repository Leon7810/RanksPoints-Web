<?php
include 'inc/config.php';
include 'inc/include.php';
require ('inc/steamauth/steamauth.php');  

if (isset($_SESSION['steamid'])) {
    require ('inc/steamauth/userInfo.php');
}

// Retrieve the search term from the query parameters; default to an empty string if not set
$search = $_GET['search'] ?? '';

// Set the class for search-related elements based on the site configuration
$setClass = empty($siteConfig['enable_search']) ? "disabled" : "";

// Define the number of items to display per page
$itemsPerPage = $siteConfig['rows_per_page'];

// Determine the current page number from the query parameters; default to 1 if not present
$page = max((int)($_GET['page'] ?? 1), 1);

// Calculate the SQL query offset based on the current page
$offset = ($page - 1) * $itemsPerPage;

// Prepare the appropriate SQL query based on whether a search term is present & check if geo-location features are enabled
if (!empty($siteConfig['enable_geoip'])) {
    // Geo-location features are enabled
    if ($search) {
        $sql = "SELECT base.*, geo.country_code FROM lvl_base AS base
                LEFT JOIN lvl_base_geoip AS geo ON base.steam = geo.steam
                WHERE base.name LIKE ? ORDER BY base.value DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $searchTerm = "%$search%";
        $stmt->bind_param("sii", $searchTerm, $itemsPerPage, $offset);
    } else {
        $sql = "SELECT base.*, geo.country_code FROM lvl_base AS base
                LEFT JOIN lvl_base_geoip AS geo ON base.steam = geo.steam
                ORDER BY base.value DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $itemsPerPage, $offset);
    }
} else {
    // Geo-location features are not enabled
    if ($search) {
        $sql = "SELECT * FROM lvl_base WHERE name LIKE ? ORDER BY value DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $searchTerm = "%$search%";
        $stmt->bind_param("sii", $searchTerm, $itemsPerPage, $offset);
    } else {
        $sql = "SELECT * FROM lvl_base ORDER BY value DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $itemsPerPage, $offset);
    }
}

// Execute the prepared statement and store the result set
$stmt->execute();
$result = $stmt->get_result();

// Fetch the total number of items in the database to calculate the total number of pages
$totalItemsResult = $conn->query("SELECT COUNT(*) as total FROM lvl_base");
$totalItems = $totalItemsResult->fetch_assoc()['total'] ?? 0;
$totalPages = (int) ceil($totalItems / $itemsPerPage); // Cast to int for clarity
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteConfig['title']) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/54ff0c56ce.js" crossorigin="anonymous"></script>
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favico/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favico/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/favico/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/img/favico/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/img/favico/android-chrome-512x512.png">
    <link rel="manifest" href="/img/favico/site.webmanifest">

    <link rel="stylesheet" href="inc/style.css">
</head>
<body>
<div class="container-fluid mt-5 col-10 px-5 pt-5 pb-5 rounded shadow-lg">
    <div class="text-center mb-5">
        <a href="index.php"><h1 class="title"><?= htmlspecialchars($siteConfig['main_title_1']) ?> <span class="text-secondary"><?= htmlspecialchars($siteConfig['main_title_2']) ?></span></h1></a>
        <div class="row">
            <?php if ($siteConfig['enable_search']): ?>
            <div class="col-12 mt-3">
                <form action="" method="get" class="form-inline justify-content-center">
                    <div class="search">
                        <i class="fa fa-search"></i>
                        <input type="text" name="search" class="form-control" placeholder="Look a player up..." value="<?= htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                    <button id="theme-toggle" class="btn btn-secondary mode"></button>
                    <?php if(!isset($_SESSION['steamid'])): ?>
                        <a href="?login" class="d-flex align-items-center justify-content-center bg-dark ml-2 col-3 rounded login mode btn btn-secondary">
                            <i class="fa-brands fa-steam"></i> &nbsp;Login via Steam
                        </a>
                    <?php else: ?>
                        <a href="profile.php" class="d-flex align-items-center justify-content-center bg-dark ml-2 col-1 rounded logout mode btn btn-secondary">
                            Profile
                        </a>
                        <a href="?logout" class="d-flex align-items-center justify-content-center bg-dark ml-2 col-1 rounded logout mode btn btn-secondary">
                            Logout
                        </a>
                    <?php endif; ?>

                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive rounded">
        <table class="table table-light">
            <thead class="thead-light">
                <tr class="text-center">
                    <th>Rank</th>
                    <th>Name</th>
                    <th>Points</th>
                    <th>Kills</th>
                    <th>Deaths</th>
                    <th>K/D</th>
                    <th>W/L</th>
                    <th>Rank</th>
                    <th>Playtime</th>
                    <th>Last Played</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($result->num_rows > 0) {
                        // Calculate the starting rank for the current page
                        $rank = ($page - 1) * $itemsPerPage + 1;

                        while($row = $result->fetch_assoc()) {
                            // Process and format data for each table row
                            $playtimeFormatted = formatPlaytime($row['playtime']);
                            $kdRatio = calculateKDRatio($row["kills"], $row["deaths"]);
                            $wlRatio = calculateWLRatio($row["round_win"], $row["round_lose"]);
                            $lastConnectDate = date("d-m-Y", $row['lastconnect']);

                            // Apply row color based on rank only if table colors are enabled and there is no search query
                            $rowClass = ($siteConfig['enable_table_colours'] && empty($_GET['search'])) ? getRowClass($rank) : '';

                            // Create the URL to the player's Steam profile
                            $steamProfileUrl = "https://steamcommunity.com/profiles/" . convertSteamIDToSteamID64($row['steam']);
                            
                            // Create the URL to the player's Steam profile picture
                            $profilePictureUrl = getSteamUserProfilePicture(convertSteamIDToSteamID64($row['steam']), $siteConfig['api_key']);

                            // Create the URL to the correct flag
                            $flagUrl = "";
                            if (!empty($siteConfig['enable_geoip']) && !empty($siteConfig['enable_flags']) && !empty($row['country_code'])) {
                                $flagUrl = "<img src='https://flagsapi.com/" . $row['country_code'] . "/flat/64.png' alt='Country Flag' style='width:30px; margin-right: 10px;'>";
                            }


                            echo "<tr class='$rowClass'>
                                    <td class='text-center align-middle'>$rank</td>
                                    <td class='bold align-middle'>
                                    $flagUrl <!-- Display flag directly -->
                                        <a href='$steamProfileUrl' target='_blank'><img src='$profilePictureUrl' alt='Steam Profile Picture' style='width:20%;border-radius: 50%;' class='mr-3'>
                                        {$row["name"]}</a>
                                    </td>
                                    <td  class='text-center align-middle'>{$row["value"]}</td>
                                    <td  class='text-center align-middle'>{$row["kills"]}</td>
                                    <td  class='text-center align-middle'>{$row["deaths"]}</td>
                                    <td  class='text-center align-middle'>$kdRatio</td>
                                    <td  class='text-center align-middle'>$wlRatio</td>
                                    <td  class='text-center align-middle'><img src='" . getRankImage($row["rank"]) . "' alt='Rank Image' style='width: 100px; height: 40px;'></td>
                                    <td  class='text-center align-middle'>$playtimeFormatted</td>
                                    <td  class='text-center align-middle'>$lastConnectDate</td>
                                </tr>";
                            $rank++;
                        }
                    } else {
                        echo "<tr><td colspan='10'>No records found :(. Is your Database configuration correct?</td></tr>";
                    }
                    $conn->close();
                ?>
            </tbody>
        </table>
        <?php if ($siteConfig['enable_pagination']): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <!-- Previous Page -->
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                </li>

                <!-- Page Number -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next Page -->
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php if($siteConfig['enable_footer']): ?>
<div class="d-flex justify-content-center mt-3">
    <footer class="text-center py-3 col-2 rounded">
        <h5 class="footer">Created by <a href="https://steamcommunity.com/id/LeonKong" class="text-primary">LeonKong ❤️</a></h5>
    </footer>
</div>
<?php endif; ?>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="inc/index.js"></script>
</body>
</html>