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

$account = new Account($con);
$username = $_SESSION["username"];

// Get user details
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

// Messages
$success_msg = '';
$error_msg = '';

// ========== HANDLE BATCH CREATION ==========
if(isset($_POST['action']) && $_POST['action'] == 'create_batch') {
    $batch_name = isset($_POST['batch_name']) ? FormSanitizer::sanitizeFormString($_POST['batch_name']) : '';
    $batch_unique_id = isset($_POST['batch_unique_id']) ? FormSanitizer::sanitizeFormString($_POST['batch_unique_id']) : '';
    $programme_id = isset($_POST['programme_id']) ? FormSanitizer::sanitizeFormString($_POST['programme_id']) : '';
    $programme_start_date = isset($_POST['programme_start_date']) ? FormSanitizer::sanitizeFormString($_POST['programme_start_date']) : '';
    $programme_end_date = isset($_POST['programme_end_date']) ? FormSanitizer::sanitizeFormString($_POST['programme_end_date']) : '';
    $total_duration = isset($_POST['total_duration']) ? intval($_POST['total_duration']) : 0;
    $no_of_days = isset($_POST['no_of_days']) ? intval($_POST['no_of_days']) : 0;
    $vendor_id = isset($_POST['vendor_id']) ? intval($_POST['vendor_id']) : 0;
    $mode = isset($_POST['mode']) ? intval($_POST['mode']) : 0;

    if(empty($batch_name) || empty($batch_unique_id) || empty($programme_id) || empty($programme_start_date) || empty($programme_end_date)) {
        $error_msg = "All required fields must be filled";
    } else {
        try {
            // Check if batch_unique_id already exists
            $check = $con->prepare("SELECT id FROM batch_master WHERE batch_unique_id = ?");
            $check->execute([$batch_unique_id]);
            
            if($check->rowCount() > 0) {
                $error_msg = "Batch ID already exists";
            } else {
                $insert = $con->prepare("INSERT INTO batch_master (batch_name, batch_unique_id, programme_id, programme_start_date, programme_end_date, no_of_days, total_duration, vendor_id, mode, approvedBy, approvedDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                
                if($insert->execute([$batch_name, $batch_unique_id, $programme_id, $programme_start_date, $programme_end_date, $no_of_days, $total_duration, $vendor_id, $mode, $_SESSION['username']])) {
                    $success_msg = "Batch created successfully!";
                } else {
                    $error_msg = "Failed to create batch";
                }
            }
        } catch(Exception $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}

// ========== HANDLE ADD STUDENTS TO BATCH ==========
if(isset($_POST['action']) && $_POST['action'] == 'add_students') {
    $batch_unique_id = isset($_POST['batch_unique_id']) ? FormSanitizer::sanitizeFormString($_POST['batch_unique_id']) : '';
    $students = isset($_POST['students']) ? $_POST['students'] : array();

    if(empty($batch_unique_id) || empty($students)) {
        $error_msg = "Please select a batch and students";
    } else {
        try {
            // Get course_id from batch_master
            $batch_info = $con->prepare("SELECT programme_id FROM batch_master WHERE batch_unique_id = ?");
            $batch_info->execute([$batch_unique_id]);
            
            if($batch_info->rowCount() == 0) {
                $error_msg = "Batch not found";
            } else {
                $batch_data = $batch_info->fetch(PDO::FETCH_ASSOC);
                $course_id = $batch_data['programme_id'];
                
                $added = 0;
                $already_exist = 0;
                
                foreach($students as $username) {
                    $username = FormSanitizer::sanitizeFormString($username);
                    
                    // Check if already mapped
                    $check = $con->prepare("SELECT id FROM user_batch_mapping WHERE username = ? AND batch_unique_id = ?");
                    $check->execute([$username, $batch_unique_id]);
                    
                    if($check->rowCount() > 0) {
                        $already_exist++;
                    } else {
                        // Add to user_batch_mapping
                        $insert = $con->prepare("INSERT INTO user_batch_mapping (username, batch_unique_id, course_id, approvedBy, approvedDate) VALUES (?, ?, ?, ?, NOW())");
                        if($insert->execute([$username, $batch_unique_id, $course_id, $_SESSION['username']])) {
                            $added++;
                        }
                    }
                }
                
                if($added > 0) {
                    $success_msg = "$added student(s) added to batch successfully!";
                    if($already_exist > 0) {
                        $success_msg .= " ($already_exist already existed)";
                    }
                } else {
                    $error_msg = "No students were added. All selected students already exist in this batch.";
                }
            }
        } catch(Exception $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}

// ========== HANDLE ASSIGN PROGRAMME TO BATCH ==========
if(isset($_POST['action']) && $_POST['action'] == 'assign_batch_programme') {
    $batch_unique_id = isset($_POST['batch_unique_id']) ? FormSanitizer::sanitizeFormString($_POST['batch_unique_id']) : '';
    $programme_id = isset($_POST['programme_id']) ? intval($_POST['programme_id']) : 0;

    if(empty($batch_unique_id) || $programme_id <= 0) {
        $error_msg = "Please select a batch and programme";
    } else {
        try {
            // Get all students in the batch
            $students = $con->prepare("SELECT DISTINCT ubm.username, u.id FROM user_batch_mapping ubm JOIN users u ON ubm.username = u.username WHERE ubm.batch_unique_id = ?");
            $students->execute([$batch_unique_id]);
            $student_list = $students->fetchAll(PDO::FETCH_ASSOC);

            if(empty($student_list)) {
                $error_msg = "No students found in this batch";
            } else {
                $assigned = 0;
                $already_assigned = 0;

                foreach($student_list as $student) {
                    $username = $student['username'];
                    
                    // Check if already assigned this course in user_batch_mapping
                    $check = $con->prepare("SELECT id FROM user_batch_mapping WHERE username = ? AND batch_unique_id = ? AND course_id = ?");
                    $check->execute([$username, $batch_unique_id, $programme_id]);
                    
                    if($check->rowCount() > 0) {
                        $already_assigned++;
                    } else {
                        // Update course_id in user_batch_mapping for this batch
                        $assign = $con->prepare("UPDATE user_batch_mapping SET course_id = ?, approvedBy = ?, approvedDate = NOW() WHERE username = ? AND batch_unique_id = ?");
                        if($assign->execute([$programme_id, $_SESSION['username'], $username, $batch_unique_id])) {
                            $assigned++;
                        }
                    }
                }

                if($assigned > 0) {
                    $success_msg = "Programme assigned to $assigned student(s) in the batch successfully!";
                    if($already_assigned > 0) {
                        $success_msg .= " ($already_assigned already had this programme)";
                    }
                } else {
                    $error_msg = "No students were assigned. All students already have this programme assigned.";
                }
            }
        } catch(Exception $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch all batches
$batches = array();
try {
    $batch_query = $con->prepare("SELECT id, batch_name, batch_unique_id, programme_id, programme_start_date, programme_end_date FROM batch_master ORDER BY batch_name ASC");
    $batch_query->execute();
    $batches = $batch_query->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $batches = array();
}

// Fetch all programmes for dropdown
$programmes = array();
try {
    $prog_query = $con->prepare("SELECT id, programme_id, programme_name FROM programmes_master WHERE approved = 1 ORDER BY programme_name");
    $prog_query->execute();
    $programmes = $prog_query->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $programmes = array();
}

// Fetch all users for student selection
$all_users = array();
try {
    $users_query = $con->prepare("SELECT id, username, firstName, lastName, email FROM users ORDER BY firstName ASC");
    $users_query->execute();
    $all_users = $users_query->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $all_users = array();
}

// Fetch batch students count
$batch_students = array();
try {
    $count_query = $con->prepare("SELECT batch_unique_id, COUNT(*) as count FROM user_batch_mapping GROUP BY batch_unique_id");
    $count_query->execute();
    $results = $count_query->fetchAll(PDO::FETCH_ASSOC);
    foreach($results as $row) {
        $batch_students[$row['batch_unique_id']] = $row['count'];
    }
} catch(Exception $e) {
    $batch_students = array();
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Batch Management">
    <meta name="author" content="Learnlike">
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/LL-logo-light.png"/>
    <title>Batch Management - Learnlike's Training</title>
    <link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link href="../assets/css/style.css" rel="stylesheet" />
    <link href="../assets/css/icons.css" rel="stylesheet" />
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
            padding: 1rem;
            border: 1px solid #e7eef7;
            color: #2d3748;
        }

        .table tbody tr:hover {
            background-color: #fafbfc;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e7eef7;
            padding: 0.75rem;
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background: white;
            border-bottom: 1px solid #e7eef7;
        }

        .modal-title {
            color: #2d3748;
            font-weight: 700;
        }

        /* Student List Styling */
        .students-list-container {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #e7eef7;
            border-radius: 8px;
            background-color: #fafbfc;
        }

        .student-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem;
            border-bottom: 1px solid #e7eef7;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: white;
        }

        .student-item:last-child {
            border-bottom: none;
        }

        .student-item:hover {
            background-color: #f0f4ff;
            border-bottom-color: #d7dff7;
        }

        .student-item input[type="checkbox"] {
            margin-right: 1rem;
            margin-top: 0.25rem;
            cursor: pointer;
            accent-color: #667eea;
            width: 18px;
            height: 18px;
        }

        .student-content {
            flex: 1;
        }

        .student-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .student-username {
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .student-email {
            font-size: 0.80rem;
            color: #95a5b6;
        }

        #noStudentsMessage {
            color: #95a5b6;
        }

        @media (max-width: 768px) {
            .students-list-container {
                max-height: 300px;
            }

            .table thead th, .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }

            .page-title {
                font-size: 1.35rem;
            }
        }
    </style>
</head>
<body class="ltr app sidebar-mini">
    <div class="page">
        <div class="page-main">
            <!-- APP-Header -->
            <div class="hor-header header">
                <div class="container main-container">
                    <div class="d-flex">
                        <a aria-label="Hide Sidebar" class="app-sidebar__toggle" data-bs-toggle="sidebar" href="javascript:void(0)"></a>
                        <a class="logo-horizontal" href="index.php">
                            <img src="../assets/images/brand/logo.png" class="header-brand-img desktop-logo" alt="logo">
                            <img src="../assets/images/brand/logo-3.png" class="header-brand-img light-logo1" alt="logo">
                        </a>
                        <!-- Profile -->
                        <div class="dropdown d-md-flex profile-1 d-flex ms-auto">
                            <a href="#" data-bs-toggle="dropdown" class="nav-link pe-2 leading-none d-flex animate">
                                <span>
                                    <img src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-user" class="avatar profile-user brround cover-image">
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <a class="dropdown-item" href="../logout.php">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-inner-icn" viewBox="0 0 24 24"><path d="M10.6523438,16.140625c-0.09375,0.09375-0.1464233,0.2208862-0.1464233,0.3534546c0,0.276123,0.2238159,0.5,0.499939,0.500061c0.1326294,0.0001221,0.2598267-0.0525513,0.3534546-0.1464844l4.4941406-4.4941406c0.000061-0.000061,0.0001221-0.000061,0.0001831-0.0001221c0.1951294-0.1952515,0.1950684-0.5117188-0.0001831-0.7068481L11.359314,7.1524048c-0.1937256-0.1871338-0.5009155-0.1871338-0.6947021,0c-0.1986084,0.1918335-0.2041016,0.5083618-0.0122681,0.7069702L14.2930298,11.5H2.5C2.223877,11.5,2,11.723877,2,12s0.223877,0.5,0.5,0.5h11.7930298L10.6523438,16.140625z M16.4199829,3.0454102C11.4741821,0.5905762,5.4748535,2.6099243,3.0200195,7.5556641C2.8970337,7.8029175,2.9978027,8.1030884,3.2450562,8.2260742C3.4923706,8.3490601,3.7925415,8.248291,3.9155273,8.0010376c0.8737793-1.7612305,2.300354-3.1878052,4.0615845-4.0615845C12.428833,1.730835,17.828064,3.5492554,20.0366821,8.0010376c2.2085571,4.4517212,0.3901367,9.8509521-4.0615845,12.0595703c-4.4517212,2.2085571-9.8510132,0.3901367-12.0595703-4.0615845c-0.1229858-0.2473145-0.4231567-0.3480835-0.6704102-0.2250977c-0.2473145,0.1229858-0.3480835,0.4230957-0.2250977,0.6704102c1.6773682,3.4109497,5.1530762,5.5667114,8.9541016,5.5537109c3.7976685,0.0003662,7.2676392-2.1509399,8.9560547-5.5526733C23.3850098,11.4996338,21.3657227,5.5002441,16.4199829,3.0454102z"/></svg>
                                    Log out
                                </a>
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

                    <!-- PAGE-HEADER -->
                    <div class="page-header">
                        <div class="page-leftheader">
                            <h4 class="page-title">Batch Management</h4>
                            <ol class="breadcrumb pt-0">
                                <li class="breadcrumb-item"><a href="index.php">Admin</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Batch Management</li>
                            </ol>
                        </div>
                        <div class="page-rightheader">
                            <div class="btn-list">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBatchModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                    Create Batch
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- /PAGE-HEADER -->

                    <!-- ALERTS -->
                    <?php if($success_msg): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> <?php echo $success_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if($error_msg): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> <?php echo $error_msg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <!-- /ALERTS -->

                    <!-- BATCHES LIST -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Batches</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Batch Name</th>
                                            <th>Batch ID</th>
                                            <th>Programme</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Students</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($batches)): ?>
                                            <?php foreach($batches as $batch): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($batch['id']); ?></td>
                                                    <td>
                                                        <a href="batch_details.php?batch_id=<?php echo htmlspecialchars($batch['batch_unique_id']); ?>" class="text-primary" style="text-decoration: none;">
                                                            <strong><?php echo htmlspecialchars($batch['batch_name']); ?></strong>
                                                        </a>
                                                    </td>
                                                    <td><strong><?php echo htmlspecialchars($batch['batch_unique_id']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($batch['programme_id']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($batch['programme_start_date'])); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($batch['programme_end_date'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-info"><?php echo isset($batch_students[$batch['batch_unique_id']]) ? $batch_students[$batch['batch_unique_id']] : 0; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#addStudentsModal" onclick="setBatchForStudents('<?php echo htmlspecialchars($batch['batch_unique_id']); ?>', '<?php echo htmlspecialchars($batch['batch_name']); ?>')">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                                                Add Students
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#assignProgrammeModal" onclick="setBatchForProgramme('<?php echo htmlspecialchars($batch['batch_unique_id']); ?>', '<?php echo htmlspecialchars($batch['batch_name']); ?>')">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6-6 6 6M4 11v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path></svg>
                                                                Assign Programme
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="8" class="text-center text-muted">No batches found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /BATCHES LIST -->

                </div>
            </div>
            <!-- /APP-CONTENT -->
        </div>
    </div>

    <!-- CREATE BATCH MODAL -->
    <div class="modal fade" id="createBatchModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Batch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_batch">
                        
                        <div class="mb-3">
                            <label for="batch_name" class="form-label">Batch Name *</label>
                            <input type="text" class="form-control" id="batch_name" name="batch_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="batch_unique_id" class="form-label">Batch ID *</label>
                            <input type="text" class="form-control" id="batch_unique_id" name="batch_unique_id" placeholder="e.g., AIJMB0001" required>
                        </div>

                        <div class="mb-3">
                            <label for="programme_id" class="form-label">Programme *</label>
                            <select class="form-select" id="programme_id" name="programme_id" required>
                                <option value="">-- Select Programme --</option>
                                <?php foreach($programmes as $prog): ?>
                                    <option value="<?php echo htmlspecialchars($prog['programme_id']); ?>">
                                        <?php echo htmlspecialchars($prog['programme_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="programme_start_date" class="form-label">Start Date *</label>
                                    <input type="date" class="form-control" id="programme_start_date" name="programme_start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="programme_end_date" class="form-label">End Date *</label>
                                    <input type="date" class="form-control" id="programme_end_date" name="programme_end_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="no_of_days" class="form-label">Number of Days</label>
                                    <input type="number" class="form-control" id="no_of_days" name="no_of_days" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="total_duration" class="form-label">Total Duration (hours)</label>
                                    <input type="number" class="form-control" id="total_duration" name="total_duration" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vendor_id" class="form-label">Vendor ID</label>
                                    <input type="number" class="form-control" id="vendor_id" name="vendor_id" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mode" class="form-label">Mode</label>
                                    <select class="form-select" id="mode" name="mode">
                                        <option value="0">Online</option>
                                        <option value="1">Hybrid</option>
                                        <option value="2">Offline</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Batch</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /CREATE BATCH MODAL -->

    <!-- ADD STUDENTS MODAL -->
    <div class="modal fade" id="addStudentsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Students to <span id="batchNameDisplay"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_students">
                        <input type="hidden" id="modal_batch_id" name="batch_unique_id">

                        <!-- Search Bar -->
                        <div class="mb-3">
                            <input type="text" class="form-control" id="studentSearchInput" placeholder="Search by name or username...">
                            <small class="form-text text-muted">Start typing to filter students</small>
                        </div>

                        <!-- Students List with Custom Styling -->
                        <div class="students-list-container">
                            <div id="studentsList">
                                <?php foreach($all_users as $user): ?>
                                    <label class="student-item">
                                        <input type="checkbox" name="students[]" value="<?php echo htmlspecialchars($user['username']); ?>" class="student-checkbox">
                                        <div class="student-content">
                                            <div class="student-name"><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></div>
                                            <div class="student-username">@<?php echo htmlspecialchars($user['username']); ?></div>
                                            <div class="student-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div id="noStudentsMessage" class="text-center text-muted py-4" style="display: none;">
                                <p>No students found</p>
                            </div>
                        </div>

                        <!-- Selected Count -->
                        <div class="mt-3">
                            <small class="text-muted">
                                <strong id="selectedCount">0</strong> student(s) selected
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Students</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /ADD STUDENTS MODAL -->

    <script>
        // Search functionality for students
        document.getElementById('studentSearchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const studentItems = document.querySelectorAll('.student-item');
            let visibleCount = 0;
            
            studentItems.forEach(item => {
                const name = item.querySelector('.student-name').textContent.toLowerCase();
                const username = item.querySelector('.student-username').textContent.toLowerCase();
                const email = item.querySelector('.student-email').textContent.toLowerCase();
                
                if(name.includes(searchTerm) || username.includes(searchTerm) || email.includes(searchTerm)) {
                    item.style.display = 'flex';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            document.getElementById('noStudentsMessage').style.display = visibleCount === 0 ? 'block' : 'none';
        });

        // Update selected count
        document.getElementById('studentsList').addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = checkedBoxes;
        });
    </script>

    <!-- ASSIGN PROGRAMME MODAL -->
    <div class="modal fade" id="assignProgrammeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Programme to <span id="batchNameDisplay2"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="assign_batch_programme">
                        <input type="hidden" id="modal_batch_id_2" name="batch_unique_id">

                        <div class="mb-3">
                            <label for="programme_select" class="form-label">Select Programme *</label>
                            <select class="form-select" id="programme_select" name="programme_id" required>
                                <option value="">-- Select Programme --</option>
                                <?php foreach($programmes as $prog): ?>
                                    <option value="<?php echo htmlspecialchars($prog['id']); ?>">
                                        <?php echo htmlspecialchars($prog['programme_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <strong>Note:</strong> This will assign the selected programme to all students in this batch.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Programme</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /ASSIGN PROGRAMME MODAL -->

    <!-- Scripts -->
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        function setBatchForStudents(batchId, batchName) {
            document.getElementById('modal_batch_id').value = batchId;
            document.getElementById('batchNameDisplay').textContent = batchName;
        }

        function setBatchForProgramme(batchId, batchName) {
            document.getElementById('modal_batch_id_2').value = batchId;
            document.getElementById('batchNameDisplay2').textContent = batchName;
        }
    </script>
</body>
</html>
