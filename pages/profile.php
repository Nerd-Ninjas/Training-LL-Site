<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");

// Check if user is logged in
if(!isset($_SESSION["username"]) || empty($_SESSION["username"])) {
    header("Location: ../login.php");
    exit;
}

// Get the user ID to display (can be current user or passed via parameter)
$user_id_to_display = null;
$isOwnProfile = false;
$isAdmin = isset($_SESSION["userType"]) && $_SESSION["userType"] == 1;

// Get current user's ID first
$account = new Account($con);
$username = $_SESSION["username"];
$userDetails = $account->getUserDetails($username);

if(!$userDetails) {
    echo "User not found";
    exit;
}

$current_user_id = $userDetails['id'] ?? 0;

// Check if viewing someone else's profile (admin only)
if(isset($_GET['id']) && !empty($_GET['id'])) {
    $requested_id = intval($_GET['id']);
    
    // Only admins can view other profiles
    if($isAdmin && $requested_id != $current_user_id) {
        // Fetch the requested user's details
        try {
            $view_query = $con->prepare("SELECT id, firstName, lastName, email, username, mobileNumber, avatarID, createdDate FROM users WHERE id = ?");
            $view_query->execute([$requested_id]);
            $view_user = $view_query->fetch(PDO::FETCH_ASSOC);
            
            if($view_user) {
                $user_id_to_display = $requested_id;
                $userDetails = $view_user;
                $isOwnProfile = false;
            } else {
                // User not found
                header("Location: my_courses.php?error=User not found");
                exit;
            }
        } catch(Exception $e) {
            header("Location: my_courses.php?error=Database error");
            exit;
        }
    } else {
        // Regular users can only view their own profile
        $user_id_to_display = $current_user_id;
        $isOwnProfile = true;
    }
} else {
    // Display current user's profile
    $user_id_to_display = $current_user_id;
    $isOwnProfile = true;
}

// Extract user details
$firstName = $userDetails['firstName'] ?? '';
$lastName = $userDetails['lastName'] ?? '';
$email = $userDetails['email'] ?? '';
$phone = $userDetails['mobileNumber'] ?? '';
$uid = $userDetails['id'] ?? '';
$created = isset($userDetails['createdDate']) ? date('d M, Y', strtotime($userDetails['createdDate'])) : '';
$avatarID = $userDetails['avatarID'] ?? 0;

// Get avatar with null check
$filePath = '../assets/images/faces/6.jpg';
if($avatarID > 0) {
    try {
        $avatarDetails = $account->avatarFetch($avatarID);
        if($avatarDetails && isset($avatarDetails['filePath'])) {
            $filePath = $avatarDetails['filePath'];
        }
    } catch(Exception $e) {
        $filePath = '../assets/images/faces/6.jpg';
    }
}

// Fetch user personal info
$address = '';
$city = '';
$state = '';
$country = '';
$postal_code = '';
$occupation = '';
$college_name = '';
$phone_verified = 0;
$email_verified = 0;

try {
    $personal_query = $con->prepare("SELECT * FROM user_personal_info WHERE user_id = ?");
    $personal_query->execute([$user_id_to_display]);
    $personal_info = $personal_query->fetch(PDO::FETCH_ASSOC);
    
    if($personal_info) {
        $address = $personal_info['address'] ?? '';
        $city = $personal_info['city'] ?? '';
        $state = $personal_info['state'] ?? '';
        $country = $personal_info['country'] ?? '';
        $postal_code = $personal_info['postal_code'] ?? '';
        $occupation = $personal_info['occupation'] ?? '';
        $college_name = $personal_info['college_name'] ?? '';
        $phone_verified = $personal_info['phone_verified'] ?? 0;
        $email_verified = $personal_info['email_verified'] ?? 0;
    }
} catch(Exception $e) {
    // Personal info not found, use empty values
}
?>
<!doctype html>
<html lang="en" dir="ltr">

<head>
    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Profile - Learnlike Training">
    <meta name="author" content="Learnlike">
    <meta name="keywords" content="admin, dashboard, training, courses">
    <meta property="og:url" content="https://training.learnlike.in" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Profile - Learnlike Training" />
    <meta property="og:description" content="" />
    <meta property="og:image" content="https://learnlike.in/assets/themes/pan/img/LL-logo-light-new.png" />

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/favicon.ico"/>

    <!-- TITLE -->
    <title>My Profile - Learnlike Training</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="../assets/css/style.css" rel="stylesheet" />
    <link href="../assets/css/skin-modes.css" rel="stylesheet" />

    <!-- FONT-ICONS CSS -->
    <link href="../assets/css/icons.css" rel="stylesheet" />

    <!-- INTERNAL Switcher css -->
    <link href="../assets/switcher/css/switcher.css" rel="stylesheet" />
    <link href="../assets/switcher/demo.css" rel="stylesheet" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f7f9fa;
        }

        .app-content {
            background: #f7f9fa;
        }

        /* MAIN CONTAINER */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0;
        }

        /* PAGE HEADER */
        .page-header-section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            margin-top: 10px;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin: 0 0 6px 0;
        }

        .page-subtitle {
            font-size: 0.9rem;
            color: #999;
            margin: 0;
        }

        /* PROFILE CARD */
        .profile-card {
            border: 1px solid #e7eef7;
            border-radius: 12px;
            padding: 30px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        .profile-avatar-container {
            flex-shrink: 0;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            flex: 1;
        }

        .profile-name {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .profile-role {
            color: #6b46c1;
            font-weight: 600;
            background: #f3e8ff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .profile-email {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }

        /* FORM GRID */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 0;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .form-value {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
        }

        /* INFO SECTION */
        .info-section {
            margin-bottom: 30px;
            border: 1px solid #e7eef7;
            border-radius: 12px;
            padding: 30px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .info-section:last-child {
            margin-bottom: 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .edit-btn {
            background: white;
            border: 1px solid #e7eef7;
            color: #6b46c1;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
        }

        .edit-btn:hover {
            background: #f3e8ff;
            border-color: #6b46c1;
        }

        .edit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* 3-COLUMN GRID FOR INFO */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        /* Modal Styles */
        .modal-content {
            border: 1px solid #e7eef7;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: #6b46c1;
            color: white;
            border: none;
        }

        .form-control {
            border: 1px solid #e7eef7;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #6b46c1;
            box-shadow: 0 0 0 3px rgba(107, 70, 193, 0.1);
        }

        .btn-save {
            background: #6b46c1;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-save:hover {
            background: #5a3ba1;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .profile-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .profile-card {
                padding: 20px;
                gap: 20px;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.2rem;
            }

            .info-section {
                padding: 20px;
            }
        }
    </style>
</head>

<body class="app sidebar-mini ltr">
    <div class="page">
        <div class="page-main">
            <!-- app-Header -->
            <div class="app-header header sticky">
                <div class="container-fluid main-container">
                    <div class="d-flex">
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript: void(0);"></a>
                        <!-- sidebar-toggle-->
                        <a class="logo-horizontal" href="<?php echo $isAdmin ? '../admin/index.php' : '../landing.php'; ?>">
                            <img src="../assets/images/brand/CV_Logo.png" style="width:180px;height:50px;" class="header-brand-img desktop-logo" alt="logo">
                            <img src="../assets/images/brand/CV_Logo.png" style="width:180px;height:50px;" class="header-brand-img light-logo1" alt="logo">
                        </a>
                        <!-- LOGO -->
                        <div class="d-flex order-lg-2 ms-auto header-right-icons">
                            <button class="navbar-toggler navresponsive-toggler d-md-none ms-auto" type="button"
                                data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent-4"
                                aria-controls="navbarSupportedContent-4" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon fe fe-more-vertical"></span>
                            </button>
                            <div class="navbar navbar-collapse responsive-navbar p-0">
                                <div class="collapse navbar-collapse" id="navbarSupportedContent-4">
                                    <div class="d-flex order-lg-2">
                                        <div class="dropdown d-flex profile-1">
                                            <a href="javascript: void(0);" data-bs-toggle="dropdown" class="nav-link leading-none d-flex">
                                                <img src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-user" class="avatar profile-user brround cover-image" onerror="this.src='../assets/images/faces/6.jpg'">
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                <div class="drop-heading">
                                                    <div class="text-center">
                                                        <h5 class="text-dark mb-0 d-block"><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h5>
                                                        <small class="text-muted"><?php echo $isAdmin ? 'Admin Account' : 'User Account'; ?></small>
                                                    </div>
                                                </div>
                                                <a class="dropdown-item" href="profile.php">
                                                    <svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>My Profile
                                                </a>
                                                <a class="dropdown-item" href="../landing.php">
                                                    <svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>Landing Page
                                                </a>
                                                <a class="dropdown-item" href="../logout.php">
                                                    <svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>Logout
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /app-Header -->

            <!-- SIDEBAR -->
            <?php 
            $current_page = basename($_SERVER['PHP_SELF']);
            ?>
            <div class="sticky">
                <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
                <div class="app-sidebar">
                    <div class="side-header">
                        <a class="header-brand1" href="<?php echo $isAdmin ? '../admin/index.php' : '../landing.php'; ?>">
                            <img src="../assets/images/brand/full-logo-dark.png" class="header-brand-img desktop-logo" alt="logo">
                            <img src="../assets/images/brand/LL-logo-light.png" class="header-brand-img toggle-logo" alt="logo">
                            <img src="../assets/images/brand/LL-logo-light.png" class="header-brand-img light-logo" alt="logo">
                            <img src="../assets/images/brand/full-logo-light.png" class="header-brand-img light-logo1" alt="logo">
                        </a>
                    </div>
                    <div class="main-sidemenu">
                        <?php if($isAdmin): ?>
                            <?php include_once("../admin/includes/sidebar.php"); ?>
                        <?php else: ?>
                            <ul class="side-menu">
                                <li>
                                    <h3>Menu</h3>
                                </li>
                                <li class="slide <?php echo ($current_page == 'my_courses.php') ? 'active' : ''; ?>">
                                    <a class="side-menu__item has-link" href="my_courses.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                            <path d="M4 6h16V4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4v2h8v-2h4c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 12V6h16v12H4z"/>
                                        </svg>
                                        <span class="side-menu__label">My Courses</span>
                                    </a>
                                </li>
                                <li class="slide <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                                    <a class="side-menu__item has-link" href="profile.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                        <span class="side-menu__label">My Profile</span>
                                    </a>
                                </li>
                                <li class="slide">
                                    <a class="side-menu__item has-link" href="../landing.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                                        </svg>
                                        <span class="side-menu__label">Landing Page</span>
                                    </a>
                                </li>
                                <li class="slide">
                                    <a class="side-menu__item has-link" href="../logout.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                            <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                                        </svg>
                                        <span class="side-menu__label">Logout</span>
                                    </a>
                                </li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- /SIDEBAR -->

            <!-- APP-CONTENT -->
            <div class="app-content main-content">
                <div class="side-app">
                    <div class="main-container container-fluid">
                        <div class="page-header-section">
                            <h1 class="page-title"><?php echo $isOwnProfile ? 'My Profile' : htmlspecialchars($firstName . ' ' . $lastName); ?></h1>
                        </div>

                        <!-- PROFILE CARD -->
                        <div class="profile-card">
                            <div class="profile-avatar-container">
                                <div class="profile-avatar">
                                    <img src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-avatar" onerror="this.src='../assets/images/faces/6.jpg'">
                                </div>
                            </div>
                            <div class="profile-info">
                                <h2 class="profile-name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                                <div class="profile-role"><?php echo $isAdmin ? 'Admin' : 'User'; ?></div>
                                <div class="profile-email"><?php echo htmlspecialchars($email); ?></div>
                                
                                <div class="form-grid">
                                    <div class="form-group">
                                        <div class="form-label">Email Address</div>
                                        <div class="form-value"><?php echo htmlspecialchars($email); ?></div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-label">Phone Number</div>
                                        <div class="form-value"><?php echo htmlspecialchars($phone ?? '—'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PERSONAL INFORMATION SECTION -->
                        <div class="info-section">
                            <div class="section-header">
                                <h3 class="section-title">Personal Information</h3>
                                <?php if($isOwnProfile): ?>
                                <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit</button>
                                <?php endif; ?>
                            </div>

                            <div class="info-grid">
                                <div class="form-group">
                                    <div class="form-label">User ID</div>
                                    <div class="form-value"><?php echo $uid; ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">First Name</div>
                                    <div class="form-value"><?php echo htmlspecialchars($firstName); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Last Name</div>
                                    <div class="form-value"><?php echo htmlspecialchars($lastName); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Email Address</div>
                                    <div class="form-value"><?php echo htmlspecialchars($email); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Phone Number</div>
                                    <div class="form-value"><?php echo htmlspecialchars($phone ?? '—'); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Member Since</div>
                                    <div class="form-value"><?php echo $created ?? '—'; ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Occupation</div>
                                    <div class="form-value"><?php echo htmlspecialchars($occupation ?: '—'); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">College/University</div>
                                    <div class="form-value"><?php echo htmlspecialchars($college_name ?: '—'); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Street Address</div>
                                    <div class="form-value"><?php echo htmlspecialchars($address ?: '—'); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">City</div>
                                    <div class="form-value"><?php echo htmlspecialchars($city ?: '—'); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">State</div>
                                    <div class="form-value"><?php echo htmlspecialchars($state ?: '—'); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Country</div>
                                    <div class="form-value"><?php echo htmlspecialchars($country ?: '—'); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Postal Code</div>
                                    <div class="form-value"><?php echo htmlspecialchars($postal_code ?: '—'); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Phone Verified</div>
                                    <div class="form-value"><?php echo $phone_verified ? 'Yes' : 'No'; ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Email Verified</div>
                                    <div class="form-value"><?php echo $email_verified ? 'Yes' : 'No'; ?></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <!-- /APP-CONTENT -->
        </div>
    </div>

    <!-- Edit Profile Modal (only for own profile) -->
    <?php if($isOwnProfile): ?>
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileLabel">Edit Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <form id="editProfileForm">
                        <div class="mb-3">
                            <h6 style="font-weight:700; color:#1f2937; margin-bottom:16px;">Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="mobileNumber" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="mobileNumber" name="mobileNumber" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <hr style="border-color:#e7eef7;">

                        <div class="mb-3">
                            <h6 style="font-weight:700; color:#1f2937; margin-bottom:16px;">Address Information</h6>
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">Street Address</label>
                                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($address ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($state ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Country</label>
                                    <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($country ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="postalCode" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="postalCode" name="postalCode" value="<?php echo htmlspecialchars($postal_code ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <hr style="border-color:#e7eef7;">

                        <div class="mb-3">
                            <h6 style="font-weight:700; color:#1f2937; margin-bottom:16px;">Professional Information</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="occupation" class="form-label">Occupation</label>
                                    <input type="text" class="form-control" id="occupation" name="occupation" value="<?php echo htmlspecialchars($occupation ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="collegeName" class="form-label">College/University</label>
                                    <input type="text" class="form-control" id="collegeName" name="collegeName" value="<?php echo htmlspecialchars($college_name ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-save" onclick="saveProfileChanges()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- JQUERY JS -->
    <script src="../assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="../assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- CUSTOM JS -->
    <script src="../assets/js/custom.js"></script>

    <?php if($isOwnProfile): ?>
    <script>
        function saveProfileChanges() {
            const form = document.getElementById('editProfileForm');
            
            if(!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            formData.append('action', 'update_profile');

            fetch('update_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Profile updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update profile'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating your profile');
            });
        }
    </script>
    <?php endif; ?>

</body>

</html>
