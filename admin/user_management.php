<?php
require_once("../includes/config.php"); 
require_once("../includes/classes/FormSanitizer.php");
require_once("../includes/classes/Constants.php");
require_once("../includes/classes/Account.php");

// Check if user is logged in and is admin (type = 1)
if(!isset($_SESSION["username"]) || empty($_SESSION["username"]) || !isset($_SESSION["userType"]) || $_SESSION["userType"] != 1) {
    header("Location: ../login.php");
    exit;
}

// Run data cleanup on first page load (checks for misaligned records from old bulk uploads)
if(!isset($_SESSION['data_cleanup_done'])) {
    $cleanup_query = $con->prepare("SELECT id FROM users 
        WHERE firstName REGEXP '^[a-zA-Z0-9]+[0-9]{2,}$' 
        AND lastName LIKE '%@%' 
        AND (email NOT LIKE '%@%' OR email = '')
        LIMIT 1");
    $cleanup_query->execute();
    if($cleanup_query->rowCount() > 0) {
        // Bad records found, fix them
        $fix_query = $con->prepare("SELECT id, firstName, lastName, username, email FROM users 
            WHERE firstName REGEXP '^[a-zA-Z0-9]+[0-9]{2,}$' 
            AND lastName LIKE '%@%' 
            AND (email NOT LIKE '%@%' OR email = '')
            LIMIT 100");
        $fix_query->execute();
        $bad_records = $fix_query->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($bad_records as $record) {
            $correct_firstName = $record['username'];
            $correct_lastName = explode('@', $record['lastName'])[0];
            $correct_username = $record['firstName'];
            $correct_email = $record['lastName'];
            
            $update = $con->prepare("UPDATE users SET firstName=?, lastName=?, username=?, email=? WHERE id=?");
            $update->execute([$correct_firstName, $correct_lastName, $correct_username, $correct_email, $record['id']]);
        }
    }
    $_SESSION['data_cleanup_done'] = true;
}

$account = new Account($con);
$username = $_SESSION["username"];

// Get user details with error handling
try {
    $userDetails = $account->getUserDetails($username);
    if(!$userDetails) {
        header("Location: ../login.php");
        exit;
    }
    $firstName = $userDetails['firstName'];
    $lastName = $userDetails['lastName'];
    $email = $userDetails['email'];
    $avatarID = $userDetails['avatarID'];

    $avatarDetails = $account->avatarFetch($avatarID);
    $filePath = $avatarDetails['filePath'];
} catch(Exception $e) {
    header("Location: ../login.php");
    exit;
}

// Fetch all users with error handling
try {
    $query = $con->prepare("SELECT * FROM users ORDER BY createdDate DESC LIMIT 1000");
    $query->execute();
    $users = $query->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $users = array();
}

// Handle Delete Request
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $delete_query = $con->prepare("DELETE FROM users WHERE id = ?");
    if($delete_query->execute([$user_id])) {
        header("Location: user_management.php?success=User deleted successfully");
        exit;
    }
}

// Handle Edit User from Modal Form
$edit_user_error = '';
$edit_user_success = '';
if(isset($_POST['action']) && $_POST['action'] == 'edit_user') {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $firstName = isset($_POST['firstName']) ? FormSanitizer::sanitizeFormString($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? FormSanitizer::sanitizeFormString($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? FormSanitizer::sanitizeFormString($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $mobileNumber = isset($_POST['mobileNumber']) ? FormSanitizer::sanitizeFormString($_POST['mobileNumber']) : '0';
    $gender = isset($_POST['gender']) ? intval($_POST['gender']) : 0;
    $dob = isset($_POST['dob']) ? FormSanitizer::sanitizeFormString($_POST['dob']) : NULL;
    $approved = isset($_POST['approved']) ? intval($_POST['approved']) : 0;
    
    // Validate required fields
    if(empty($firstName) || empty($lastName) || empty($email)) {
        $edit_user_error = "All required fields must be filled";
    } else if($user_id <= 0) {
        $edit_user_error = "Invalid user ID";
    } else {
        // Check if email is used by another user
        $check_email = $con->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->execute([$email, $user_id]);
        
        if($check_email->rowCount() > 0) {
            $edit_user_error = "Email already used by another user";
        } else {
            try {
                // Check if password needs to be updated
                if(!empty($password)) {
                    // Hash new password
                    $hashed_password = hash('sha512', $password);
                    $update_user = $con->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ?, mobileNumber = ?, gender = ?, dob = ?, approved = ?, password = ? WHERE id = ?");
                    
                    $dob_value = !empty($dob) ? $dob : NULL;
                    $mobileNumber_value = !empty($mobileNumber) ? intval($mobileNumber) : 0;
                    
                    if($update_user->execute([$firstName, $lastName, $email, $mobileNumber_value, $gender, $dob_value, $approved, $hashed_password, $user_id])) {
                        $edit_user_success = "User updated successfully with new password!";
                        // Refresh user list
                        $query = $con->prepare("SELECT * FROM users ORDER BY createdDate DESC LIMIT 1000");
                        $query->execute();
                        $users = $query->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $edit_user_error = "Failed to update user: " . implode(", ", $update_user->errorInfo());
                    }
                } else {
                    // Update without changing password
                    $update_user = $con->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ?, mobileNumber = ?, gender = ?, dob = ?, approved = ? WHERE id = ?");
                    
                    $dob_value = !empty($dob) ? $dob : NULL;
                    $mobileNumber_value = !empty($mobileNumber) ? intval($mobileNumber) : 0;
                    
                    if($update_user->execute([$firstName, $lastName, $email, $mobileNumber_value, $gender, $dob_value, $approved, $user_id])) {
                        $edit_user_success = "User updated successfully!";
                        // Refresh user list
                        $query = $con->prepare("SELECT * FROM users ORDER BY createdDate DESC LIMIT 1000");
                        $query->execute();
                        $users = $query->fetchAll(PDO::FETCH_ASSOC);
                    } else {
                        $edit_user_error = "Failed to update user";
                    }
                }
            } catch(Exception $e) {
                $edit_user_error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Handle Add User from Modal Form
$add_user_error = '';
$add_user_success = '';

if(isset($_POST['action']) && $_POST['action'] == 'add_user') {
    
    // Get form data
    $firstName = isset($_POST['firstName']) ? FormSanitizer::sanitizeFormString($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? FormSanitizer::sanitizeFormString($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? FormSanitizer::sanitizeFormString($_POST['email']) : '';
    $username = isset($_POST['username']) ? FormSanitizer::sanitizeFormString($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $mobileNumber = isset($_POST['mobileNumber']) ? FormSanitizer::sanitizeFormString($_POST['mobileNumber']) : '0';
    $gender = isset($_POST['gender']) ? intval($_POST['gender']) : 0;
    $dob = isset($_POST['dob']) ? FormSanitizer::sanitizeFormString($_POST['dob']) : NULL;
    $type = 0; // Default type for students
    
    error_log("Sanitized data: firstName=$firstName, lastName=$lastName, email=$email, username=$username");
    
    // Validate required fields
    if(empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
        $add_user_error = "All required fields must be filled (firstName: '$firstName', lastName: '$lastName', email: '$email', username: '$username', password: '" . (!empty($password) ? "***" : "empty") . "')";
        error_log("Validation error: " . $add_user_error);
    } else {
        // Check if username already exists
        $check_username = $con->prepare("SELECT id FROM users WHERE username = ?");
        $check_username->execute([$username]);
        
        if($check_username->rowCount() > 0) {
            $add_user_error = "Username already exists";
            error_log("Username already exists: " . $username);
        } else {
            // Check if email already exists
            $check_email = $con->prepare("SELECT id FROM users WHERE email = ?");
            $check_email->execute([$email]);
            
            if($check_email->rowCount() > 0) {
                $add_user_error = "Email already exists";
                error_log("Email already exists: " . $email);
            } else {
                // Hash password
                $hashed_password = hash('sha512', $password);
                
                // Insert user
                try {
                    $insert_user = $con->prepare("INSERT INTO users (firstName, lastName, email, username, password, mobileNumber, gender, dob, type, createdDate, createdBy) 
                                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
                    
                    $dob_value = !empty($dob) ? $dob : NULL;
                    $mobileNumber_value = !empty($mobileNumber) ? intval($mobileNumber) : 0;
                    
                    error_log("Attempting to insert user with data: firstName=$firstName, lastName=$lastName, email=$email, username=$username, mobile=$mobileNumber_value, gender=$gender, dob=$dob_value, type=$type, createdBy=" . $_SESSION['username']);
                    
                    if($insert_user->execute([$firstName, $lastName, $email, $username, $hashed_password, $mobileNumber_value, $gender, $dob_value, $type, $_SESSION['username']])) {
                        // Refresh user list
                        $query = $con->prepare("SELECT * FROM users ORDER BY createdDate DESC LIMIT 1000");
                        $query->execute();
                        $users = $query->fetchAll(PDO::FETCH_ASSOC);
                        $add_user_success = "User created successfully!";
                        error_log("User created successfully: " . $username);
                    } else {
                        $add_user_error = "Failed to create user: " . implode(", ", $insert_user->errorInfo());
                        error_log("Insert failed: " . $add_user_error);
                    }
                } catch(Exception $e) {
                    $add_user_error = "Database error: " . $e->getMessage();
                    error_log("Exception: " . $add_user_error);
                }
            }
        }
    }
}

// Handle Edit User from Modal Form

// Handle Bulk Upload
$bulk_upload_error = '';
$bulk_upload_success = '';
if(isset($_POST['bulk_upload'])) {
    if(isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $file_tmp = $_FILES['csv_file']['tmp_name'];
        $file_type = pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION);
        
        if($file_type == 'csv') {
            $csv_data = array_map('str_getcsv', file($file_tmp));
            $header = array_shift($csv_data);
            
            // Auto-detect column order based on header row
            $col_map = array();
            $header_lower = array_map('strtolower', $header);
            
            // Check if user uploaded with old (wrong) column order: username,email,password,firstName,lastName
            // vs correct order: firstName,lastName,email,username,password
            if(isset($header[0]) && strtolower(trim($header[0])) === 'username') {
                // OLD WRONG ORDER detected: username,email,password,firstName,lastName...
                $col_map = array(
                    'firstName' => 3,   // Position 3 (was 4th column)
                    'lastName' => 4,    // Position 4 (was 5th column) 
                    'email' => 1,       // Position 1 (was 2nd column)
                    'username' => 0,    // Position 0 (was 1st column)
                    'password' => 2,    // Position 2 (was 3rd column)
                    'mobileNumber' => 5,
                    'gender' => 6,
                    'dob' => 7
                );
                $bulk_upload_success = "⚠️ DETECTED OLD CSV FORMAT - Correcting column mapping...";
            } else {
                // CORRECT ORDER: firstName,lastName,email,username,password...
                $col_map = array(
                    'firstName' => 0,
                    'lastName' => 1,
                    'email' => 2,
                    'username' => 3,
                    'password' => 4,
                    'mobileNumber' => 5,
                    'gender' => 6,
                    'dob' => 7
                );
            }
            
            $inserted = 0;
            $skipped = 0;
            foreach($csv_data as $row) {
                // Skip empty rows
                if(empty(trim($row[0]))) continue;
                
                // Extract data from CSV using detected column mapping
                $firstName = isset($row[$col_map['firstName']]) ? FormSanitizer::sanitizeFormString(trim($row[$col_map['firstName']])) : '';
                $lastName = isset($row[$col_map['lastName']]) ? FormSanitizer::sanitizeFormString(trim($row[$col_map['lastName']])) : '';
                $email = isset($row[$col_map['email']]) ? FormSanitizer::sanitizeFormString(trim($row[$col_map['email']])) : '';
                $username = isset($row[$col_map['username']]) ? FormSanitizer::sanitizeFormString(trim($row[$col_map['username']])) : '';
                $password = isset($row[$col_map['password']]) ? trim($row[$col_map['password']]) : '';
                $mobileNumber = isset($row[$col_map['mobileNumber']]) ? FormSanitizer::sanitizeFormString(trim($row[$col_map['mobileNumber']])) : '0';
                $gender = isset($row[$col_map['gender']]) ? intval($row[$col_map['gender']]) : 0;
                $dob = isset($row[$col_map['dob']]) && !empty(trim($row[$col_map['dob']])) ? FormSanitizer::sanitizeFormString(trim($row[$col_map['dob']])) : NULL;
                
                // Validate required fields
                if(empty($firstName) || empty($lastName) || empty($email) || empty($username) || empty($password)) {
                    $skipped++;
                    continue;
                }
                
                // Check if username already exists
                $check = $con->prepare("SELECT id FROM users WHERE username = ?");
                $check->execute([$username]);
                if($check->rowCount() == 0) {
                    // Check if email already exists
                    $check_email = $con->prepare("SELECT id FROM users WHERE email = ?");
                    $check_email->execute([$email]);
                    if($check_email->rowCount() == 0) {
                        $hashed_password = hash('sha512', $password);
                        $mobileNumber_value = !empty($mobileNumber) ? intval($mobileNumber) : 0;
                        
                        $insert = $con->prepare("INSERT INTO users (firstName, lastName, email, username, password, mobileNumber, gender, dob, type, createdDate, createdBy) 
                                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), ?)");
                        if($insert->execute([$firstName, $lastName, $email, $username, $hashed_password, $mobileNumber_value, $gender, $dob, $_SESSION['username']])) {
                            $inserted++;
                        }
                    } else {
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }
            }
            $bulk_upload_success = "$inserted users imported successfully!" . ($skipped > 0 ? " ($skipped skipped due to duplicates or missing fields)" : "");
        } else {
            $bulk_upload_error = "Please upload a CSV file";
        }
    } else {
        $bulk_upload_error = "File upload error";
    }
}
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
	<meta name="keywords" content="admin, dashboard, user management">
	<meta property="og:url" content="https://training.learnlike.in" />
    	<meta property="og:type" content="article" />
    	<meta property="og:title" content="Learnlike's Training Dashboard" />

	<!-- FAVICON -->
	<link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/LL-logo-light.png"/>

	<!-- TITLE -->
	<title>User Management - Learnlike's Training</title>

	<!-- BOOTSTRAP CSS -->
	<link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

	<!-- STYLE CSS -->
	<link href="../assets/css/style.css" rel="stylesheet" />
	<link href="../assets/css/skin-modes.css" rel="stylesheet" />

	<!--- FONT-ICONS CSS -->
	<link href="../assets/css/icons.css" rel="stylesheet" />

    <!-- INTERNAL Switcher css -->
    <link href="../assets/switcher/css/switcher.css" rel="stylesheet" />
    <link href="../assets/switcher/demo.css" rel="stylesheet" />

	<style>
		:root {
			--primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			--danger-gradient: linear-gradient(135deg, #f64e60 0%, #ee5a6f 100%);
		}

		body {
			background-color: #f5f7fb;
		}

		.page-header {
			background: white;
			padding: 2rem 1rem;
			border-bottom: 1px solid #e7eef7;
			margin-bottom: 2rem;
		}

		.page-title {
			color: #2d3748;
			font-weight: 700;
			font-size: 1.75rem;
			margin-bottom: 0.5rem;
		}

		.btn-list {
			display: flex;
			gap: 1rem;
			flex-wrap: wrap;
		}

		.btn-primary, .btn-secondary {
			border: none;
			border-radius: 8px;
			padding: 0.65rem 1.5rem;
			font-weight: 600;
			font-size: 0.95rem;
			transition: all 0.3s ease;
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
		}

		.btn-primary {
			background: var(--primary-gradient);
			color: white;
			box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
		}

		.btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
			color: white;
		}

		.btn-secondary {
			background: #e7eef7;
			color: #667eea;
		}

		.btn-secondary:hover {
			background: #d7dff7;
			color: #667eea;
			transform: translateY(-2px);
		}

		.card {
			border: none;
			border-radius: 12px;
			box-shadow: 0 2px 12px rgba(0,0,0,0.08);
			margin-bottom: 2rem;
			overflow: hidden;
		}

		.card-header {
			background: white;
			padding: 1.5rem;
			border-bottom: 1px solid #e7eef7;
		}

		.card-title {
			color: #2d3748;
			font-weight: 700;
			font-size: 1.25rem;
			margin: 0;
		}

		.card-body {
			padding: 1.5rem;
		}

		.table {
			margin-bottom: 0;
		}

		.table thead th {
			background-color: #f5f7fb;
			color: #4a5568;
			font-weight: 600;
			border: 1px solid #e7eef7;
			padding: 1rem;
		}

		.table tbody td {
			border: 1px solid #e7eef7;
			padding: 1rem;
			vertical-align: middle;
		}

		.table tbody tr {
			background: white;
			transition: all 0.2s ease;
		}

		.table tbody tr:hover {
			background-color: #f9fafb;
			box-shadow: inset 0 0 0 2000px rgba(102, 126, 234, 0.02);
		}

		.badge-status {
			padding: 6px 12px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: 600;
			display: inline-block;
		}

		.bg-success {
			background-color: #13c2c2 !important;
			color: white;
		}

		.bg-warning {
			background-color: #faad14 !important;
			color: white;
		}

		.btn-danger {
			background: var(--danger-gradient);
			border: none;
			color: white;
			padding: 0.4rem 0.6rem;
			border-radius: 6px;
			transition: all 0.2s ease;
		}

		.btn-danger:hover {
			transform: scale(1.05);
			box-shadow: 0 4px 12px rgba(246, 78, 96, 0.3);
			color: white;
		}

		.btn-info {
			background: #17a2b8;
			border: none;
			color: white;
			padding: 0.4rem 0.6rem;
			border-radius: 6px;
			transition: all 0.2s ease;
		}

		.btn-info:hover {
			background: #138496;
			transform: scale(1.05);
			box-shadow: 0 4px 12px rgba(23, 162, 184, 0.3);
			color: white;
			text-decoration: none;
		}

		.modal-content {
			border-radius: 12px;
			border: none;
			box-shadow: 0 10px 40px rgba(0,0,0,0.15);
		}

		.modal-header {
			background: var(--primary-gradient);
			border-radius: 12px 12px 0 0;
			border: none;
			padding: 1.5rem;
		}

		.modal-header .btn-close {
			filter: brightness(0) invert(1);
		}

		.modal-title {
			color: white;
			font-weight: 700;
			font-size: 1.25rem;
		}

		.form-label {
			color: #2d3748;
			font-weight: 600;
			margin-bottom: 0.5rem;
			font-size: 0.95rem;
		}

		.form-control, .form-select {
			border: 1px solid #e7eef7;
			border-radius: 8px;
			padding: 0.65rem 0.875rem;
			font-size: 0.95rem;
			transition: all 0.2s ease;
		}

		.form-control:focus, .form-select:focus {
			border-color: #667eea;
			box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
		}

		.modal-footer {
			padding: 1rem 1.5rem;
			background: #f9fafb;
			border-top: 1px solid #e7eef7;
		}

		.csv-template {
			font-size: 13px;
			background: #f5f7fb;
			padding: 12px;
			border-radius: 8px;
			font-family: 'Courier New', monospace;
			margin-top: 1rem;
			border-left: 3px solid #667eea;
			color: #2d3748;
		}

		.alert {
			border: none;
			border-radius: 8px;
			padding: 1rem 1.5rem;
			margin-bottom: 1.5rem;
		}

		.alert-success {
			background: #f0fdf4;
			color: #166534;
			border-left: 4px solid #13c2c2;
		}

		.alert-danger {
			background: #fef2f2;
			color: #991b1b;
			border-left: 4px solid #f64e60;
		}

		.breadcrumb {
			background: transparent;
			padding: 0;
			margin: 0;
		}

		.breadcrumb-item {
			color: #718096;
		}

		.breadcrumb-item.active {
			color: #4a5568;
			font-weight: 600;
		}

		.page-rightheader {
			display: flex;
			gap: 1rem;
		}

		@media (max-width: 768px) {
			.page-header {
				flex-direction: column;
			}

			.btn-list {
				flex-direction: column;
			}

			.btn {
				width: 100%;
				justify-content: center;
			}

			.table {
				font-size: 0.85rem;
			}

			.table thead th, .table tbody td {
				padding: 0.75rem;
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
											<a href="javascript: void(0);" data-bs-toggle="dropdown"
												class="nav-link leading-none d-flex">
												<img src="<?php echo $filePath; ?>" alt="profile-user"
													class="avatar  profile-user brround cover-image">
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

			<!-- APP-SIDEBAR-->
			<aside class="app-sidebar sticky">
				<div class="app-sidebar__logo">
					<a class="header-brand" href="index.php">
						<img src="../assets/images/brand/CV_Logo.png" class="header-brand-img light-logo" alt="logo">
						<img src="../assets/images/brand/CV_Logo.png" class="header-brand-img light-logo1" alt="logo">
					</a>
				</div>
				<ul class="side-menu">
					<li class="side-item side-item-category">
						<span class="hide-menu">Main</span>
					</li>
					<li class="slide">
						<a href="index.php" class="side-menu__item">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-2h2v16h-2z"/></svg>
							<span class="side-menu__label">Dashboard</span>
						</a>
					</li>
					<li class="slide">
						<a href="user_management.php" class="side-menu__item active">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
							<span class="side-menu__label">User Management</span>
						</a>
					</li>
					<li class="slide">
						<a href="approvals.php" class="side-menu__item">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
							<span class="side-menu__label">Approvals</span>
						</a>
					</li>
					<li class="slide">
						<a href="programme_management.php" class="side-menu__item">
							<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 6h16V4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4v2h8v-2h4c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 12V6h16v12H4z"/></svg>
							<span class="side-menu__label">Programme Management</span>
						</a>
					</li>
				</ul>
			</aside>
			<!-- /APP-SIDEBAR-->

			<!-- APP-CONTENT -->
			<div class="app-content main-content">
				<div class="side-app">

					<!-- PAGE-HEADER -->
					<div class="page-header">
						<div class="page-leftheader">
							<h4 class="page-title">User Management</h4>
							<ol class="breadcrumb pt-0">
								<li class="breadcrumb-item"><a href="index.php">Admin</a></li>
								<li class="breadcrumb-item active" aria-current="page">Users</li>
							</ol>
						</div>
						<div class="page-rightheader">
							<div class="btn-list">
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
								Add User
							</button>
							<button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
									<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
									Bulk Upload
								</button>
							</div>
						</div>
					</div>
					<!-- /PAGE-HEADER -->

					<!-- ALERTS -->
					<?php if(isset($_GET['success'])): ?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>Success!</strong> <?php echo htmlspecialchars($_GET['success']); ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php endif; ?>
					<?php if(isset($_GET['message'])): ?>
						<div class="alert alert-info alert-dismissible fade show" role="alert">
							<strong>Info:</strong> <?php echo htmlspecialchars($_GET['message']); ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php endif; ?>
					<?php if($add_user_success): ?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>Success!</strong> <?php echo $add_user_success; ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php endif; ?>
					<?php if($add_user_error): ?>
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
							<strong>Error!</strong> <?php echo $add_user_error; ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php endif; ?>
					<?php if($edit_user_success): ?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>Success!</strong> <?php echo $edit_user_success; ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php endif; ?>
					<?php if($edit_user_error): ?>
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
							<strong>Error!</strong> <?php echo $edit_user_error; ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php endif; ?>
					<?php if($bulk_upload_success): ?>
						<div class="alert alert-success alert-dismissible fade show" role="alert">
							<strong>Success!</strong> <?php echo $bulk_upload_success; ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php endif; ?>
					<?php if($bulk_upload_error): ?>
						<div class="alert alert-danger alert-dismissible fade show" role="alert">
							<strong>Error!</strong> <?php echo $bulk_upload_error; ?>
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
						</div>
					<?php endif; ?>
					<!-- /ALERTS -->

					<!-- CARD -->
					<div class="card">
						<div class="card-header">
							<h3 class="card-title">All Users</h3>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table id="userTable" class="table table-hover table-bordered">
									<thead>
										<tr>
											<th>ID</th>
											<th>First Name</th>
											<th>Last Name</th>
											<th>Username</th>
											<th>Email</th>
											<th>Mobile</th>
											<th>Status</th>
											<th>Created Date</th>
											<th>Actions</th>
										</tr>
									</thead>
									<tbody>
										<?php 
										if(isset($users) && is_array($users) && count($users) > 0):
											foreach($users as $user): 
										?>
											<tr>
												<td><?php echo isset($user['id']) ? htmlspecialchars($user['id']) : 'N/A'; ?></td>
												<td><?php echo isset($user['firstName']) ? htmlspecialchars($user['firstName']) : 'N/A'; ?></td>
												<td><?php echo isset($user['lastName']) ? htmlspecialchars($user['lastName']) : 'N/A'; ?></td>
												<td><?php echo isset($user['username']) ? htmlspecialchars($user['username']) : 'N/A'; ?></td>
												<td><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : 'N/A'; ?></td>
												<td><?php echo isset($user['mobileNumber']) ? htmlspecialchars($user['mobileNumber']) : 'N/A'; ?></td>
												<td>
													<?php if(isset($user['approved']) && $user['approved'] == 1): ?>
														<span class="badge badge-status bg-success">Approved</span>
													<?php else: ?>
														<span class="badge badge-status bg-warning">Pending</span>
													<?php endif; ?>
												</td>
												<td><?php echo isset($user['createdDate']) ? date('M d, Y', strtotime($user['createdDate'])) : 'N/A'; ?></td>
												<td>
													<div class="btn-group" role="group">
														<a href="user-details.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info" title="View Details">
															<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
														</a>
														<button type="button" class="btn btn-sm btn-primary" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" data-bs-toggle="modal" data-bs-target="#editUserModal">
															<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19H4v-3L16.5 3.5z"></path></svg>
														</button>
														<button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
															<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
														</button>
													</div>
												</td>
											</tr>
										<?php 
											endforeach;
										else:
										?>
											<tr><td colspan="9" class="text-center text-muted">No users found</td></tr>
										<?php endif; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<!-- /CARD -->

				</div>
			</div>
			<!-- /APP-CONTENT -->

		</div>

		<!-- FOOTER -->
		<footer class="footer">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-md-12 col-sm-12">
						<span>Copyright © 2025 <a href="javascript:void(0);">Learnlike</a>. All rights reserved.</span>
					</div>
				</div>
			</div>
		</footer>
		<!-- /FOOTER -->

	</div>

	<!-- JQUERY JS -->
	<script src="../assets/js/jquery.min.js"></script>

	<!-- BOOTSTRAP JS -->
	<script src="../assets/plugins/bootstrap/js/popper.min.js"></script>
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
	<!-- SIDEBAR TOGGLE -->
	<script>
		$(document).on('click', '[data-bs-toggle="sidebar"]', function (event) {
			event.preventDefault();
			$('.app').toggleClass('sidenav-toggled');
		});
	</script>

	<!-- MODALS -->
	<!-- ADD USER MODAL -->
	<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-white" id="addUserModalLabel">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2" style="vertical-align: middle;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"></path></svg>
						Add New User
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form method="POST" id="addUserForm">
					<input type="hidden" name="action" value="add_user">
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">First Name *</label>
								<input type="text" class="form-control" name="firstName" required>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Last Name *</label>
								<input type="text" class="form-control" name="lastName" required>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Email *</label>
								<input type="email" class="form-control" name="email" required>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Username *</label>
								<input type="text" class="form-control" name="username" required>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Password *</label>
								<input type="password" class="form-control" name="password" required>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Mobile Number</label>
								<input type="tel" class="form-control" name="mobileNumber">
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Gender</label>
								<select class="form-select" name="gender">
									<option value="0">Select Gender</option>
									<option value="1">Male</option>
									<option value="2">Female</option>
									<option value="3">Other</option>
								</select>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Date of Birth</label>
								<input type="date" class="form-control" name="dob">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Create User</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /ADD USER MODAL -->

	<!-- EDIT USER MODAL -->
	<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-white" id="editUserModalLabel">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2" style="vertical-align: middle;"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19H4v-3L16.5 3.5z"></path></svg>
						Edit User
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form method="POST" id="editUserForm">
					<input type="hidden" name="action" value="edit_user">
					<div class="modal-body">
						<input type="hidden" name="user_id" id="editUserId">
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">First Name *</label>
								<input type="text" class="form-control" name="firstName" id="editFirstName" required>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Last Name *</label>
								<input type="text" class="form-control" name="lastName" id="editLastName" required>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Email *</label>
								<input type="email" class="form-control" name="email" id="editEmail" required>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Mobile Number</label>
								<input type="tel" class="form-control" name="mobileNumber" id="editMobileNumber">
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Gender</label>
								<select class="form-select" name="gender" id="editGender">
									<option value="0">Select Gender</option>
									<option value="1">Male</option>
									<option value="2">Female</option>
									<option value="3">Other</option>
								</select>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Date of Birth</label>
								<input type="date" class="form-control" name="dob" id="editDob">
							</div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-3">
								<label class="form-label">Password (Leave empty to keep current)</label>
								<div class="input-group">
									<input type="password" class="form-control" name="password" id="editPassword" placeholder="Enter new password or leave empty">
									<button class="btn btn-outline-secondary" type="button" id="toggleEditPassword">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
									</button>
								</div>
								<small class="text-muted">Leave blank to keep the current password</small>
							</div>
							<div class="col-md-6 mb-3">
								<label class="form-label">Approval Status</label>
								<select class="form-select" name="approved" id="editApproved">
									<option value="0">Pending</option>
									<option value="1">Approved</option>
								</select>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" class="btn btn-primary">Update User</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /EDIT USER MODAL -->

	<!-- BULK UPLOAD MODAL -->
	<div class="modal fade" id="bulkUploadModal" tabindex="-1" role="dialog" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-white" id="bulkUploadModalLabel">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2" style="vertical-align: middle;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
						Bulk Upload Users
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<form method="POST" enctype="multipart/form-data" id="bulkUploadForm">
					<div class="modal-body">
						<div class="mb-3">
							<div class="d-flex justify-content-between align-items-center mb-2">
								<label class="form-label mb-0">Upload CSV File *</label>
								<a href="download_csv_template.php" class="btn btn-sm btn-outline-primary">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
									Download Template
								</a>
							</div>
							<input type="file" class="form-control" name="csv_file" accept=".csv" required>
							<small class="form-text text-muted d-block mt-2">
								<strong>CSV Format Required:</strong><br>
								First row should contain headers. Include columns in this order (all fields except dob are required):
							</small>
							<div class="csv-template">
firstName,lastName,email,username,password,mobileNumber,gender,dob<br>
John,Doe,john@example.com,john123,password123,9876543210,1,2000-05-15<br>
Jane,Smith,jane@example.com,jane456,password456,9876543211,2,2001-08-20
							</div>
						</div>
						<div class="alert alert-info mb-3">
							<strong>Field Information:</strong><br>
							• <strong>gender:</strong> 0=Select, 1=Male, 2=Female, 3=Other<br>
							• <strong>dob:</strong> Date of birth in YYYY-MM-DD format (optional)<br>
							• <strong>mobileNumber:</strong> Phone number (optional)<br>
							• Duplicate usernames will be skipped. All passwords will be hashed automatically.
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
						<button type="submit" name="bulk_upload" class="btn btn-primary">Upload Users</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<!-- /BULK UPLOAD MODAL -->

	<!-- SCRIPTS -->
	<script>
		// Delete User
		function deleteUser(userId) {
			if(confirm('Are you sure you want to delete this user?')) {
				window.location.href = 'user_management.php?action=delete&id=' + userId;
			}
		}

		// Edit User - Populate modal with user data
		function editUser(userData) {
			console.log('User data:', userData);
			$('#editUserId').val(userData.id);
			$('#editFirstName').val(userData.firstName);
			$('#editLastName').val(userData.lastName);
			$('#editEmail').val(userData.email);
			$('#editMobileNumber').val(userData.mobileNumber);
			$('#editGender').val(userData.gender);
			$('#editDob').val(userData.dob);
			$('#editApproved').val(userData.approved);
		}

		// Initialize simple search functionality
		$(document).ready(function() {
			// Toggle password visibility in edit modal
			$('#toggleEditPassword').on('click', function() {
				var passwordField = $('#editPassword');
				var isPassword = passwordField.attr('type') === 'password';
				
				if(isPassword) {
					passwordField.attr('type', 'text');
					$(this).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>');
				} else {
					passwordField.attr('type', 'password');
					$(this).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>');
				}
			});

			// Add search functionality to table
			var searchTimeout;
			$('.table-search').on('keyup', function() {
				clearTimeout(searchTimeout);
				var searchValue = $(this).val().toLowerCase();
				
				searchTimeout = setTimeout(function() {
					$('#userTable tbody tr').each(function() {
						var rowText = $(this).text().toLowerCase();
						if(rowText.indexOf(searchValue) > -1) {
							$(this).show();
						} else {
							$(this).hide();
						}
					});
				}, 300);
			});
		});

		// Edit User Modal
		$('#editUserModal').on('show.bs.modal', function (e) {
			var button = $(e.relatedTarget);
			var userId = button.data('user-id');
			
			// Fetch user data via AJAX (you'll need to create an API endpoint for this)
			// For now, this is a placeholder
			console.log('Edit user: ' + userId);
		});

		// Add User Form Submit
		$('#addUserForm').on('submit', function(e) {
			// Allow normal form submission (POST)
			// Form will submit to the same page and PHP will process it
		});

		// Edit User Form Submit
		$('#editUserForm').on('submit', function(e) {
			// Allow normal form submission (POST)
			// Form will submit to the same page and PHP will process it
		});
	</script>

</body>

</html>
