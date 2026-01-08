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
    $personal_query->execute([$uid]);
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
    <meta name="description" content="Admin Profile - Learnlike Training">
    <meta name="author" content="Learnlike">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/LL-logo-light.png" />

    <!-- TITLE -->
    <title>Admin Profile - Learnlike Training</title>

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

        body.app {
            background: var(--bg);
        }

        .page-container {
            padding: 30px 20px;
        }

        /* Profile Header Card */
        .profile-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 24px;
            display: flex;
            gap: 28px;
            align-items: flex-start;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

        .profile-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        /* Card Styles */
        .info-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
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

        /* Form Grid */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
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

        .form-value.empty {
            color: var(--muted);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .profile-card {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .page-container {
                padding: 20px 15px;
            }

            .profile-card {
                padding: 20px;
                gap: 16px;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
            }

            .profile-meta {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .edit-btn {
                align-self: flex-start;
                margin-top: 10px;
            }
        }

        /* Sidebar Toggle Styles */
        .app-sidebar {
            transition: all 0.3s ease;
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: white;
            box-shadow: 2px 0 8px rgba(0,0,0,0.15);
            z-index: 1000;
            overflow-y: auto;
        }

        .app-sidebar.show {
            left: 0;
            display: block;
        }

        .app-sidebar__overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }

        .app-sidebar__overlay.show {
            display: block;
        }

        @media (min-width: 1024px) {
            .app-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                width: 280px;
                height: 100vh;
                background: white;
                box-shadow: 2px 0 8px rgba(0,0,0,0.15);
                z-index: 999;
                display: block !important;
            }

            .app-sidebar__overlay {
                display: none !important;
            }
        }

        @media (max-width: 1023px) {
            .app-sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                width: 280px;
                height: 100vh;
                background: white;
                box-shadow: 2px 0 8px rgba(0,0,0,0.15);
                z-index: 1000;
                transition: left 0.3s ease;
                overflow-y: auto;
            }

            .app-sidebar.show {
                left: 0;
            }

            .app-sidebar__overlay.show {
                display: block;
                z-index: 999;
            }

            .page-main.sidebar-open {
                overflow: hidden;
            }
        }

        /* Modal Styles */
        .modal-content {
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .form-label {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            font-size: 14px;
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

        .btn-close:focus {
            box-shadow: 0 0 0 0.25rem rgba(107, 70, 193, 0.25);
        }
    </style>

</head>

<body class="app sidebar-mini ltr">

    <div class="page">
        <div class="page-main">

            <!-- App Header -->
            <div class="app-header header sticky">
                <div class="container-fluid main-container">
                    <div class="d-flex">
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript: void(0);"></a>
                        <a class="logo-horizontal " href="index.php">
                            <img src="../assets/images/brand/CV_Logo.png"  style="width:180px;height:50px;" class="header-brand-img desktop-logo" alt="logo">
                            <img src="../assets/images/brand/CV_Logo.png"  style="width:180px;height:50px;" class="header-brand-img light-logo1" alt="logo">
                        </a>
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
                                                <img src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-user" class="avatar profile-user brround cover-image">
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                <div class="drop-heading">
                                                    <div class="text-center">
                                                        <h5 class="text-dark mb-0 d-block"><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h5>
                                                        <small class="text-muted">Admin User</small>
                                                    </div>
                                                </div>
                                                <a class="dropdown-item" href="javascript: void(0);">
                                                    <svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>Profile
                                                </a>
                                                <a class="dropdown-item" href="../landing.php">
                                                    <svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>Landing Page
                                                </a>
                                                <a class="dropdown-item" href="../logout.php">
                                                    <svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>Logout
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

            <!-- Sidebar -->
            <?php include_once("includes/sidebar.php"); ?>

            <!-- Content Area -->
            <div class="app-content main-content mt-0">
                <div class="side-app">
                    <div class="page-container">

                        <!-- Breadcrumb -->
                        <div style="font-size:13px; margin-bottom:20px;">
                            <a href="index.php" style="color:#6b46c1; text-decoration:none;">Admin</a>
                            <span style="color:#9ca3af;"> » </span>
                            <span style="color:#374151;">Profile</span>
                        </div>

                        <!-- Page Title -->
                        <h1 style="font-size:28px; font-weight:700; margin-bottom:24px; color:#1f2937;">Admin Profile</h1>

                        <!-- Profile Header Card -->
                        <div class="profile-card">
                            <div class="profile-avatar">
                                <img src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-avatar">
                            </div>
                            <div class="profile-info">
                                <h2 class="profile-name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                                <div class="profile-role">Admin</div>
                                <div class="profile-subtitle"><?php echo htmlspecialchars($email); ?></div>
                                
                                <div class="profile-meta">
                                    <div class="form-group">
                                        <div class="form-label">User ID</div>
                                        <div class="form-value"><?php echo $uid; ?></div>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-label">Phone Number</div>
                                        <div class="form-value"><?php echo htmlspecialchars($phone ?? '—'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information Card -->
                        <div class="info-card">
                            <div class="card-header">
                                <h3 class="card-title">Personal Information</h3>
                                <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit All</button>
                            </div>
                            <div class="form-grid">
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
                                    <div class="form-label">User Role</div>
                                    <div class="form-value">Admin</div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">Member Since</div>
                                    <div class="form-value"><?php echo $created ?? '—'; ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Card -->
                        <div class="info-card">
                            <div class="card-header">
                                <h3 class="card-title">Address Information</h3>
                                <button class="edit-btn" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit All</button>
                            </div>
                            <div class="form-grid">
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
                                    <div class="form-label">Occupation</div>
                                    <div class="form-value"><?php echo htmlspecialchars($occupation ?: '—'); ?></div>
                                </div>
                                <div class="form-group">
                                    <div class="form-label">College/University</div>
                                    <div class="form-value"><?php echo htmlspecialchars($college_name ?: '—'); ?></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background:#6b46c1; color:#fff; border:none;">
                    <h5 class="modal-title" id="editProfileLabel">Edit Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding:24px;">
                    <form id="editProfileForm">
                        <!-- Basic Information Section -->
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

                        <!-- Address Information Section -->
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

                        <!-- Professional Information Section -->
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

    <!-- JQUERY JS -->
    <script src="../assets/js/jquery.min.js"></script>

    <!-- BOOTSTRAP JS -->
    <script src="../assets/plugins/bootstrap/js/popper.min.js"></script>
    <script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>

    <!-- CUSTOM JS -->
    <script src="../assets/js/custom.js"></script>

    <!-- Save Profile Changes Script -->
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

    <!-- Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.querySelector('.app-sidebar__toggle');
            const sidebar = document.querySelector('.app-sidebar');
            const overlay = document.querySelector('.app-sidebar__overlay');
            const pageMain = document.querySelector('.page-main');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (sidebar) {
                        sidebar.classList.toggle('show');
                    }
                    if (overlay) {
                        overlay.classList.toggle('show');
                    }
                    if (pageMain) {
                        pageMain.classList.toggle('sidebar-open');
                    }
                });
            }
            
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (sidebar) {
                        sidebar.classList.remove('show');
                    }
                    this.classList.remove('show');
                    if (pageMain) {
                        pageMain.classList.remove('sidebar-open');
                    }
                });
            }
        });
    </script>

</body>

</html>
