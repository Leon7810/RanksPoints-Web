<?php
include 'inc/config.php';
include 'inc/include.php';

// Initialize search term
$search = $_GET['search'] ?? '';

// Check if search is enabled and set the class
$setClass = $siteConfig['enable_search'] ? "" : "disabled";

$itemsPerPage = 10;

// Get the current page number from the URL, default to 1 if not present
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);

// Calculate the offset for the SQL query
$offset = ($page - 1) * $itemsPerPage;

// Modify SQL query to fetch only the items for the current page
$sql = "SELECT * FROM lvl_base ORDER BY value DESC LIMIT $itemsPerPage OFFSET $offset";

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

// Fetch total number of items to calculate total pages
$totalItemsResult = $conn->query("SELECT COUNT(*) as total FROM lvl_base");
$totalItems = $totalItemsResult->fetch_assoc()['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteConfig['title']) ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/54ff0c56ce.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="inc/style.css">
</head>
<body class="bg-dark text-white">
<div class="container-fluid mt-5 col-10">
    <div class="text-center mb-5">
        <h1><?= htmlspecialchars($siteConfig['main_title_1']) ?> <span class="text-secondary"><?= htmlspecialchars($siteConfig['main_title_2']) ?></h1>
        <div class="row mb-5">
            <div class="col-12 mt-3 mb-3">
                <form action="" method="get" class="form-inline justify-content-center">
                    <div class="form-group">
                        <input type="text" name="search" class="form-control" placeholder="Search a player..." value="<?= htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary ml-2">Search</button>
                </form>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-dark">
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

                            // Determine the CSS class for the row based on rank, only if there's no search query
                            $rowClass = empty($_GET['search']) ? getRowClass($rank) : '';

                            // Create the URL to the player's Steam profile
                            $steamProfileUrl = "https://steamcommunity.com/profiles/" . convertSteamIDToSteamID64($row['steam']);

                            
                            $profilePictureUrl = getSteamUserProfilePicture(convertSteamIDToSteamID64($row['steam']), $siteConfig['api_key']);

                            echo "<tr class='$rowClass'>
                                    <td class='text-center align-middle'>$rank</td>
                                    <td class='bold align-middle'><img src='$profilePictureUrl' alt='Steam Profile Picture' style='width:20%;border-radius: 50%;' class='mr-3'> {$row["name"]} <a href='$steamProfileUrl' target='_blank'><i class='fa-solid fa-up-right-from-square icon'></i></a></td>
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
                        echo "<tr><td colspan='10'>No records found</td></tr>";
                    }
                    $conn->close();
                ?>
            </tbody>
        </table>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <!-- Previous Page -->
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                </li>

                <!-- Page Number -->
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <!-- Next Page -->
                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<?php if($siteConfig['enable_footer']): ?>
<footer class="footer text-center py-3">
    <h5>Created by <a href="https://steamcommunity.com/id/LeonKong" class="text-primary">LeonKong ❤️</a>.</h5>
</footer>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>