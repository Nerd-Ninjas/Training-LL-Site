<?php
require_once("includes/config.php");
require_once("includes/classes/Account.php");

// Check if user is logged in
if(!$_SESSION["username"]) {
    header("Location: login.php");
    exit;
}

$account = new Account($con);
$username = $_SESSION["username"];
$userDetails = $account->getUserDetails($username);

$firstName = $userDetails['firstName'] ?? 'User';
$lastName = $userDetails['lastName'] ?? '';
$avatarID = $userDetails['avatarID'] ?? 0;
$filePath = 'assets/images/faces/6.jpg'; // default avatar

// Get avatar from avatar table
if($avatarID > 0) {
    $query = $con->prepare("SELECT filePath FROM avatar WHERE id = :avatarID");
    $query->bindValue(":avatarID", $avatarID);
    $query->execute();
    if($query->rowCount() == 1) {
        $avatarRow = $query->fetch(PDO::FETCH_ASSOC);
        $filePath = $avatarRow['filePath'] ?? 'assets/images/faces/6.jpg';
    }
}
?>

<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Awaiting Programme Assignment - Learnlike's Training</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/brand/LL-logo-light.png"/>
    <link href="assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link href="assets/css/icons.css" rel="stylesheet" />    <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.11/dist/dotlottie-wc.js" type="module"></script>    <styl>
    <style>   
    @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .logo-container {
            animation: fadeInDown 0.8s ease-out;
        }

        .icon-container {
            animation: fadeInUp 0.8s ease-out 0.3s both;
        }

        .icon-inner {
            animation: pulse 2s ease-in-out infinite;
        }

        .card-main {
            animation: fadeInUp 0.8s ease-out 0.5s both;
        }

        .text-title {
            animation: fadeInUp 0.8s ease-out 0.7s both;
        }

        .text-subtitle {
            animation: fadeInUp 0.8s ease-out 0.8s both;
        }

        .message-box {
            animation: slideInLeft 0.8s ease-out 0.9s both;
        }

        .user-info-box {
            animation: slideInRight 0.8s ease-out 1s both;
        }

        .btn-group {
            animation: fadeInUp 0.8s ease-out 1.1s both;
        }

        .btn-primary {
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-outline-secondary:hover {
            transform: translateY(-3px);
        }

        .footer-text {
            animation: fadeInUp 0.8s ease-out 1.2s both;
        }
    </style>
</head>

<body class="ltr bg-light">
    <div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <div class="w-100" style="max-width: 500px;">
            <!-- Logo Container -->
            <div class="text-center mb-5 logo-container">
                <div class="d-flex align-items-center justify-content-center gap-3 mb-4">
                    <!-- Cyber Vault Logo -->
                    <div class="logo-item">
                        <img src="assets/images/brand/full-logo-light.png" alt="learnlike logo" style="height: 50px; object-fit: contain;">
                    </div>
                    <!-- Learnlike Logo -->
                    <div style="font-size: 24px; color: #ddd;">|</div>
                    <div class="logo-item">
                        <img src="assets/images/brand/CV_Logo.png" alt="Cyber valut Logo" style="height: 50px; object-fit: contain;">
                    </div>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card border-0 shadow card-main">
                <div class="card-body p-5 text-center">
                    
                    <!-- Animated Icon -->
                    <div class="mb-4 icon-container d-flex justify-content-center">
                        <dotlottie-wc src="https://lottie.host/471272ec-a48e-4d8c-85e8-b2c4e0b85949/EsDFBicivO.lottie" style="width: 150px; height: 150px;" autoplay loop></dotlottie-wc>
                    </div>

                    <!-- Title -->
                    <h2 class="fw-bold text-dark mb-2 text-title">Pending Assignment</h2>
                    
                    <!-- Subtitle -->
                    <p class="text-muted mb-4 text-subtitle" style="font-size: 15px; line-height: 1.6;">
                        You haven't been assigned to any training programme yet.
                    </p>

                    <!-- Message Box -->
                    <div class="bg-light p-4 rounded-3 mb-4 text-start message-box" style="border-left: 4px solid #667eea;">
                        <p class="mb-0 text-dark" style="font-size: 14px; line-height: 1.6;">
                            Your administrator will assign you to a suitable training programme soon. Once assigned, you'll be able to access course materials and start learning. Please check back later or contact your administrator for updates.
                        </p>
                    </div>

                    <!-- User Info -->
                    <div class="d-flex align-items-center justify-content-center mb-4 p-3 rounded-2 user-info-box" style="background: #f8f9fa;">
                        <img src="<?php echo htmlspecialchars($filePath); ?>" alt="Profile" class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
                        <div class="text-start">
                            <h6 class="mb-0 fw-bold text-dark"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h6>
                            <small class="text-muted">@<?php echo htmlspecialchars($username); ?></small>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 btn-group">
                        <a href="profile.php" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="me-2" style="width: 18px; height: 18px;" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><path fill="white" d="M12,12c2.21,0,4-1.79,4-4c0-2.21-1.79-4-4-4c-2.21,0-4,1.79-4,4C8,10.21,9.79,12,12,12z M12,14c-2.67,0-8,1.34-8,4v2h16v-2C20,15.34,14.67,14,12,14z"/></svg>
                            View Your Profile
                        </a>
                        <a href="logout.php" class="btn btn-outline-secondary btn-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="me-2" style="width: 18px; height: 18px;" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><path fill="currentColor" d="M17,7l-1.41,1.41L18.17,11H8v2h10.17l-2.58,2.58L17,17l5-5L17,7z M4,5h8V3H4C2.9,3,2,3.9,2,5v14c0,1.1,0.9,2,2,2h8v-2H4V5z"/></svg>
                            Logout
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4 footer-text">
                <p class="text-muted small">Learnlike's Training Portal</p>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
