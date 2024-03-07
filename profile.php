<?php
include 'inc/config.php';
include 'inc/include.php';
require ('inc/steamauth/steamauth.php');  

if (!isset($_SESSION['steamid'])) {
    header('Location: index.php');
}

if (isset($_SESSION['steamid'])) {
    require ('inc/steamauth/userInfo.php');

    // Convert SteamID64 to SteamID
    $normalSteamId = convertSteamID64ToSteamID($steamprofile['steamid']);

    // Get user stats
    $userStatsQuery = $conn->prepare("SELECT * FROM lvl_base WHERE steam = ?");
    $userStatsQuery->bind_param("s", $normalSteamId);
    $userStatsQuery->execute();
    $userStatsResult = $userStatsQuery->get_result();
    if ($userStatsResult->num_rows > 0) {
        $userStats = $userStatsResult->fetch_assoc();
    } else {
        $userStats = array('points' => 0, 'kills' => 0, 'deaths' => 0, 'kd_ratio' => 0, 'win_matches' => 0);
    }

    // Calculate the posittion of the player based on points
    $positionQuery = $conn->prepare("SELECT COUNT(*) + 1 AS position FROM lvl_base WHERE value > (SELECT value FROM lvl_base WHERE steam = ?)");
    $positionQuery->bind_param("s", $normalSteamId);
    $positionQuery->execute();
    $positionResult = $positionQuery->get_result();
    $position = $positionResult->fetch_assoc()['position'];
}

$kdRatio = calculateKDRatio($userStats['kills'], $userStats['deaths']);
$wlRatio = calculateWLRatio($userStats['round_win'], $userStats['round_lose']);
$lastConnectDate = date("d-m-Y", $userStats['lastconnect']);
$playtimeFormatted = formatPlaytime($userStats['playtime']);
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteConfig['title']) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/54ff0c56ce.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="inc/style.css">
</head>
<body>
<div class="container-fluid mt-5 col-10 px-5 pt-5 pb-5 rounded shadow-lg">
    
    <!-- Header Section -->
    <div class="text-center mb-5">
        <h1 class="title"><?= htmlspecialchars($siteConfig['main_title_1']) ?> <span class="text-secondary"><?= htmlspecialchars($siteConfig['main_title_2']) ?></span></h1>
        
        <!-- Search and Links -->
        <?php if ($siteConfig['enable_search']): ?>
        <div class="row mt-3">
            <div class="col-12">
                <div action="" method="get" class="form-inline justify-content-center">
                    <button id="theme-toggle" class="btn btn-secondary mode"></button>
                    <!-- Login/Logout Buttons -->
                    <?php if(!isset($_SESSION['steamid'])): ?>
                        <a href="?login" class="d-flex align-items-center justify-content-center bg-dark ml-2 col-3 rounded login mode btn btn-secondary">
                            <i class="fa-brands fa-steam"></i> &nbsp;Login via Steam
                        </a>
                    <?php else: ?>
                        <a href="index.php" class="d-flex align-items-center justify-content-center bg-dark ml-2 col-1 rounded logout mode btn btn-secondary">
                            Home
                        </a>
                        <a href="?logout" class="d-flex align-items-center justify-content-center bg-dark ml-2 col-1 rounded logout mode btn btn-secondary">
                            Logout
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Rank Image above the Center Card -->
    <div class="row justify-content-center mb-3">
        <div class="col-md-4 text-center">
            <img src='<?= getRankImage($userStats["rank"])?>' alt='Rank Image' style='width: 200px; height: 80px;'>
        </div>
    </div>

    <!-- Cards Section -->
    <div class="row align-items-stretch">
        <!-- Left Card -->
        <div class="col-md-4 d-flex">
            <div class="card w-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title text-center">K/D Ratio</h5>
                    <div class="align-self-center">
                        <canvas id="kdRatioChart"></canvas>
                    </div>
                    <h5 class="card-title text-center mt-3">W/L Ratio</h5>
                    <div class="align-self-center">
                    <canvas id="wlRatioChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Profile Card (Center) -->
        <div class="col-md-4 d-flex">
            <div class="profile-statistics card w-100 rounded">
            <img src="<?= $steamprofile['avatarfull'] ?>" class="card-img-top" alt="Profielfoto">
                <div class="card-body">
                    <h5 class="card-title"><?= $steamprofile['personaname'] ?><img style="border-radius:2%; margin-left: 2%" src="<?php echo "https://flagsapi.com/" . $steamprofile['loccountrycode'] . "/shiny/64.png" ?>"></h5>
                    <p class="card-text">Position: <span class="font-weight-bold">#<?= $position ?></span></p>
                </div>
                <ul class="list-group list-group-flush black">
                    <li class="list-group-item">Points: <span class="font-weight-bold"><?= $userStats['value'] ?></span></li>
                    <li class="list-group-item">Kills: <span class="font-weight-bold"><?= $userStats['kills'] ?></span></li>
                    <li class="list-group-item">Deaths: <span class="font-weight-bold"><?= $userStats['deaths'] ?></span></li>
                    <li class="list-group-item">K/D Ratio: <span class="font-weight-bold"><?= $kdRatio ?></span></li>
                    <li class="list-group-item">Win/Lose Ratio: <span class="font-weight-bold"><?= $wlRatio ?></span></li>
                    <li class="list-group-item">Playtime: <span class="font-weight-bold"><?= $playtimeFormatted ?></span></li>
                    <li class="list-group-item">Last connection: <span class="font-weight-bold"><?= $lastConnectDate ?></span></li>
                </ul>
                <div class="card-body profile">
                    <a href="<?= $steamprofile['profileurl'] ?>" class="card-link">Steam Profile</a>
                </div>
            </div>
        </div>

        <!-- Right Card -->
        <div class="col-md-4 d-flex right-card">
            <div class="card w-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h5 class="card-title text-center">Your playtime</h5>
                    <div class="align-self-center">
                        <canvas id="playtimeChart" style="height: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($siteConfig['enable_footer']): ?>
<div class="d-flex justify-content-center mt-3">
    <footer class="text-center py-3 col-2 rounded col-4 col-sm-4 col-md-4 col-lg-4">
        <h5 class="footer">Created by <a href="https://steamcommunity.com/id/LeonKong" class="text-primary">LeonKong ❤️</a></h5>
    </footer>
</div>
<?php endif; ?>
<script>
// K/D Ratio Chart
var ctxKD = document.getElementById('kdRatioChart').getContext('2d');
var kdRatioChart = new Chart(ctxKD, {
    type: 'pie',
    data: {
        labels: ['Kills', 'Deaths'],
        datasets: [{
            data: [<?= $userStats['kills'] ?>, <?= $userStats['deaths'] ?>],
            backgroundColor: ['rgba(54, 162, 235, 0.6)', 'rgba(255, 99, 132, 0.6)']
        }]
    }
});

// Win/Lose Ratio Chart
var ctxWL = document.getElementById('wlRatioChart').getContext('2d');
var wlRatioChart = new Chart(ctxWL, {
    type: 'pie',
    data: {
        labels: ['Wins', 'Losses'],
        datasets: [{
            data: [<?= $userStats['round_win'] ?>, <?= $userStats['round_lose'] ?>],
            backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 206, 86, 0.6)']
        }]
    }
});

// Convert UNIX time to hours (or your preferred unit)
var playtimeHours = <?= $userStats['playtime'] ?> / 3600; // Assuming playtime is in seconds
var ctxPlaytime = document.getElementById('playtimeChart').getContext('2d');
var playtimeChart = new Chart(ctxPlaytime, {
    type: 'bar',
    data: {
        labels: ['Play Time'],
        datasets: [{
            label: 'Hours Played',
            data: [playtimeHours],
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'Healthy Max Amount',
            data: [30],
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
});
</script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<script src="inc/index.js"></script>
</body>
</html>