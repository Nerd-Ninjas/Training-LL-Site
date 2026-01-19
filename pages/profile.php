<?php
require_once("../includes/config.php");
require_once("../includes/head_main.php");
require_once("../includes/classes/FormSanitizer.php");
require_once("../includes/classes/Constants.php");
require_once("../includes/classes/Account.php");

// Check if user is logged in
if(!$_SESSION["username"]) {
    header("Location: ../login.php");
    exit;
}

$account = new Account($con);
$username = $_SESSION["username"];
$userDetails = $account->getUserDetails($username);

// Check if viewing own profile or another user
$view_uid = isset($_GET['uid']) ? $_GET['uid'] : $userDetails['id'];
$isOwnProfile = ($view_uid == $userDetails['id']);

if(!$isOwnProfile) {
    // Check if user exists
    $stmt = $con->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$view_uid]);
    $profileUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$profileUser) {
        header("Location: my_courses.php");
        exit;
    }
    $firstName = $profileUser['firstName'];
    $lastName = $profileUser['lastName'];
    $email = $profileUser['email'];
    $phone = $profileUser['phone'] ?? '';
    $avatarID = $profileUser['avatarID'];
    $created = $profileUser['created'] ?? date('Y-m-d');
} else {
    $firstName = $userDetails['firstName'];
    $lastName = $userDetails['lastName'];
    $email = $userDetails['email'];
    $phone = $userDetails['phone'] ?? '';
    $avatarID = $userDetails['avatarID'];
    $created = $userDetails['created'] ?? date('Y-m-d');
}

// Get avatar
$avatarDetails = $account->avatarFetch($avatarID);
$filePath = $avatarDetails['filePath'];

// Check if user is admin
$isAdmin = $userDetails['type'] == 1;

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Get user's current page for sidebar active state
$_SESSION['course_id'] = $_SESSION['course_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Learnlike's Training</title>
    
    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/LL-logo-light.png"/>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>

    <!-- STYLE CSS -->
    <link href="../assets/css/style.css" rel="stylesheet"/>
    <link href="../assets/css/skin-modes.css" rel="stylesheet"/>

    <!-- FONT-ICONS CSS -->
    <link href="../assets/css/icons.css" rel="stylesheet"/>

    <!-- INTERNAL Switcher css -->
    <link href="../assets/switcher/css/switcher.css" rel="stylesheet"/>
    <link href="../assets/switcher/demo.css" rel="stylesheet"/>

    <style>
        /* PROFILE PAGE CUSTOM STYLES */
        .page-header-section {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .profile-card {
            display: flex;
            gap: 30px;
            background: white;
            border: 1px solid #e7eef7;
            border-radius: 12px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .profile-avatar-container {
            display: flex;
            justify-content: center;
            flex-shrink: 0;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #6b46c1;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 10px 0;
        }

        .profile-role {
            font-weight: 600;
            color: #6b46c1;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 15px;
            background: #f3e8ff;
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
        }

        .modal-header {
            background: white;
            border-bottom: 1px solid #e7eef7;
            padding: 20px;
        }

        .modal-title {
            font-weight: 700;
            color: #1f2937;
            font-size: 18px;
        }

        .modal-body {
            padding: 30px;
        }

        .form-control, .form-select {
            border: 1px solid #e7eef7;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #6b46c1;
            box-shadow: 0 0 0 3px rgba(107, 70, 193, 0.1);
        }

        .btn-primary {
            background: #6b46c1;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #5a3ba1;
        }

        .btn-secondary {
            background: white;
            border: 1px solid #e7eef7;
            color: #6b7280;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .btn-save {
            background: #6b46c1;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
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

    <!-- PAGE -->
    <div class="page">
        <div class="page-main">

            <!-- app-Header -->
            <div class="app-header header sticky">
                <div class="container-fluid main-container">
                    <div class="d-flex">
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript: void(0);"></a>
                        <!-- sidebar-toggle-->
                        <a class="logo-horizontal" href="my_courses.php">
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
                                            <a href="javascript: void(0);" data-bs-toggle="dropdown"
                                                class="nav-link leading-none d-flex">
                                                <img src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-user"
                                                    class="avatar profile-user brround cover-image">
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                <div class="drop-heading">
                                                    <div class="text-center">
                                                        <h5 class="text-dark mb-0 d-block"><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h5>
                                                        <small class="text-muted"><?php echo $isAdmin ? 'Admin User' : 'User'; ?></small>
                                                    </div>
                                                </div>
                                                <a class="dropdown-item" href="profile.php">
                                                    <svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>Profile
                                                </a>
                                                <a class="dropdown-item" href="my_courses.php">
                                                    <svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>My Courses
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
            <div class="sticky">
                <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
                <div class="app-sidebar">
                    <div class="side-header">
                        <a class="header-brand1" href="my_courses.php">
                            <img src="../assets/images/brand/full-logo-dark.png" class="header-brand-img desktop-logo" alt="logo">
                            <img src="../assets/images/brand/LL-logo-light.png" class="header-brand-img toggle-logo" alt="logo">
                            <img src="../assets/images/brand/LL-logo-light.png" class="header-brand-img light-logo" alt="logo">
                            <img src="../assets/images/brand/full-logo-light.png" class="header-brand-img light-logo1" alt="logo">
                        </a>
                    </div>
                    <div class="main-sidemenu">
                        <ul class="side-menu">
                            <li>
                                <h3>Menu</h3>
                            </li>
                            <li class="slide active">
                                <a class="side-menu__item has-link" href="profile.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                    </svg>
                                    <span class="side-menu__label">My Profile</span>
                                </a>
                            </li>
                            <li class="slide">
                                <a class="side-menu__item has-link" href="my_courses.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
                                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                                    </svg>
                                    <span class="side-menu__label">My Courses</span>
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
                    </div>
                </div>
            </div>
            <!-- /SIDEBAR -->

            <!-- APP-CONTENT -->
            <div class="app-content main-content mt-0">
                <div class="side-app">
                    <div class="page-container">
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
                                    <div class="form-value"><?php echo htmlspecialchars($view_uid); ?></div>
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
                                    <div class="form-value"><?php echo htmlspecialchars($created ?? '—'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /APP-CONTENT -->
        </div>
    </div>
    <!-- /PAGE -->

    <!-- EDIT PROFILE MODAL -->
    <?php if($isOwnProfile): ?>
    <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileLabel">Edit Profile</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm">
                        <div class="form-group mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-save" type="button" onclick="saveProfileChanges()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center flex-row-reverse">
                <div class="col-md-12 col-sm-12 text-center">
                    <a href="https://learnlike.in" style="color:#6f42c1">Learnlike</a> © All rights reserved 2025 | 
                    <a href="#">Terms & Conditions</a> |
                    <a href="#">Privacy Policy</a>
                </div>
            </div>
        </div>
    </footer>
    <!-- /FOOTER -->

    <!-- JQUERY JS -->
    <script src="../assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="../assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- SIDE-MENU JS-->
    <script src="../assets/plugins/sidemenu/sidemenu.js"></script>

    <!-- PERFECT SCROLLBAR JS-->
    <script src="../assets/plugins/p-scroll/perfect-scrollbar.js"></script>
    <script src="../assets/plugins/p-scroll/pscroll.js"></script>

    <!-- STICKY JS -->
    <script src="../assets/js/sticky.js"></script>

    <!-- COLOR THEME JS -->
    <script src="../assets/js/themeColors.js"></script>

    <!-- CUSTOM JS -->
    <script src="../assets/js/custom.js"></script>

    <!-- SWITCHER JS -->
    <script src="../assets/switcher/js/switcher.js"></script>

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
