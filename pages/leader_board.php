<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");

$account = new Account($con);

if (!isset($_GET['id']) || !preg_match('/^[a-zA-Z0-9]+$/', $_GET['id'])) {
    die("Invalid tool ID");
}

$tool_id = $_GET['id']; 
$quizDetails = $account->getExamDetails($tool_id);

if (!$quizDetails) {
    die("Quiz not found");
}

$quiz_id = $quizDetails['quiz_id'];

if (!isset($_SESSION['username'])) {
    die("User not logged in");
}

$user_id = $_SESSION['username'];

// Pagination settings
$limit = 50; // Number of records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch leaderboard and total records count efficiently
$leaderboardData = $account->getLeaderboard($quiz_id);
$totalRecords = count($leaderboardData);
$totalPages = ceil($totalRecords / $limit);
$leaderboard = array_slice($leaderboardData, $offset, $limit); // Paginate manually
$userRank = $account->getUserRank($quiz_id, $user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

<!-- PAGE-HEADER -->
<div class="page-header">
  <div>
    <h1 class="page-title">Leaderboard</h1>
  </div>
</div>
<!-- PAGE-HEADER END -->

<!-- Row -->
<div class="row row-sm">
  <div class="col-lg-12">
    <div class="card custom-card">
      <div class="card-header border-bottom">
        <h3 class="card-title">Top Performers</h3>
      </div>
      <div class="card-body">
        <?php if (!empty($leaderboard)) { ?>
        <div class="table-responsive">
          <table class="table border text-nowrap text-md-nowrap table-bordered">
            <thead>
              <tr>
                <th>Rank</th>
                <th>Username</th>
                <th>Score</th>
                <th>Total Questions</th>
                <th>Percentage</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $rank = $offset + 1;
              foreach ($leaderboard as $row) { 
                  $userDetails = $account->getUserDetails($row['username']);
                  
                  // Medal Icons for Rank Column
                  $medal = "";
                  if ($rank == 1) {
                      $medal = "<i class='fas fa-medal' style='color: gold;'></i>";
                  } elseif ($rank == 2) {
                      $medal = "<i class='fas fa-medal' style='color: silver;'></i>";
                  } elseif ($rank == 3) {
                      $medal = "<i class='fas fa-medal' style='color: #cd7f32;'></i>";
                  }
              ?>
              <tr>
                <td><?php echo $medal . ' ' . $rank; ?></td>      
                <td><?php echo htmlspecialchars($userDetails['firstName'] . ' ' . $userDetails['lastName']); ?></td>
                <td><?php echo htmlspecialchars($row['score']); ?></td>
                <td><?php echo htmlspecialchars($row['total_questions']); ?></td>
                <td><?php echo htmlspecialchars($row['percentage']) . '%'; ?></td>
                <td><span class="badge bg-success">Passed</span></td>
                <td><?php echo date("d-m-Y H:i", strtotime($row['created_at'])); ?></td>
              </tr>
              <?php 
              $rank++;
              } ?>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <nav>
          <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
              <!--<li class="page-item <?php if ($page == $i) echo 'active'; ?>">
                <a class="page-link" href="leaderboard.php?id=<?php echo $tool_id; ?>&&ui=<?php echo $_SESSION['username'];?>&&page=<?php echo $i; ?>"><?php echo $i; ?></a>
              </li>-->
            <?php } ?>
          </ul>
        </nav>

        <!-- User Rank -->
        <div class="user-rank mt-4 text-center">
          <h5>Your Rank: 
            <span>
              <?php 
              if ($userRank == 1) {
                  echo "<i class='fas fa-medal' style='color: gold;'></i> $userRank";
              } elseif ($userRank == 2) {
                  echo "<i class='fas fa-medal' style='color: silver;'></i> $userRank";
              } elseif ($userRank == 3) {
                  echo "<i class='fas fa-medal' style='color: #cd7f32;'></i> $userRank";
              } else {
                  echo $userRank ? $userRank : 'Not Ranked';
              }
              ?>
            </span>
          </h5>
        </div>
        
        <?php } else { ?>
        <p style="text-align:center;font-weight:bold;">No passed attempts found</p>
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<!-- End Row -->

</body>
</html>
