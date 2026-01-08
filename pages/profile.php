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
    $avatarDetails = $account->avatarFetch($avatarID);
    if($avatarDetails && isset($avatarDetails['filePath'])) {
        $filePath = $avatarDetails['filePath'];
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Profile - Learnlike Training</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

    <!-- STYLE CSS -->
    <link href="../assets/css/style.css" rel="stylesheet" />
    <link href="../assets/css/skin-modes.css" rel="stylesheet" />

    <!--- FONT-ICONS CSS -->
    <link href="../assets/css/icons.css" rel="stylesheet" />

    <style>
        :root {
            --purple: #6b46c1;
            --purple-light: #f3e8ff;
            --bg: #f5f5f7;
            --white: #ffffff;
            --text: #374151;
            --muted: #9ca3af;
            --border: #e5e7eb;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .page {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* Header */
        .header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            gap: 20px;
        }

        .logo {
            font-weight: 700;
            color: var(--purple);
            font-size: 24px;
            min-width: 180px;
            text-decoration: none;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-left: auto;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        /* Main Layout */
        .page-main {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Sidebar */
        .app-sidebar {
            width: 280px;
            background: var(--white);
            border-right: 1px solid var(--border);
            overflow-y: auto;
            padding: 20px 0;
            position: fixed;
            height: calc(100vh - 60px);
            left: 0;
            top: 60px;
            z-index: 999;
            transition: transform 0.3s ease;
        }

        .app-sidebar.hidden {
            transform: translateX(-100%);
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px 12px;
            color: var(--text);
            font-size: 20px;
        }

        .hamburger.show {
            display: block;
        }

        .sidebar-section {
            padding: 16px 0;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--muted);
            padding: 0 16px;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            color: var(--text);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .sidebar-item:hover {
            background: var(--purple-light);
            color: var(--purple);
        }

        /* Content Area */
        .app-content {
            flex: 1;
            margin-left: 280px;
            overflow-y: auto;
            background: var(--bg);
        }

        .content {
            padding: 32px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .breadcrumb a {
            color: var(--purple);
            text-decoration: none;
            cursor: pointer;
        }

        .breadcrumb .sep {
            color: var(--muted);
        }

        /* Page Title */
        .page-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #1f2937;
        }

        /* Card */
        .card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Profile Header Card */
        .profile-card {
            display: flex;
            gap: 24px;
            align-items: flex-start;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            font-size: 22px;
            font-weight: 700;
            color: #1f2937;
        }

        .profile-role {
            color: var(--purple);
            font-weight: 600;
            margin-top: 4px;
            display: inline-block;
            background: var(--purple-light);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
        }

        .profile-subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-top: 8px;
        }

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 12px;
            color: var(--muted);
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

        /* Section Header */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .edit-btn {
            background: var(--white);
            border: 1px solid var(--border);
            color: var(--purple);
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
        }

        .edit-btn:hover {
            background: var(--purple-light);
        }

        .edit-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Modal Styles */
        .modal-content {
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .form-control {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(107, 70, 193, 0.1);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .app-sidebar {
                display: block;
            }

            .app-content {
                margin-left: 0;
            }

            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .profile-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .hamburger.show {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .content {
                padding: 20px;
            }

            .profile-card {
                padding: 0;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .header {
                padding: 12px 16px;
            }

            .logo {
                font-size: 18px;
                min-width: 120px;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <button class="hamburger" id="sidebarToggle" title="Toggle Sidebar">
                <i class="ri-menu-line"></i>
            </button>
            <a class="logo" href="<?php echo $isAdmin ? '../admin/index.php' : 'my_courses.php'; ?>">
                <img src="../assets/images/brand/CV_Logo.png" style="width:140px;height:40px;" alt="logo">
            </a>
            <div class="header-right">
                <div class="user-profile">
                    <div class="user-avatar">
                        <img src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-avatar" onerror="this.src='../assets/images/faces/6.jpg'">
                    </div>
                    <div>
                        <div class="user-name"><?php echo htmlspecialchars($firstName); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Layout -->
        <div class="page-main">
            <!-- Sidebar -->
            <div class="app-sidebar hidden" id="appSidebar">
                <?php if($isAdmin): ?>
                    <?php include_once("../admin/includes/sidebar.php"); ?>
                <?php else: ?>
                    <!-- User Sidebar Menu -->
                    <div class="sidebar-section">
                        <div class="sidebar-label">Menu</div>
                        <a class="sidebar-item" href="my_courses.php">
                            <i class="ri-book-line" style="margin-right:12px;"></i> My Courses
                        </a>
                        <a class="sidebar-item" href="profile.php">
                            <i class="ri-user-line" style="margin-right:12px;"></i> My Profile
                        </a>
                        <a class="sidebar-item" href="tools.php">
                            <i class="ri-tools-line" style="margin-right:12px;"></i> Tools
                        </a>
                    </div>

                    <div class="sidebar-section">
                        <div class="sidebar-label">Account</div>
                        <a class="sidebar-item" href="logout.php">
                            <i class="ri-logout-box-line" style="margin-right:12px;"></i> Logout
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Content Area -->
            <div class="app-content">
                <div class="content">
                    <!-- Breadcrumb -->
                    <div class="breadcrumb">
                        <a href="<?php echo $isAdmin ? '../admin/index.php' : 'my_courses.php'; ?>">
                            <?php echo $isAdmin ? 'Dashboard' : 'Courses'; ?>
                        </a>
                        <span class="sep">»</span>
                        <span>Profile</span>
                    </div>

                    <!-- Page Title -->
                    <h1 class="page-title">
                        <?php echo $isOwnProfile ? 'My Profile' : htmlspecialchars($firstName . ' ' . $lastName); ?>
                    </h1>

                    <!-- Profile Header Card -->
                    <div class="card">
                        <div class="profile-card">
                            <div class="profile-avatar">
                                <img src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-avatar" onerror="this.src='../assets/images/faces/6.jpg'">
                            </div>
                            <div class="profile-info">
                                <h2 class="profile-name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                                <div class="profile-role"><?php echo $isAdmin ? 'Admin' : 'User'; ?></div>
                                <div class="profile-subtitle"><?php echo htmlspecialchars($email); ?></div>
                                
                                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
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
                    </div>

                    <!-- Personal Information Card -->
                    <div class="card">
                        <div class="section-header">
                            <h3 class="section-title">Personal Information</h3>
                            <?php if($isOwnProfile): ?>
                            <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit</button>
                            <?php endif; ?>
                        </div>
                        <div class="form-grid">
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
    </div>

    <!-- Edit Profile Modal (only for own profile) -->
    <?php if($isOwnProfile): ?>
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background:#6b46c1; color:#fff; border:none;">
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

                        <hr style="border-color:#e5e7eb;">

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

                        <hr style="border-color:#e5e7eb;">

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
                <div class="modal-footer" style="border-top:1px solid #e5e7eb;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn" style="background:#6b46c1; color:#fff; border:none;" onclick="saveProfileChanges()">Save Changes</button>
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

    <script>
        // Sidebar Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('appSidebar');
            const contentArea = document.querySelector('.app-content');

            if(toggleBtn && sidebar) {
                toggleBtn.classList.add('show');
                
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                });

                // Close sidebar when clicking on a link
                sidebar.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function() {
                        if(window.innerWidth < 1024) {
                            sidebar.classList.add('hidden');
                        }
                    });
                });

                // Responsive behavior
                window.addEventListener('resize', function() {
                    if(window.innerWidth >= 1024) {
                        sidebar.classList.remove('hidden');
                        toggleBtn.style.display = 'none';
                    } else {
                        toggleBtn.style.display = 'block';
                        sidebar.classList.add('hidden');
                    }
                });

                // Initial state
                if(window.innerWidth >= 1024) {
                    sidebar.classList.remove('hidden');
                    toggleBtn.style.display = 'none';
                } else {
                    sidebar.classList.add('hidden');
                    toggleBtn.style.display = 'block';
                }
            }
        });
    </script>

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
