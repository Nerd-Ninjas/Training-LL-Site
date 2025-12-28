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

// Handle AJAX request for loading mapped courses
if(isset($_GET['action']) && $_GET['action'] == 'get_mapped_courses' && isset($_GET['programme_id'])) {
    header('Content-Type: application/json');
    
    try {
        $programme_id = FormSanitizer::sanitizeFormString($_GET['programme_id']);
        
        $query = $con->prepare("
            SELECT c.course_id
            FROM programme_courses_mapping pcm
            JOIN courses_master c ON pcm.course_id = c.course_id
            WHERE pcm.programme_id = ?
            ORDER BY c.course_id
        ");
        $query->execute([$programme_id]);
        $mapped_courses = $query->fetchAll(PDO::FETCH_COLUMN, 0);
        
        echo json_encode([
            'success' => true,
            'mapped_courses' => $mapped_courses
        ]);
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

$account = new Account($con);
$username = $_SESSION["username"];

// Get admin details
try {
    $userDetails = $account->getUserDetails($username);
    $firstName = $userDetails['firstName'];
    $lastName = $userDetails['lastName'];
    $avatarID = $userDetails['avatarID'];
    $avatarDetails = $account->avatarFetch($avatarID);
    $filePath = $avatarDetails['filePath'];
} catch(Exception $e) {
    header("Location: ../login.php");
    exit;
}

// Handle Programme Management
$programme_error = '';
$programme_success = '';

// Add/Edit Programme
if(isset($_POST['action']) && $_POST['action'] == 'save_programme') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $programme_id = FormSanitizer::sanitizeFormString($_POST['programme_id'] ?? '');
    $programme_name = FormSanitizer::sanitizeFormString($_POST['programme_name'] ?? '');
    
    if(empty($programme_id) || empty($programme_name)) {
        $programme_error = "Programme ID and name are required";
    } else {
        try {
            if($id > 0) {
                // Update
                $update = $con->prepare("UPDATE programmes_master SET programme_id=?, programme_name=? WHERE id=?");
                if($update->execute([$programme_id, $programme_name, $id])) {
                    $programme_success = "Programme updated successfully!";
                } else {
                    $programme_error = "Failed to update programme";
                }
            } else {
                // Insert
                $insert = $con->prepare("INSERT INTO programmes_master (programme_id, programme_name, approved, approvedBy, approvedDate) VALUES (?, ?, 1, ?, NOW())");
                if($insert->execute([$programme_id, $programme_name, $_SESSION['username']])) {
                    $programme_success = "Programme added successfully!";
                } else {
                    $programme_error = "Failed to add programme";
                }
            }
        } catch(Exception $e) {
            $programme_error = "Error: " . $e->getMessage();
        }
    }
}

// Delete Programme
if(isset($_GET['action']) && $_GET['action'] == 'delete_programme' && isset($_GET['id'])) {
    try {
        $delete = $con->prepare("DELETE FROM programmes_master WHERE id=?");
        if($delete->execute([$_GET['id']])) {
            $programme_success = "Programme deleted successfully!";
        } else {
            $programme_error = "Failed to delete programme";
        }
    } catch(Exception $e) {
        $programme_error = "Error: " . $e->getMessage();
    }
}

// Handle Course Management
$course_error = '';
$course_success = '';

// Add/Edit Course
if(isset($_POST['action']) && $_POST['action'] == 'save_course') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $course_id = FormSanitizer::sanitizeFormString($_POST['course_id'] ?? '');
    $course_name = FormSanitizer::sanitizeFormString($_POST['course_name'] ?? '');
    $description = FormSanitizer::sanitizeFormString($_POST['description'] ?? '');
    $image_path = '';
    
    if(empty($course_id) || empty($course_name)) {
        $course_error = "Course ID and name are required";
    } else {
        try {
            // Handle image upload
            if(isset($_FILES['course_image']) && $_FILES['course_image']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = '../assets/images/courses/';
                
                // Create directory if it doesn't exist
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = $_FILES['course_image']['name'];
                $file_tmp = $_FILES['course_image']['tmp_name'];
                $file_size = $_FILES['course_image']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Validate file type (only images)
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if(!in_array($file_ext, $allowed_exts)) {
                    throw new Exception("Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.");
                }
                
                // Validate file size (max 5MB)
                if($file_size > 5242880) {
                    throw new Exception("File size exceeds 5MB limit.");
                }
                
                // Generate unique filename
                $new_filename = 'course_' . time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . $new_filename;
                
                // Move uploaded file
                if(move_uploaded_file($file_tmp, $upload_path)) {
                    $image_path = 'assets/images/courses/' . $new_filename;
                } else {
                    throw new Exception("Failed to upload image file.");
                }
            }
            
            if($id > 0) {
                // Update
                if(!empty($image_path)) {
                    // If new image uploaded, delete old image
                    $old_course = $con->prepare("SELECT image FROM courses_master WHERE id=?");
                    $old_course->execute([$id]);
                    $old_data = $old_course->fetch(PDO::FETCH_ASSOC);
                    
                    if($old_data && !empty($old_data['image'])) {
                        $old_image_path = '../' . $old_data['image'];
                        if(file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                    
                    $update = $con->prepare("UPDATE courses_master SET course_id=?, course_name=?, description=?, image=? WHERE id=?");
                    if($update->execute([$course_id, $course_name, $description, $image_path, $id])) {
                        $course_success = "Course updated successfully!";
                    } else {
                        $course_error = "Failed to update course";
                    }
                } else {
                    $update = $con->prepare("UPDATE courses_master SET course_id=?, course_name=?, description=? WHERE id=?");
                    if($update->execute([$course_id, $course_name, $description, $id])) {
                        $course_success = "Course updated successfully!";
                    } else {
                        $course_error = "Failed to update course";
                    }
                }
            } else {
                // Insert
                $insert = $con->prepare("INSERT INTO courses_master (course_id, course_name, description, image, approved, approvedBy, approvedDate) VALUES (?, ?, ?, ?, 1, ?, NOW())");
                if($insert->execute([$course_id, $course_name, $description, $image_path, $_SESSION['username']])) {
                    $course_success = "Course added successfully!";
                } else {
                    $course_error = "Failed to add course";
                }
            }
        } catch(Exception $e) {
            $course_error = "Error: " . $e->getMessage();
        }
    }
}

// Delete Course
if(isset($_GET['action']) && $_GET['action'] == 'delete_course' && isset($_GET['id'])) {
    try {
        $delete = $con->prepare("DELETE FROM courses_master WHERE id=?");
        if($delete->execute([$_GET['id']])) {
            $course_success = "Course deleted successfully!";
        } else {
            $course_error = "Failed to delete course";
        }
    } catch(Exception $e) {
        $course_error = "Error: " . $e->getMessage();
    }
}

// Handle Course Mapping
$mapping_error = '';
$mapping_success = '';

if(isset($_POST['action']) && $_POST['action'] == 'save_mapping') {
    $programme_id = FormSanitizer::sanitizeFormString($_POST['programme_id'] ?? '');
    $course_ids = isset($_POST['course_ids']) && is_array($_POST['course_ids']) ? $_POST['course_ids'] : [];
    
    if(empty($programme_id)) {
        $mapping_error = "Please select a programme";
    } else {
        try {
            // Delete all existing mappings for this programme
            $delete_stmt = $con->prepare("DELETE FROM programme_courses_mapping WHERE programme_id = ?");
            $delete_result = $delete_stmt->execute([$programme_id]);
            
            if(!$delete_result) {
                throw new Exception("Failed to delete existing mappings");
            }
            
            $courses_mapped = 0;
            
            // Insert new mappings for each selected course
            if(!empty($course_ids) && count($course_ids) > 0) {
                $insert_stmt = $con->prepare("INSERT INTO programme_courses_mapping (programme_id, course_id, approved, approvedBy, approvedDate) VALUES (?, ?, 1, ?, NOW())");
                
                foreach($course_ids as $course_id) {
                    $sanitized_course_id = FormSanitizer::sanitizeFormString($course_id);
                    
                    if(empty($sanitized_course_id)) {
                        continue;
                    }
                    
                    $result = $insert_stmt->execute([$programme_id, $sanitized_course_id, $_SESSION['username']]);
                    
                    if(!$result) {
                        throw new Exception("Failed to map course: " . $sanitized_course_id);
                    }
                    
                    $courses_mapped++;
                }
                
                if($courses_mapped > 0) {
                    $mapping_success = "Successfully mapped " . $courses_mapped . " course(s) to programme " . htmlspecialchars($programme_id) . "!";
                } else {
                    $mapping_success = "No valid courses to map";
                }
            } else {
                $mapping_success = "All mappings for programme " . htmlspecialchars($programme_id) . " have been cleared!";
            }
        } catch(Exception $e) {
            $mapping_error = "Error saving mapping: " . $e->getMessage();
        }
    }
}

// Create tables if they don't exist
try {
    $con->exec("
        CREATE TABLE IF NOT EXISTS programmes_master (
            id INT AUTO_INCREMENT PRIMARY KEY,
            programme_id VARCHAR(50) UNIQUE,
            programme_name TEXT,
            approved INT DEFAULT 0,
            approvedBy VARCHAR(8),
            approvedDate DATETIME
        )
    ");
    
    $con->exec("
        CREATE TABLE IF NOT EXISTS courses_master (
            id INT AUTO_INCREMENT PRIMARY KEY,
            course_id VARCHAR(50) UNIQUE,
            course_name TEXT,
            approved INT DEFAULT 0,
            approvedBy VARCHAR(8),
            approvedDate DATETIME,
            description TEXT,
            image VARCHAR(255)
        )
    ");
    
    $con->exec("
        CREATE TABLE IF NOT EXISTS programme_courses_mapping (
            id INT AUTO_INCREMENT PRIMARY KEY,
            programme_id VARCHAR(50),
            course_id VARCHAR(50),
            approved INT,
            approvedBy VARCHAR(8),
            approvedDate DATETIME
        )
    ");
} catch(Exception $e) {
    // Tables might already exist
}

// Fetch all data
try {
    $programmes_query = $con->prepare("SELECT * FROM programmes_master ORDER BY id DESC");
    $programmes_query->execute();
    $programmes = $programmes_query->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $programmes = [];
}

try {
    $courses_query = $con->prepare("SELECT * FROM courses_master ORDER BY id DESC");
    $courses_query->execute();
    $courses = $courses_query->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $courses = [];
}

// Insert sample data if tables are empty
try {
    $count_programmes = $con->query("SELECT COUNT(*) as cnt FROM programmes_master")->fetch(PDO::FETCH_ASSOC)['cnt'];
    if($count_programmes == 0) {
        $sample_programmes = [
            ['MSTE', 'Microsoft Tools for Education', 1, '100000', date('Y-m-d H:i:s')],
            ['CVBG', 'Cyber Security Beginner', 1, '100000', date('Y-m-d H:i:s')],
            ['AIJM', 'Artificial Intelligence for Journalism and Multimedia', 1, '100000', date('Y-m-d H:i:s')]
        ];
        
        $insert_prog = $con->prepare("INSERT INTO programmes_master (programme_id, programme_name, approved, approvedBy, approvedDate) VALUES (?, ?, ?, ?, ?)");
        foreach($sample_programmes as $prog) {
            $insert_prog->execute($prog);
        }
    }
    
    $count_courses = $con->query("SELECT COUNT(*) as cnt FROM courses_master")->fetch(PDO::FETCH_ASSOC)['cnt'];
    if($count_courses == 0) {
        $sample_courses = [
            ['EXCEL-001', 'Excel Basics', 1, '100000', date('Y-m-d H:i:s'), 'Learn Excel fundamentals and functions', null],
            ['PPT-001', 'PowerPoint Essentials', 1, '100000', date('Y-m-d H:i:s'), 'Create professional presentations', null],
            ['NS-001', 'Network Security', 1, '100000', date('Y-m-d H:i:s'), 'Understand network security principles', null],
            ['AI-001', 'AI Fundamentals', 1, '100000', date('Y-m-d H:i:s'), 'Introduction to Artificial Intelligence', null]
        ];
        
        $insert_course = $con->prepare("INSERT INTO courses_master (course_id, course_name, approved, approvedBy, approvedDate, description, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach($sample_courses as $course) {
            $insert_course->execute($course);
        }
    }
} catch(Exception $e) {
    // Data might already exist
}

$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'programmes';
$edit_programme = null;
$edit_course = null;

if(isset($_GET['edit_programme']) && !empty($_GET['edit_programme'])) {
    $prog_query = $con->prepare("SELECT * FROM programmes_master WHERE id=?");
    $prog_query->execute([$_GET['edit_programme']]);
    $edit_programme = $prog_query->fetch(PDO::FETCH_ASSOC);
}

if(isset($_GET['edit_course']) && !empty($_GET['edit_course'])) {
    $c_query = $con->prepare("SELECT * FROM courses_master WHERE id=?");
    $c_query->execute([$_GET['edit_course']]);
    $edit_course = $c_query->fetch(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Programme Management - Learnlike's Training</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/LL-logo-light.png"/>
    <link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/style.css" rel="stylesheet" />
    <style>
        .tabs-container {
            display: flex;
            gap: 0;
            margin-bottom: 30px;
            border-bottom: 2px solid #e7eef7;
        }
        .tab-button {
            padding: 15px 25px;
            background: #f5f7fb;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #666;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .tab-button.active {
            background: white;
            color: #667eea;
            border-bottom-color: #667eea;
        }
        .tab-button:hover {
            background: white;
            color: #667eea;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .page-container {
            padding: 20px;
        }
        .card {
            border: 1px solid #e7eef7;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 30px;
        }
        .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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
        .table tbody tr:hover {
            background-color: #f9fafb;
        }
        .btn-sm {
            padding: 0.4rem 0.6rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-right: 5px;
        }
        .btn-edit {
            background: #667eea;
            color: white;
        }
        .btn-edit:hover {
            background: #5568d3;
            transform: scale(1.05);
        }
        .btn-delete {
            background: #f64e60;
            color: white;
        }
        .btn-delete:hover {
            background: #e63e50;
            transform: scale(1.05);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d5dce0;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-cancel {
            background: #f0f2f5;
            color: #333;
            padding: 10px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-cancel:hover {
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
        .form-section {
            background: #f5f7fb;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .form-section-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
        }
        .checkbox-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .tabs-container {
                flex-wrap: wrap;
            }
            .tab-button {
                flex: 1;
                min-width: 150px;
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
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript: void(0);"><span class="navbar-toggler-icon fe fe-menu"></span></a>
                        <!-- sidebar-toggle-->
                        <a class="logo-horizontal" href="index.php">
                            <img src="../assets/images/brand/CV_Logo.png" style="width:180px;height:50px;" class="header-brand-img desktop-logo" alt="logo">
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

            <!-- APP-SIDEBAR -->
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
                        <a href="user_management.php" class="side-menu__item">
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
                        <a href="programme_management.php" class="side-menu__item active">
                            <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 6h16V4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4v2h8v-2h4c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 12V6h16v12H4z"/></svg>
                            <span class="side-menu__label">Programme Management</span>
                        </a>
                    </li>
                </ul>
            </aside>
            <!-- /APP-SIDEBAR -->

            <!-- APP-CONTENT -->
            <div class="app-content main-content">
                <div class="side-app">
                    <div class="page-container">
                        <h2 style="margin-bottom: 30px; color: #333; font-weight: 700;">Programme & Course Management</h2>

                        <!-- Tab Navigation -->
                        <div class="tabs-container">
                            <button class="tab-button <?php echo $current_tab == 'programmes' ? 'active' : ''; ?>" onclick="switchTab('programmes')">
                                <i class="fe fe-book"></i> Programme Management
                            </button>
                            <button class="tab-button <?php echo $current_tab == 'courses' ? 'active' : ''; ?>" onclick="switchTab('courses')">
                                <i class="fe fe-layers"></i> Course Management
                            </button>
                            <button class="tab-button <?php echo $current_tab == 'mapping' ? 'active' : ''; ?>" onclick="switchTab('mapping')">
                                <i class="fe fe-link"></i> Course Mapping
                            </button>
                        </div>

                        <!-- PROGRAMMES TAB -->
                        <div id="programmes-tab" class="tab-content <?php echo $current_tab == 'programmes' ? 'active' : ''; ?>">
                            <?php if($programme_success): ?>
                                <div class="alert alert-success">
                                    <strong>✓</strong> <?php echo $programme_success; ?>
                                </div>
                            <?php endif; ?>
                            <?php if($programme_error): ?>
                                <div class="alert alert-danger">
                                    <strong>✗</strong> <?php echo $programme_error; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Add/Edit Form -->
                            <div class="card">
                                <div class="card-title">
                                    <?php echo $edit_programme ? 'Edit Programme' : 'Add New Programme'; ?>
                                </div>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="save_programme">
                                    <?php if($edit_programme): ?>
                                        <input type="hidden" name="id" value="<?php echo $edit_programme['id']; ?>">
                                    <?php endif; ?>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Programme ID/Code *</label>
                                            <input type="text" name="programme_id" required placeholder="Enter programme ID (e.g., MSTE)" value="<?php echo $edit_programme ? htmlspecialchars($edit_programme['programme_id']) : ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Programme Name *</label>
                                            <input type="text" name="programme_name" required placeholder="Enter programme name" value="<?php echo $edit_programme ? htmlspecialchars($edit_programme['programme_name']) : ''; ?>">
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn-submit">Save Programme</button>
                                        <?php if($edit_programme): ?>
                                            <a href="?tab=programmes" class="btn-cancel" style="text-decoration: none; display: inline-block;">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>

                            <!-- Programmes List -->
                            <div class="card">
                                <div class="card-title">
                                    All Programmes
                                </div>
                                <div style="overflow-x: auto;">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Programme ID</th>
                                                <th>Programme Name</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($programmes as $prog): ?>
                                                <tr>
                                                    <td><?php echo $prog['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($prog['programme_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($prog['programme_name']); ?></td>
                                                    <td>
                                                        <?php if($prog['approved'] == 1): ?>
                                                            <span style="color: green; font-weight: bold;">✓ Approved</span>
                                                        <?php else: ?>
                                                            <span style="color: orange; font-weight: bold;">⊙ Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="?tab=programmes&edit_programme=<?php echo $prog['id']; ?>" class="btn-sm btn-edit">Edit</a>
                                                        <a href="?action=delete_programme&id=<?php echo $prog['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- COURSES TAB -->
                        <div id="courses-tab" class="tab-content <?php echo $current_tab == 'courses' ? 'active' : ''; ?>">
                            <?php if($course_success): ?>
                                <div class="alert alert-success">
                                    <strong>✓</strong> <?php echo $course_success; ?>
                                </div>
                            <?php endif; ?>
                            <?php if($course_error): ?>
                                <div class="alert alert-danger">
                                    <strong>✗</strong> <?php echo $course_error; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Add/Edit Form -->
                            <div class="card">
                                <div class="card-title">
                                    <?php echo $edit_course ? 'Edit Course' : 'Add New Course'; ?>
                                </div>
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="save_course">
                                    <?php if($edit_course): ?>
                                        <input type="hidden" name="id" value="<?php echo $edit_course['id']; ?>">
                                    <?php endif; ?>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Course ID/Code *</label>
                                            <input type="text" name="course_id" required placeholder="Enter course ID (e.g., EXCEL-001)" value="<?php echo $edit_course ? htmlspecialchars($edit_course['course_id']) : ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>Course Name *</label>
                                            <input type="text" name="course_name" required placeholder="Enter course name" value="<?php echo $edit_course ? htmlspecialchars($edit_course['course_name']) : ''; ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="description" placeholder="Enter course description" rows="3"><?php echo $edit_course ? htmlspecialchars($edit_course['description'] ?? '') : ''; ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Course Image</label>
                                        <div style="display: flex; align-items: center; gap: 15px;">
                                            <div style="flex: 1;">
                                                <input type="file" name="course_image" accept="image/jpeg,image/png,image/gif,image/webp" placeholder="Upload course image">
                                                <small style="color: #666; display: block; margin-top: 5px;">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                                            </div>
                                            <?php if($edit_course && !empty($edit_course['image'])): ?>
                                                <div style="text-align: center;">
                                                    <img src="<?php echo htmlspecialchars('../' . $edit_course['image']); ?>" alt="Course Image" style="max-width: 100px; max-height: 100px; border-radius: 8px; border: 1px solid #ddd; padding: 5px;">
                                                    <small style="display: block; color: #666; margin-top: 5px;">Current Image</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn-submit">Save Course</button>
                                        <?php if($edit_course): ?>
                                            <a href="?tab=courses" class="btn-cancel" style="text-decoration: none; display: inline-block;">Cancel</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>

                            <!-- Courses List -->
                            <div class="card">
                                <div class="card-title">
                                    All Courses
                                </div>
                                <div style="overflow-x: auto;">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Course ID</th>
                                                <th>Course Name</th>
                                                <th>Image</th>
                                                <th>Description</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($courses as $course): ?>
                                                <tr>
                                                    <td><?php echo $course['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($course['course_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                                                    <td>
                                                        <?php if(!empty($course['image'])): ?>
                                                            <img src="<?php echo htmlspecialchars('../' . $course['image']); ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>" style="max-width: 50px; max-height: 50px; border-radius: 6px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <span style="color: #999; font-size: 12px;">No image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars(substr($course['description'] ?? '', 0, 50)); ?></td>
                                                    <td>
                                                        <a href="?tab=courses&edit_course=<?php echo $course['id']; ?>" class="btn-sm btn-edit">Edit</a>
                                                        <a href="?action=delete_course&id=<?php echo $course['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- MAPPING TAB -->
                        <div id="mapping-tab" class="tab-content <?php echo $current_tab == 'mapping' ? 'active' : ''; ?>">
                            <?php if($mapping_success): ?>
                                <div class="alert alert-success">
                                    <strong>✓</strong> <?php echo $mapping_success; ?>
                                </div>
                            <?php endif; ?>
                            <?php if($mapping_error): ?>
                                <div class="alert alert-danger">
                                    <strong>✗</strong> <?php echo $mapping_error; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Mapping Form -->
                            <div class="card">
                                <div class="card-title">
                                    Map Courses to Programmes
                                </div>
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="save_mapping">

                                    <div class="form-group">
                                        <label>Select Programme *</label>
                                        <select name="programme_id" id="programme_select" required onchange="loadMappedCourses()">
                                            <option value="">-- Select a programme --</option>
                                            <?php foreach($programmes as $prog): ?>
                                                <option value="<?php echo htmlspecialchars($prog['programme_id']); ?>">
                                                    <?php echo htmlspecialchars($prog['programme_id'] . ' - ' . $prog['programme_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-section">
                                        <div class="form-section-title">Available Courses</div>
                                        <div class="checkbox-list">
                                            <?php foreach($courses as $course): ?>
                                                <div class="checkbox-item">
                                                    <input type="checkbox" name="course_ids[]" value="<?php echo htmlspecialchars($course['course_id']); ?>" id="course_<?php echo $course['id']; ?>">
                                                    <label for="course_<?php echo $course['id']; ?>">
                                                        <?php echo htmlspecialchars($course['course_id'] . ' - ' . $course['course_name']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn-submit">Save Mapping</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Existing Mappings -->
                            <div class="card">
                                <div class="card-title">
                                    Programme-Course Mappings
                                </div>
                                <div style="overflow-x: auto;">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Programme ID</th>
                                                <th>Programme Name</th>
                                                <th>Mapped Courses</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            try {
                                                $mapping_query = $con->prepare("
                                                    SELECT DISTINCT 
                                                        pm.id,
                                                        pm.programme_id,
                                                        pm.programme_name
                                                    FROM programmes_master pm
                                                    ORDER BY pm.id DESC
                                                ");
                                                $mapping_query->execute();
                                                $prog_list = $mapping_query->fetchAll(PDO::FETCH_ASSOC);
                                                
                                                foreach($prog_list as $p) {
                                                    $course_query = $con->prepare("
                                                        SELECT c.course_id, c.course_name
                                                        FROM programme_courses_mapping pcm
                                                        JOIN courses_master c ON pcm.course_id = c.course_id
                                                        WHERE pcm.programme_id = ?
                                                    ");
                                                    $course_query->execute([$p['programme_id']]);
                                                    $mapped_courses = $course_query->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($p['programme_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($p['programme_name']); ?></td>
                                                        <td>
                                                            <?php 
                                                            if(count($mapped_courses) > 0) {
                                                                echo implode(', ', array_map(function($c) { 
                                                                    return htmlspecialchars($c['course_id']); 
                                                                }, $mapped_courses));
                                                            } else {
                                                                echo '<span style="color: #999; font-style: italic;">No courses mapped</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } catch(Exception $e) {
                                                echo '<tr><td colspan="3" class="text-center text-muted">Error loading mappings</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /APP-CONTENT -->
        </div>
    </div>

    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
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
    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
            
            // Update URL
            window.history.pushState(null, null, '?tab=' + tabName);
        }

        function loadMappedCourses() {
            const programmeId = document.getElementById('programme_select').value;
            
            // Uncheck all courses first
            document.querySelectorAll('input[name="course_ids[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            if(programmeId) {
                // Fetch mapped courses for this programme
                fetch('?action=get_mapped_courses&programme_id=' + encodeURIComponent(programmeId))
                    .then(response => response.json())
                    .then(data => {
                        if(data.success && data.mapped_courses && data.mapped_courses.length > 0) {
                            // Check the checkboxes for mapped courses
                            data.mapped_courses.forEach(courseId => {
                                const checkbox = document.querySelector('input[name="course_ids[]"][value="' + courseId + '"]');
                                if(checkbox) {
                                    checkbox.checked = true;
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading mapped courses:', error);
                    });
            }
        }
    </script>
</body>
</html>
