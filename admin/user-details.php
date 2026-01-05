<?php
require_once("../includes/config.php");
require_once("../includes/classes/FormSanitizer.php");
require_once("../includes/classes/Constants.php");
require_once("../includes/classes/Account.php");

// Check if user is logged in and is admin
if(!isset($_SESSION["username"]) || empty($_SESSION["username"]) || !isset($_SESSION["userType"]) || $_SESSION["userType"] != 1) {
    header("Location: ../login.php");
    exit;
}

// Get user ID from URL parameter
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: user_management.php");
    exit;
}

$user_id = intval($_GET['id']);

// Fetch user details
try {
    $user_query = $con->prepare("SELECT id, firstName, lastName, email, username, mobileNumber, gender, dob FROM users WHERE id = ?");
    $user_query->execute([$user_id]);
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    
    if(!$user) {
        header("Location: user_management.php?error=User not found");
        exit;
    }
} catch(Exception $e) {
    header("Location: user_management.php?error=Database error");
    exit;
}

// Fetch user personal info
try {
    $personal_query = $con->prepare("SELECT * FROM user_personal_info WHERE user_id = ?");
    $personal_query->execute([$user_id]);
    $personal_info = $personal_query->fetch(PDO::FETCH_ASSOC);
    
    // If no personal info exists, create empty array with default values
    if(!$personal_info) {
        $personal_info = array(
            'id' => null,
            'user_id' => $user_id,
            'address' => '',
            'city' => '',
            'state' => '',
            'country' => '',
            'postal_code' => '',
            'occupation' => '',
            'college_name' => '',
            'phone_verified' => 0,
            'email_verified' => 0,
            'profile_completed' => 0,
            'type' => 0
        );
    }
} catch(Exception $e) {
    $personal_info = array(
        'id' => null,
        'user_id' => $user_id,
        'address' => '',
        'city' => '',
        'state' => '',
        'country' => '',
        'postal_code' => '',
        'occupation' => '',
        'college_name' => '',
        'phone_verified' => 0,
        'email_verified' => 0,
        'profile_completed' => 0,
        'type' => 0
    );
}

// Handle form submission
$success_message = '';
$error_message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_personal_info'])) {
    try {
        $address = FormSanitizer::sanitizeFormString($_POST['address'] ?? '');
        $city = FormSanitizer::sanitizeFormString($_POST['city'] ?? '');
        $state = FormSanitizer::sanitizeFormString($_POST['state'] ?? '');
        $country = FormSanitizer::sanitizeFormString($_POST['country'] ?? '');
        $postal_code = FormSanitizer::sanitizeFormString($_POST['postal_code'] ?? '');
        $occupation = FormSanitizer::sanitizeFormString($_POST['occupation'] ?? '');
        $college_name = FormSanitizer::sanitizeFormString($_POST['college_name'] ?? '');
        $phone_verified = isset($_POST['phone_verified']) ? 1 : 0;
        $email_verified = isset($_POST['email_verified']) ? 1 : 0;
        $profile_completed = isset($_POST['profile_completed']) ? 1 : 0;
        $type = isset($_POST['type']) ? intval($_POST['type']) : 0;
        
        // Check if record exists
        if($personal_info['id'] === null || $personal_info['id'] == '') {
            // Insert new record
            $insert = $con->prepare("INSERT INTO user_personal_info (user_id, address, city, state, country, postal_code, occupation, college_name, phone_verified, email_verified, profile_completed, type) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if($insert->execute([$user_id, $address, $city, $state, $country, $postal_code, $occupation, $college_name, $phone_verified, $email_verified, $profile_completed, $type])) {
                $success_message = "Personal information added successfully!";
                // Refresh personal info
                $personal_info = array(
                    'id' => $con->lastInsertId(),
                    'user_id' => $user_id,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'country' => $country,
                    'postal_code' => $postal_code,
                    'occupation' => $occupation,
                    'college_name' => $college_name,
                    'phone_verified' => $phone_verified,
                    'email_verified' => $email_verified,
                    'profile_completed' => $profile_completed,
                    'type' => $type
                );
            } else {
                $error_message = "Failed to add personal information";
            }
        } else {
            // Update existing record
            $update = $con->prepare("UPDATE user_personal_info SET address=?, city=?, state=?, country=?, postal_code=?, occupation=?, college_name=?, phone_verified=?, email_verified=?, profile_completed=?, type=? WHERE user_id=?");
            if($update->execute([$address, $city, $state, $country, $postal_code, $occupation, $college_name, $phone_verified, $email_verified, $profile_completed, $type, $user_id])) {
                $success_message = "Personal information updated successfully!";
                // Update local array
                $personal_info['address'] = $address;
                $personal_info['city'] = $city;
                $personal_info['state'] = $state;
                $personal_info['country'] = $country;
                $personal_info['postal_code'] = $postal_code;
                $personal_info['occupation'] = $occupation;
                $personal_info['college_name'] = $college_name;
                $personal_info['phone_verified'] = $phone_verified;
                $personal_info['email_verified'] = $email_verified;
                $personal_info['profile_completed'] = $profile_completed;
                $personal_info['type'] = $type;
            } else {
                $error_message = "Failed to update personal information";
            }
        }
        
        // Handle programme assignment if selected
        if(!empty($_POST['programme_id'])) {
            $programme_id = intval($_POST['programme_id']);
            
            // Check if already assigned
            $check = $con->prepare("SELECT id FROM programme_assign WHERE user_id = ? AND programme_id = ?");
            $check->execute([$user_id, $programme_id]);
            
            if($check->rowCount() == 0) {
                // Get the admin user ID from session username
                $admin_query = $con->prepare("SELECT id FROM users WHERE username = ?");
                $admin_query->execute([$_SESSION['username']]);
                $admin_result = $admin_query->fetch(PDO::FETCH_ASSOC);
                $assigned_by = $admin_result ? intval($admin_result['id']) : NULL;
                
                // Assign programme
                $assign = $con->prepare("INSERT INTO programme_assign (user_id, programme_id, assigned_by) VALUES (?, ?, ?)");
                if($assign->execute([$user_id, $programme_id, $assigned_by])) {
                    if(strpos($success_message, 'updated') !== false || strpos($success_message, 'added') !== false) {
                        $success_message = str_replace('successfully!', 'and programme assigned successfully!', $success_message);
                    } else {
                        $success_message = "Programme assigned successfully!";
                    }
                } else {
                    $error_message = "Personal information saved but programme assignment failed!";
                }
            } else {
                $error_message = "Programme is already assigned to this user!";
            }
        }
    } catch(Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle programme assignment
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_programme'])) {
    try {
        $programme_id = intval($_POST['programme_id'] ?? 0);
        $assigned_by = $_SESSION['username'];
        
        if($programme_id <= 0) {
            $error_message = "Please select a valid programme";
        } else {
            // Check if programme already assigned
            $check = $con->prepare("SELECT id FROM programme_assign WHERE user_id = ? AND programme_id = ?");
            $check->execute([$user_id, $programme_id]);
            
            if($check->rowCount() > 0) {
                $error_message = "This programme is already assigned to the user";
            } else {
                // Assign programme
                $assign = $con->prepare("INSERT INTO programme_assign (user_id, programme_id, assigned_by) VALUES (?, ?, ?)");
                if($assign->execute([$user_id, $programme_id, $assigned_by])) {
                    $success_message = "Programme assigned successfully!";
                } else {
                    $error_message = "Failed to assign programme";
                }
            }
        }
    } catch(Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Handle programme removal
if(isset($_GET['remove_programme']) && !empty($_GET['remove_programme'])) {
    try {
        $programme_id = intval($_GET['remove_programme']);
        $remove = $con->prepare("DELETE FROM programme_assign WHERE user_id = ? AND programme_id = ?");
        if($remove->execute([$user_id, $programme_id])) {
            $success_message = "Programme assignment removed!";
        } else {
            $error_message = "Failed to remove programme assignment";
        }
    } catch(Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Fetch all programmes
$programmes = array();
try {
    $prog_query = $con->prepare("SELECT id, programme_id, programme_name FROM programmes_master WHERE approved = 1 ORDER BY programme_name");
    $prog_query->execute();
    $programmes = $prog_query->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $programmes = array();
}

// Fetch assigned programmes for this user
$assigned_programmes = array();
try {
    $assigned_query = $con->prepare("
        SELECT pa.id, pa.programme_id, pm.programme_id as prog_code, pm.programme_name, pa.assigned_date
        FROM programme_assign pa
        JOIN programmes_master pm ON pa.programme_id = pm.id
        WHERE pa.user_id = ?
        ORDER BY pa.assigned_date DESC
    ");
    $assigned_query->execute([$user_id]);
    $assigned_programmes = $assigned_query->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $assigned_programmes = array();
}

$account = new Account($con);
$username = $_SESSION["username"];

// Get admin details for header
try {
    $userDetails = $account->getUserDetails($username);
    $firstName = $userDetails['firstName'];
    $lastName = $userDetails['lastName'];
    $email_admin = $userDetails['email'];
    $avatarID = $userDetails['avatarID'];
    $avatarDetails = $account->avatarFetch($avatarID);
    $filePath = $avatarDetails['filePath'];
} catch(Exception $e) {
    header("Location: ../login.php");
    exit;
}
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>User Details - Learnlike's Training</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/LL-logo-light.png"/>
    <link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/style.css" rel="stylesheet" />
    <style>
        .page-container {
            padding: 20px;
        }
        .details-card {
            border: 1px solid #e7eef7;
            border-radius: 8px;
            padding: 30px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .details-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f2f5;
        }
        .user-badge {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-badge-img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        .user-info h4 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        .user-info p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        .info-display {
            margin-bottom: 30px;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e7eef7;
        }
        .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 20px;
        }
        .info-row.full {
            grid-template-columns: 1fr;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .info-value {
            font-size: 16px;
            color: #333;
            padding: 10px 0;
        }
        .info-value.empty {
            color: #999;
            font-style: italic;
        }
        .info-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            width: fit-content;
        }
        .info-badge.verified {
            background: #d4edda;
            color: #155724;
        }
        .info-badge.unverified {
            background: #f8f9fa;
            color: #666;
        }
        .form-section {
            margin-bottom: 30px;
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .form-section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e7eef7;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-row.full {
            grid-template-columns: 1fr;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            border: 1px solid #d5dce0;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
        }
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f2f5;
        }
        .action-buttons.view-mode {
            display: flex;
        }
        .action-buttons.edit-mode {
            display: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #f0f2f5;
            color: #333;
            padding: 10px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover {
            background: #e1e4e8;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.2s ease;
        }
        .back-link:hover {
            color: #764ba2;
            transform: translateX(-3px);
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .info-row {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
            }
            .details-header {
                flex-direction: column;
                text-align: center;
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
                        <a class="logo-horizontal " href="index.php">
                            <img src="../assets/images/brand/CV_Logo.png"  style="width:180px;height:50px;" class="header-brand-img desktop-logo" alt="logo">
                            <img src="../assets/images/brand/CV_Logo.png"  style="width:180px;height:50px;" class="header-brand-img light-logo1" alt="logo">
                        </a>
                        <!-- LOGO -->
                        <div class="main-header-center ms-3 d-none d-xl-block">
                            <input class="form-control" placeholder="Search for results..." type="search">
                            <button class="btn">
                                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24"><path  d="M21.2529297,17.6464844l-2.8994141-2.8994141c-0.0021973-0.0021973-0.0043945-0.0043945-0.0065918-0.0065918c-0.8752441-0.8721313-2.2249146-0.9760132-3.2143555-0.3148804l-0.8467407-0.8467407c1.0981445-1.2668457,1.7143555-2.887146,1.715332-4.5747681c0.0021973-3.8643799-3.1286621-6.9989014-6.993042-7.0011597S2.0092773,5.1315308,2.007019,8.9959106S5.1356201,15.994812,9,15.9970703c1.6889038,0.0029907,3.3114014-0.6120605,4.5789185-1.7111206l0.84729,0.84729c-0.6630859,0.9924316-0.5566406,2.3459473,0.3208618,3.2202759l2.8994141,2.8994141c0.4780884,0.4786987,1.1271973,0.7471313,1.8037109,0.7460938c0.6766357,0.0001831,1.3256226-0.2686768,1.803894-0.7472534C22.2493286,20.2558594,22.2488403,18.6417236,21.2529297,17.6464844z M9.0084229,14.9970703c-3.3120728,0.0023193-5.9989624-2.6807861-6.0012817-5.9928589S5.6879272,3.005249,9,3.0029297c1.5910034-0.0026855,3.1175537,0.628479,4.2421875,1.7539062c1.1252441,1.1238403,1.7579956,2.6486206,1.7590942,4.2389526C15.0036011,12.3078613,12.3204956,14.994751,9.0084229,14.9970703z M20.5458984,20.5413818c-0.604126,0.6066284-1.5856934,0.6087036-2.1923828,0.0045166l-2.8994141-2.8994141c-0.2913818-0.2910156-0.4549561-0.6861572-0.4544678-1.0979614C15.0006714,15.6928101,15.6951294,15,16.5507812,15.0009766c0.4109497-0.0005493,0.8051758,0.1624756,1.0957031,0.453125l2.8994141,2.8994141C21.1482544,18.9584351,21.1482544,19.9364624,20.5458984,20.5413818z"/></svg>
                            </button>
                        </div>
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
                                                <img src="<?php echo $filePath; ?>" alt="profile-user" class="avatar profile-user brround cover-image">
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
                                                <a class="dropdown-item" href="javascript: void(0);">
                                                    <svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.64l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.49.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.22-.07.5.12.64l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.64l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.49-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.5-.12-.64l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>Settings
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
            <!-- /app-Header -->

            <?php include_once("includes/sidebar.php"); ?>

            <!-- APP-CONTENT -->
            <div class="app-content main-content mt-0">
                <div class="side-app">
                    <div class="page-container">
                        <a href="user_management.php" class="back-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            Back to Users
                        </a>

                        <div class="details-card">
                            <div class="details-header">
                                <div class="user-badge">
                                    <div class="user-info">
                                        <h4><?php echo htmlspecialchars($user['firstName'] . " " . $user['lastName']); ?></h4>
                                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                                        <p>Username: <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <p style="color: #999; margin: 0;">User ID: <strong><?php echo $user['id']; ?></strong></p>
                                </div>
                            </div>

                            <?php if($success_message): ?>
                                <div class="alert alert-success">
                                    <strong>✓</strong> <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>

                            <?php if($error_message): ?>
                                <div class="alert alert-danger">
                                    <strong>✗</strong> <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>

                            <!-- VIEW MODE -->
                            <div id="viewMode" class="info-display">
                                <!-- Contact Information -->
                                <div class="info-section">
                                    <div class="info-section-title">Contact Information</div>
                                    <div class="info-row full">
                                        <div class="info-item">
                                            <div class="info-label">Address</div>
                                            <div class="info-value <?php echo empty($personal_info['address']) ? 'empty' : ''; ?>">
                                                <?php echo !empty($personal_info['address']) ? htmlspecialchars($personal_info['address']) : 'Not provided'; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-item">
                                            <div class="info-label">City</div>
                                            <div class="info-value <?php echo empty($personal_info['city']) ? 'empty' : ''; ?>">
                                                <?php echo !empty($personal_info['city']) ? htmlspecialchars($personal_info['city']) : 'Not provided'; ?>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">State</div>
                                            <div class="info-value <?php echo empty($personal_info['state']) ? 'empty' : ''; ?>">
                                                <?php echo !empty($personal_info['state']) ? htmlspecialchars($personal_info['state']) : 'Not provided'; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-item">
                                            <div class="info-label">Country</div>
                                            <div class="info-value <?php echo empty($personal_info['country']) ? 'empty' : ''; ?>">
                                                <?php echo !empty($personal_info['country']) ? htmlspecialchars($personal_info['country']) : 'Not provided'; ?>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Postal Code</div>
                                            <div class="info-value <?php echo empty($personal_info['postal_code']) ? 'empty' : ''; ?>">
                                                <?php echo !empty($personal_info['postal_code']) ? htmlspecialchars($personal_info['postal_code']) : 'Not provided'; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Professional Information -->
                                <div class="info-section">
                                    <div class="info-section-title">Professional Information</div>
                                    <div class="info-row">
                                        <div class="info-item">
                                            <div class="info-label">Occupation</div>
                                            <div class="info-value <?php echo empty($personal_info['occupation']) ? 'empty' : ''; ?>">
                                                <?php echo !empty($personal_info['occupation']) ? htmlspecialchars($personal_info['occupation']) : 'Not provided'; ?>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">College/Institution Name</div>
                                            <div class="info-value <?php echo empty($personal_info['college_name']) ? 'empty' : ''; ?>">
                                                <?php echo !empty($personal_info['college_name']) ? htmlspecialchars($personal_info['college_name']) : 'Not provided'; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Verification Status -->
                                <div class="info-section">
                                    <div class="info-section-title">Verification & Status</div>
                                    <div class="info-row">
                                        <div class="info-item">
                                            <div class="info-label">Phone Verification</div>
                                            <div class="info-value">
                                                <?php if($personal_info['phone_verified']): ?>
                                                    <span class="info-badge verified">✓ Verified</span>
                                                <?php else: ?>
                                                    <span class="info-badge unverified">✗ Not Verified</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">Email Verification</div>
                                            <div class="info-value">
                                                <?php if($personal_info['email_verified']): ?>
                                                    <span class="info-badge verified">✓ Verified</span>
                                                <?php else: ?>
                                                    <span class="info-badge unverified">✗ Not Verified</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="info-item">
                                            <div class="info-label">Profile Status</div>
                                            <div class="info-value">
                                                <?php if($personal_info['profile_completed']): ?>
                                                    <span class="info-badge verified">✓ Completed</span>
                                                <?php else: ?>
                                                    <span class="info-badge unverified">✗ Incomplete</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-label">User Type</div>
                                            <div class="info-value">
                                                <?php 
                                                    $types = ['0' => 'Standard', '1' => 'Premium', '2' => 'VIP'];
                                                    echo htmlspecialchars($types[$personal_info['type']] ?? 'Standard');
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Programme Assignment -->
                                <div class="info-section">
                                    <div class="info-section-title">Assigned Programmes</div>
                                    <?php if(!empty($assigned_programmes)): ?>
                                        <div style="margin-bottom: 20px;">
                                            <div style="display: grid; gap: 12px;">
                                                <?php foreach($assigned_programmes as $prog): ?>
                                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; border-left: 4px solid #667eea;">
                                                        <div>
                                                            <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                                                                <?php echo htmlspecialchars($prog['programme_name']); ?>
                                                            </div>
                                                            <div style="font-size: 12px; color: #999;">
                                                                Code: <?php echo htmlspecialchars($prog['prog_code']); ?> | Assigned: <?php echo date('d M Y', strtotime($prog['assigned_date'])); ?>
                                                            </div>
                                                        </div>
                                                        <a href="?id=<?php echo $user_id; ?>&remove_programme=<?php echo $prog['programme_id']; ?>" 
                                                           onclick="return confirm('Remove this programme assignment?')" 
                                                           style="color: #dc3545; text-decoration: none; font-weight: 600; font-size: 14px;">Remove</a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div style="color: #999; font-style: italic; padding: 15px 0;">
                                            No programmes assigned yet.
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Action Buttons - View Mode -->
                                <div class="action-buttons view-mode" id="viewModeButtons">
                                    <button type="button" class="btn-primary" onclick="toggleEditMode()">Edit Information</button>
                                </div>
                            </div>

                            <!-- EDIT MODE FORM -->
                            <form method="POST" action="" id="editForm" style="display: none;">
                                <input type="hidden" name="update_personal_info" value="1">

                                <!-- Contact Information -->
                                <div class="form-section active">
                                    <div class="form-section-title">Contact Information</div>
                                    <div class="form-row full">
                                        <div class="form-group">
                                            <label for="address">Address</label>
                                            <textarea id="address" name="address" placeholder="Enter street address"><?php echo htmlspecialchars($personal_info['address'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="city">City</label>
                                            <input type="text" id="city" name="city" placeholder="Enter city" value="<?php echo htmlspecialchars($personal_info['city'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="state">State</label>
                                            <input type="text" id="state" name="state" placeholder="Enter state" value="<?php echo htmlspecialchars($personal_info['state'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="country">Country</label>
                                            <input type="text" id="country" name="country" placeholder="Enter country" value="<?php echo htmlspecialchars($personal_info['country'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="postal_code">Postal Code</label>
                                            <input type="text" id="postal_code" name="postal_code" placeholder="Enter postal code" value="<?php echo htmlspecialchars($personal_info['postal_code'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Professional Information -->
                                <div class="form-section active">
                                    <div class="form-section-title">Professional Information</div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="occupation">Occupation</label>
                                            <input type="text" id="occupation" name="occupation" placeholder="Enter occupation" value="<?php echo htmlspecialchars($personal_info['occupation'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label for="college_name">College/Institution Name</label>
                                            <input type="text" id="college_name" name="college_name" placeholder="Enter college/institution name" value="<?php echo htmlspecialchars($personal_info['college_name'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Verification Status -->
                                <div class="form-section active">
                                    <div class="form-section-title">Verification & Status</div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="phone_verified" name="phone_verified" <?php echo $personal_info['phone_verified'] ? 'checked' : ''; ?>>
                                                <label for="phone_verified">Phone Verified</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="email_verified" name="email_verified" <?php echo $personal_info['email_verified'] ? 'checked' : ''; ?>>
                                                <label for="email_verified">Email Verified</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <div class="checkbox-group">
                                                <input type="checkbox" id="profile_completed" name="profile_completed" <?php echo $personal_info['profile_completed'] ? 'checked' : ''; ?>>
                                                <label for="profile_completed">Profile Completed</label>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="type">User Type</label>
                                            <select id="type" name="type">
                                                <option value="0" <?php echo $personal_info['type'] == 0 ? 'selected' : ''; ?>>Standard</option>
                                                <option value="1" <?php echo $personal_info['type'] == 1 ? 'selected' : ''; ?>>Premium</option>
                                                <option value="2" <?php echo $personal_info['type'] == 2 ? 'selected' : ''; ?>>VIP</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Programme Assignment -->
                                <div class="form-section active">
                                    <div class="form-section-title">Assign Programme</div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="programme_id">Select Programme</label>
                                            <select id="programme_id" name="programme_id">
                                                <option value="">-- Select a programme --</option>
                                                <?php foreach($programmes as $prog): ?>
                                                    <?php 
                                                    // Check if already assigned
                                                    $is_assigned = false;
                                                    foreach($assigned_programmes as $assigned) {
                                                        if($assigned['programme_id'] == $prog['id']) {
                                                            $is_assigned = true;
                                                            break;
                                                        }
                                                    }
                                                    if(!$is_assigned):
                                                    ?>
                                                        <option value="<?php echo $prog['id']; ?>">
                                                            <?php echo htmlspecialchars($prog['programme_name'] . ' (' . $prog['programme_id'] . ')'); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons - Edit Mode -->
                                <div class="action-buttons edit-mode" id="editModeButtons" style="display: none;">
                                    <button type="submit" class="btn-primary">Save Changes</button>
                                    <button type="button" class="btn-secondary" onclick="toggleEditMode()">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /APP-CONTENT -->
        </div>
    </div>

    <!-- JQUERY JS -->
    <script src="../assets/js/jquery.min.js"></script>
    <!-- BOOTSTRAP JS -->
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Perfect SCROLLBAR JS-->
    <script src="../assets/plugins/p-scroll/perfect-scrollbar.js"></script>
    <!-- STICKY JS -->
    <script src="../assets/js/sticky.js"></script>
    <!-- COLOR THEME JS -->
    <script src="../assets/js/themeColors.js"></script>
    <!-- CUSTOM JS -->
    <script src="../assets/js/custom.js"></script>
    <!-- SWITCHER JS -->
    <script src="../assets/switcher/js/switcher.js"></script>
    <!-- SIDE-MENU JS-->
	<script src="../assets/plugins/sidemenu/sidemenu.js"></script>
    <script>
        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editForm = document.getElementById('editForm');
            const viewModeButtons = document.getElementById('viewModeButtons');
            const editModeButtons = document.getElementById('editModeButtons');
            
            // Toggle display
            if (viewMode.style.display === 'none') {
                // Switch to view mode
                viewMode.style.display = 'block';
                editForm.style.display = 'none';
                viewModeButtons.style.display = 'flex';
                editModeButtons.style.display = 'none';
            } else {
                // Switch to edit mode
                viewMode.style.display = 'none';
                editForm.style.display = 'block';
                viewModeButtons.style.display = 'none';
                editModeButtons.style.display = 'flex';
            }
        }
    </script>
</body>
</html>

