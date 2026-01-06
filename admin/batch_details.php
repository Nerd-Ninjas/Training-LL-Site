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

// Check if batch_id parameter exists
if(!isset($_GET['batch_id']) || empty($_GET['batch_id'])) {
    header("Location: batch_management.php");
    exit;
}

$batch_id = FormSanitizer::sanitizeFormString($_GET['batch_id']);

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

// Fetch batch details
$batch_details = null;
try {
    $batch_query = $con->prepare("SELECT * FROM batch_master WHERE batch_unique_id = ?");
    $batch_query->execute([$batch_id]);
    if($batch_query->rowCount() > 0) {
        $batch_details = $batch_query->fetch(PDO::FETCH_ASSOC);
    } else {
        header("Location: batch_management.php?error=Batch not found");
        exit;
    }
} catch(Exception $e) {
    header("Location: batch_management.php?error=Error fetching batch");
    exit;
}

// Fetch students in this batch
$students = array();
try {
    $students_query = $con->prepare("
        SELECT u.id, u.username, u.firstName, u.lastName, u.email, u.mobileNumber, u.approved, ubm.approvedDate, ubm.approvedBy
        FROM users u
        INNER JOIN user_batch_mapping ubm ON u.username = ubm.username
        WHERE ubm.batch_unique_id = ?
        ORDER BY u.firstName ASC
    ");
    $students_query->execute([$batch_id]);
    $students = $students_query->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $students = array();
}

// Fetch programme information
$programme_info = null;
try {
    $prog_query = $con->prepare("
        SELECT * FROM programmes_master 
        WHERE programme_id = ?
    ");
    $prog_query->execute([$batch_details['programme_id']]);
    if($prog_query->rowCount() > 0) {
        $programme_info = $prog_query->fetch(PDO::FETCH_ASSOC);
    }
} catch(Exception $e) {
    $programme_info = null;
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Batch Details">
    <meta name="author" content="Learnlike">
    <link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/LL-logo-light.png"/>
    <title><?php echo htmlspecialchars($batch_details['batch_name']); ?> - Batch Details</title>
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

        .batch-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .info-box {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            border: 1px solid #e7eef7;
            transition: all 0.2s ease;
        }

        .info-box:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .info-label {
            color: #718096;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.75rem;
            letter-spacing: 0.5px;
            display: block;
        }

        .info-value {
            color: #2d3748;
            font-size: 1.1rem;
            font-weight: 600;
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

        .status-badge-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .mode-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }

        @media (max-width: 768px) {
            .table thead th, .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }

            .page-title {
                font-size: 1.35rem;
            }

            .batch-info-grid {
                grid-template-columns: 1fr;
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
                            <h4 class="page-title"><?php echo htmlspecialchars($batch_details['batch_name']); ?></h4>
                            <ol class="breadcrumb pt-0">
                                <li class="breadcrumb-item"><a href="index.php">Admin</a></li>
                                <li class="breadcrumb-item"><a href="batch_management.php">Batch Management</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Batch Details</li>
                            </ol>
                        </div>
                        <div class="page-rightheader">
                            <div class="btn-list">
                                <a href="batch_management.php" class="btn btn-secondary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"></path></svg>
                                    Back to Batches
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- /PAGE-HEADER -->

                    <!-- BATCH INFORMATION CARDS -->
                    <div class="batch-info-grid">
                        <div class="info-box">
                            <span class="info-label">Batch ID</span>
                            <div class="info-value"><?php echo htmlspecialchars($batch_details['batch_unique_id']); ?></div>
                        </div>

                        <div class="info-box">
                            <span class="info-label">Programme</span>
                            <div class="info-value"><?php echo htmlspecialchars($batch_details['programme_id']); ?></div>
                        </div>

                        <div class="info-box">
                            <span class="info-label">Start Date</span>
                            <div class="info-value"><?php echo date('M d, Y', strtotime($batch_details['programme_start_date'])); ?></div>
                        </div>

                        <div class="info-box">
                            <span class="info-label">End Date</span>
                            <div class="info-value"><?php echo date('M d, Y', strtotime($batch_details['programme_end_date'])); ?></div>
                        </div>

                        <div class="info-box">
                            <span class="info-label">Total Duration</span>
                            <div class="info-value"><?php echo htmlspecialchars($batch_details['total_duration']); ?> hours</div>
                        </div>

                        <div class="info-box">
                            <span class="info-label">Number of Days</span>
                            <div class="info-value"><?php echo htmlspecialchars($batch_details['no_of_days']); ?> days</div>
                        </div>

                        <div class="info-box">
                            <span class="info-label">Mode</span>
                            <div class="info-value">
                                <?php 
                                    $mode_text = 'Unknown';
                                    $mode_badge = 'secondary';
                                    if($batch_details['mode'] == 0) {
                                        $mode_text = 'Online';
                                        $mode_badge = 'info';
                                    }
                                    elseif($batch_details['mode'] == 1) {
                                        $mode_text = 'Hybrid';
                                        $mode_badge = 'warning';
                                    }
                                    elseif($batch_details['mode'] == 2) {
                                        $mode_text = 'Offline';
                                        $mode_badge = 'success';
                                    }
                                ?>
                                <span class="badge bg-<?php echo $mode_badge; ?>"><?php echo htmlspecialchars($mode_text); ?></span>
                            </div>
                        </div>

                        <div class="info-box">
                            <span class="info-label">Vendor ID</span>
                            <div class="info-value"><?php echo htmlspecialchars($batch_details['vendor_id']); ?></div>
                        </div>

                        <div class="info-box">
                            <span class="info-label">Total Students</span>
                            <div class="info-value" style="color: #667eea; font-weight: 700;"><?php echo count($students); ?></div>
                        </div>
                    </div>
                    <!-- /BATCH INFORMATION CARDS -->

                    <!-- PROGRAMME DETAILS -->
                    <?php if($programme_info): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Programme Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Programme Name:</strong> <?php echo htmlspecialchars($programme_info['programme_name']); ?></p>
                                        <p><strong>Programme ID:</strong> <?php echo htmlspecialchars($programme_info['programme_id']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Status:</strong> 
                                            <?php if($programme_info['approved'] == 1): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php endif; ?>
                                        </p>
                                        <p><strong>Approved By:</strong> <?php echo htmlspecialchars($programme_info['approvedBy']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- /PROGRAMME DETAILS -->

                    <!-- STUDENTS LIST -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Students in This Batch (<?php echo count($students); ?>)</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Mobile</th>
                                            <th>Status</th>
                                            <th>Added By</th>
                                            <th>Added Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(!empty($students)): ?>
                                            <?php foreach($students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['firstName']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['lastName']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($student['mobileNumber']); ?></td>
                                                    <td>
                                                        <?php if($student['approved'] == 1): ?>
                                                            <span class="badge status-badge-approved">Approved</span>
                                                        <?php else: ?>
                                                            <span class="badge status-badge-pending">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($student['approvedBy']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($student['approvedDate'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="9" class="text-center text-muted">No students found in this batch</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /STUDENTS LIST -->

                </div>
            </div>
            <!-- /APP-CONTENT -->
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
