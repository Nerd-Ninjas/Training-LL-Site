<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");

// ensure admin is logged in
if(!isset($_SESSION["username"]) || $_SESSION["userType"] != 1) {
    header("Location: ../login.php");
    exit;
}

$account = new Account($con);
$username = $_SESSION["username"];
$userDetails = $account->getUserDetails($username);
if(!$userDetails) {
    echo "User not found";
    exit;
}

$firstName = $userDetails['firstName'] ?? '';
$lastName = $userDetails['lastName'] ?? '';
$email = $userDetails['email'] ?? '';
$phone = $userDetails['mobileNumber'] ?? '';
$uid = $userDetails['id'] ?? '';
$created = isset($userDetails['createdDate']) ? date('d M, Y', strtotime($userDetails['createdDate'])) : '';
$avatarID = $userDetails['avatarID'] ?? 0;
$avatarDetails = $account->avatarFetch($avatarID);
$filePath = $avatarDetails && isset($avatarDetails['filePath']) ? $avatarDetails['filePath'] : '../assets/images/avatars/default.png';
?>
<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Learnlike's Training Dashboard">
    <meta name="author" content="Learnlike">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/LL-logo-light.png" />

    <!-- TITLE -->
    <title>Profile - Admin</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="../assets/css/style.css" rel="stylesheet" />
    <link href="../assets/css/skin-modes.css" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="../assets/css/icons.css" rel="stylesheet" />

    <style>
        /* compact override to use colorful card from profile.html */
        :root{--bg-start:#fff1f0; --bg-end:#f0f9ff; --card-start:#ffffff; --card-end:#f8fbff; --muted:#58616b; --accent-1:#ff6b6b; --accent-2:#6b8bff; --accent-3:#8b5cff}
        body.app { background: linear-gradient(180deg,var(--bg-start),var(--bg-end)); }
        .profile-card{background:linear-gradient(180deg,var(--card-start),var(--card-end));border-radius:16px;box-shadow:0 20px 50px rgba(15,23,42,.12);display:flex;gap:28px;padding:28px;align-items:center}
        .profile-avatar{width:188px;flex:0 0 188px;display:flex;align-items:center;justify-content:center;border-radius:16px;background:linear-gradient(135deg,var(--accent-1),var(--accent-2));padding:10px}
        .profile-avatar img{width:156px;height:156px;border-radius:50%;object-fit:cover;border:6px solid #fff;box-shadow:0 8px 30px rgba(16,24,40,.12)}
        h1.profile-name{margin:0;font-size:26px}
        .meta-badge{display:inline-block;margin-left:6px;padding:6px 10px;background:linear-gradient(90deg,var(--accent-2),var(--accent-3));color:#fff;border-radius:999px;font-size:13px;font-weight:600}
        .field{background:linear-gradient(180deg,#ffffff,#fbfbff);padding:12px;border-radius:10px;border:1px solid rgba(139,92,255,0.06)}
        .label{font-size:12px;color:var(--muted);display:block}
        .value{font-weight:700;margin-top:6px;color:var(--accent-2)}
        .bio{margin-top:16px;padding:14px;background:linear-gradient(180deg,#fff6fb,#f7fbff);border-radius:10px;border:1px solid rgba(107,139,255,0.06);color:#24303f}
    </style>

</head>

<body class="app sidebar-mini ltr">

    <div class="page">
        <div class="page-main">

            <!-- include header and sidebar from existing layout -->
            <?php include_once("includes/sidebar.php"); ?>

            <div class="app-content main-content mt-0">
                <div class="side-app">
                    <div class="main-container container-fluid">

                        <div class="page-header">
                            <div class="row align-items-center">
                                <div class="col-12">
                                    <div class="page-header-title">
                                        <i class="icon icon-user me-2"></i>
                                        <div class="d-inline-block">
                                            <h3>Profile</h3>
                                            <span class="text-muted">Manage your account details</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <!-- Header card (white with purple accent) -->
                                <div class="card mb-4 shadow-sm" style="border-radius:12px;background:#fff;border:1px solid rgba(107,70,193,0.06)">
                                    <div class="card-body d-flex align-items-center gap-4 p-4">
                                        <div style="flex:0 0 auto;">
                                            <div style="width:96px;height:96px;border-radius:50%;overflow:hidden;border:6px solid #fff;box-shadow:0 6px 18px rgba(16,24,40,.06)">
                                                <img src="<?php echo htmlspecialchars($filePath); ?>" alt="avatar" style="width:100%;height:100%;object-fit:cover;display:block">
                                            </div>
                                        </div>
                                        <div style="flex:1;">
                                            <h4 class="mb-1" style="font-weight:700;color:#231f20"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?> <small class="ms-2" style="display:inline-block;background:#6b46c1;color:#fff;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:600">Admin</small></h4>
                                            <div class="text-muted" style="margin-top:6px"><?php echo htmlspecialchars($username); ?> • <?php echo htmlspecialchars($email); ?></div>
                                            <div class="text-muted mt-2" style="font-size:13px"><?php echo htmlspecialchars($phone); ?><?php if($created) echo ' • Joined '.$created; ?></div>
                                        </div>
                                        <div style="flex:0 0 auto;text-align:right">
                                            <a href="#" class="btn btn-outline-primary" style="background:#6b46c1;border-color:#6b46c1;color:#fff">Edit</a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Personal Information card -->
                                <div class="card mb-3" style="border-radius:12px;background:#fff;border:1px solid rgba(107,70,193,0.06)">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 style="margin:0;color:#231f20">Personal Information</h5>
                                            <a href="#" class="btn" style="background:#fff;border:1px solid rgba(107,70,193,0.12);color:#6b46c1;padding:6px 12px;border-radius:8px">Edit</a>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="small text-muted">First Name</div>
                                                <div style="font-weight:600;color:#111"><?php echo htmlspecialchars($firstName); ?></div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="small text-muted">Last Name</div>
                                                <div style="font-weight:600;color:#111"><?php echo htmlspecialchars($lastName); ?></div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="small text-muted">Date of Birth</div>
                                                <div style="font-weight:600;color:#111">—</div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="small text-muted">Email Address</div>
                                                <div style="font-weight:600;color:#111"><?php echo htmlspecialchars($email); ?></div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="small text-muted">Phone Number</div>
                                                <div style="font-weight:600;color:#111"><?php echo htmlspecialchars($phone); ?></div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="small text-muted">User Role</div>
                                                <div style="font-weight:600;color:#111">Admin</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Address card -->
                                <div class="card mb-5" style="border-radius:12px;background:#fff;border:1px solid rgba(107,70,193,0.06)">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 style="margin:0;color:#231f20">Address</h5>
                                            <a href="#" class="btn" style="background:#fff;border:1px solid rgba(107,70,193,0.12);color:#6b46c1;padding:6px 12px;border-radius:8px">Edit</a>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="small text-muted">Country</div>
                                                <div style="font-weight:600;color:#111">—</div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="small text-muted">City</div>
                                                <div style="font-weight:600;color:#111">—</div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="small text-muted">Postal Code</div>
                                                <div style="font-weight:600;color:#111">—</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>

</body>
</html>
