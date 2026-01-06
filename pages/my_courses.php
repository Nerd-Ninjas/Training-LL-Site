<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");

// Check if user is logged in
if(!isset($_SESSION["username"]) || empty($_SESSION["username"])) {
	header("Location: ../index.php");
	exit();
}

// Check if user is admin - redirect to admin dashboard
if(isset($_SESSION["userType"]) && $_SESSION["userType"] == 1) {
	header("Location: ../admin/index.php");
	exit();
}

$username = $_SESSION["username"];
$account = new Account($con);
$userDetails = $account->getUserDetails($username);
$firstName = $userDetails['firstName'] ?? 'User';
$lastName = $userDetails['lastName'] ?? '';
$email = $userDetails['email'] ?? '';
$avatarID = $userDetails['avatarID'] ?? 0;

// Get avatar
$filePath = '../assets/images/faces/6.jpg';
if($avatarID > 0) {
	$avatarQuery = $con->prepare("SELECT filePath FROM avatar WHERE id = :avatarID");
	$avatarQuery->bindValue(":avatarID", $avatarID);
	$avatarQuery->execute();
	if($avatarQuery->rowCount() == 1) {
		$avatarRow = $avatarQuery->fetch(PDO::FETCH_ASSOC);
		$filePath = $avatarRow['filePath'] ?? '../assets/images/faces/6.jpg';
	}
}

// Fetch all assigned courses
$userCourses = array();
try {
	$coursesQuery = $con->prepare("
		SELECT DISTINCT ubm.course_id, bm.batch_name, ubm.batch_unique_id, ubm.approvedDate
		FROM user_batch_mapping ubm
		LEFT JOIN batch_master bm ON ubm.batch_unique_id = bm.batch_unique_id
		WHERE ubm.username = ?
		AND ubm.course_id IS NOT NULL
		AND ubm.course_id != ''
		ORDER BY ubm.approvedDate DESC
	");
	$coursesQuery->execute([$username]);
	$userCourses = $coursesQuery->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
	error_log("Error fetching courses: " . $e->getMessage());
}
?>
<!doctype html>
<html lang="en" dir="ltr">

<head>
	<!-- META DATA -->
	<meta charset="UTF-8">
	<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="My Courses - Learnlike Training Dashboard">
	<meta name="author" content="Learnlike">
	<meta name="keywords" content="admin, dashboard, training, courses">
	<meta property="og:url" content="https://training.learnlike.in" />
	<meta property="og:type" content="article" />
	<meta property="og:title" content="My Courses - Learnlike Training Dashboard" />
	<meta property="og:description" content="" />
	<meta property="og:image" content="https://learnlike.in/assets/themes/pan/img/LL-logo-light-new.png" />

	<!-- FAVICON -->
	<link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/favicon.ico"/>

	<!-- TITLE -->
	<title>My Courses - Learnlike Training Dashboard</title>

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
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
			min-height: 100vh;
		}

		/* NAVBAR */
		.top-navbar {
			background: white;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
			padding: 12px 0;
			position: sticky;
			top: 0;
			z-index: 999;
		}

		.navbar-content {
			display: flex;
			justify-content: space-between;
			align-items: center;
			max-width: 1400px;
			margin: 0 auto;
			padding: 0 30px;
		}

		.navbar-logo img {
			height: 40px;
			width: auto;
		}

		.navbar-right {
			display: flex;
			align-items: center;
			gap: 20px;
		}

		.profile-dropdown {
			position: relative;
		}

		.profile-btn {
			display: flex;
			align-items: center;
			gap: 12px;
			background: none;
			border: none;
			cursor: pointer;
			padding: 6px 12px;
			border-radius: 8px;
			transition: background 0.3s;
		}

		.profile-btn:hover {
			background: #f0f0f0;
		}

		.profile-avatar {
			width: 36px;
			height: 36px;
			border-radius: 50%;
			object-fit: cover;
			border: 2px solid #e0e0e0;
		}

		.profile-name {
			font-weight: 600;
			color: #1a1a1a;
			font-size: 0.95rem;
		}

		.dropdown-content {
			position: absolute;
			top: 100%;
			right: 0;
			background: white;
			border: 1px solid #e0e0e0;
			border-radius: 8px;
			box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
			min-width: 200px;
			margin-top: 8px;
			display: none;
			z-index: 1000;
		}

		.dropdown-content.show {
			display: block;
		}

		.dropdown-content a {
			display: block;
			padding: 12px 16px;
			color: #1a1a1a;
			text-decoration: none;
			font-size: 0.95rem;
			border-bottom: 1px solid #f0f0f0;
			transition: background 0.2s;
		}

		.dropdown-content a:last-child {
			border-bottom: none;
		}

		.dropdown-content a:hover {
			background: #f7f9fa;
		}

		.dropdown-content svg {
			width: 18px;
			height: 18px;
			margin-right: 10px;
			display: inline-block;
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
			margin-top: 70px;
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

		/* DETAILS CARD */
		.details-card {
			border: 1px solid #e7eef7;
			border-radius: 8px;
			padding: 20px;
			background: white;
			box-shadow: 0 2px 8px rgba(0,0,0,0.08);
		}

		/* INFO SECTION */
		.info-section {
			margin-bottom: 30px;
		}

		.info-section:last-child {
			margin-bottom: 0;
		}

		.info-section-title {
			font-size: 17px;
			font-weight: 600;
			color: #333;
			margin-bottom: 15px;
			padding-bottom: 8px;
			border-bottom: 2px solid #e7eef7;
		}

		/* COURSES CONTAINER */
		.courses-container {
			display: flex;
			flex-direction: column;
			gap: 10px;
		}

		.course-item {
			display: grid;
			grid-template-columns: 1fr auto auto;
			gap: 15px;
			align-items: center;
			padding: 12px;
			border: 1px solid #f0f2f5;
			border-radius: 8px;
			transition: all 0.2s ease;
			background: #fafbfc;
		}

		.course-item:hover {
			background: white;
			border-color: #e7eef7;
			box-shadow: 0 2px 8px rgba(0,0,0,0.06);
		}

		/* COURSE HEADER INFO */
		.course-header-info {
			display: flex;
			align-items: center;
			gap: 12px;
		}

		.course-details-info {
			flex: 1;
		}

		.course-name {
			font-size: 15px;
			font-weight: 600;
			color: #333;
			margin: 0;
			word-break: break-word;
		}

		.course-batch-info {
			font-size: 12px;
			color: #999;
			margin: 2px 0 0 0;
		}

		/* COURSE META INFO */
		.course-meta-info {
			display: flex;
			gap: 15px;
			min-width: fit-content;
		}

		.course-meta-item {
			display: flex;
			flex-direction: column;
			align-items: flex-start;
		}

		.meta-label {
			font-size: 11px;
			color: #999;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: 0.4px;
			margin-bottom: 2px;
		}

		.meta-value {
			font-size: 13px;
			color: #333;
			font-weight: 500;
		}

		.meta-value.status-active {
			color: #10b981;
			font-weight: 600;
		}

		/* COURSE ACTION */
		.course-action {
			min-width: fit-content;
		}

		.btn-course-action {
			display: inline-block;
			padding: 10px 20px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			text-decoration: none;
			border-radius: 6px;
			font-weight: 600;
			font-size: 14px;
			transition: all 0.2s ease;
			border: none;
			cursor: pointer;
		}

		.btn-course-action:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
			color: white;
		}

		/* EMPTY STATE */
		.empty-state-container {
			padding: 60px 30px;
			text-align: center;
		}

		.empty-state {
			padding: 40px 20px;
		}

		.empty-state-icon {
			font-size: 4.5rem;
			margin-bottom: 20px;
			opacity: 0.3;
		}

		.empty-state h3 {
			font-size: 1.5rem;
			font-weight: 700;
			color: #2d3748;
			margin-bottom: 12px;
		}

		.empty-state p {
			font-size: 1rem;
			color: #718096;
			margin: 0;
		}

		/* PAGE CONTAINER */
		.page-container {
			padding: 20px;
		}

		/* RESPONSIVE */
		@media (max-width: 1024px) {
			.course-item {
				grid-template-columns: 1fr;
				gap: 12px;
			}

			.course-header-info {
				grid-column: 1;
			}

			.course-meta-info {
				grid-column: 1;
				gap: 30px;
			}

			.course-action {
				grid-column: 1;
			}

			.btn-course-action {
				width: 100%;
				text-align: center;
			}
		}

		@media (max-width: 768px) {
			.page-container {
				padding: 15px;
			}

			.details-card {
				padding: 20px;
			}

			.page-title {
				font-size: 1.4rem;
			}

			.course-item {
				grid-template-columns: 1fr;
				gap: 12px;
				padding: 12px;
			}

			.course-meta-info {
				flex-wrap: wrap;
				gap: 15px;
			}

			.info-section-title {
				font-size: 16px;
				margin-bottom: 15px;
				padding-bottom: 8px;
			}
		}

		@media (max-width: 480px) {
			.page-container {
				padding: 10px;
			}

			.details-card {
				padding: 15px;
				border-radius: 6px;
			}

			.page-title {
				font-size: 1.2rem;
			}

			.page-subtitle {
				font-size: 0.85rem;
			}

			.course-item {
				padding: 10px;
			}

			.course-header-info {
				gap: 10px;
			}

			.course-thumbnail-small {
				width: 50px;
				height: 50px;
			}

			.course-icon-small {
				font-size: 1.5rem;
			}

			.course-name {
				font-size: 14px;
			}

			.course-batch-info {
				font-size: 12px;
			}

			.btn-course-action {
				padding: 8px 16px;
				font-size: 13px;
			}

			.empty-state-icon {
				font-size: 3rem;
				margin-bottom: 15px;
			}

			.empty-state h3 {
				font-size: 1.2rem;
				margin-bottom: 8px;
			}

			.empty-state p {
				font-size: 0.9rem;
			}

		/* SIDEBAR TOGGLE STYLES */
		.sticky {
			position: relative;
		}

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

		/* Desktop - Sidebar visible by default */
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

		/* Tablet and Mobile - Sidebar hidden by default */
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

			/* Adjust content when sidebar is open */
			.page-main.sidebar-open {
				overflow: hidden;
			}
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
						<a class="logo-horizontal" href="../landing.php">
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
												<img src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-user" class="avatar profile-user brround cover-image">
											</a>
											<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
												<div class="drop-heading">
													<div class="text-center">
														<h5 class="text-dark mb-0 d-block"><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h5>
														<small class="text-muted">User Account</small>
													</div>
												</div>
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

			<!-- SIDEBAR -->
			<?php 
			// Create a simple sidebar for user pages
			$current_page = basename($_SERVER['PHP_SELF']);
			?>
			<div class="sticky">
				<div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
				<div class="app-sidebar">
					<div class="side-header">
						<a class="header-brand1" href="../landing.php">
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
							<li class="slide <?php echo ($current_page == 'my_courses.php') ? 'active' : ''; ?>">
								<a class="side-menu__item has-link" href="my_courses.php">
									<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
										<path d="M4 6h16V4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4v2h8v-2h4c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 12V6h16v12H4z"/>
									</svg>
									<span class="side-menu__label">My Courses</span>
								</a>
							</li>
							<li class="slide">
								<a class="side-menu__item has-link" href="../landing.php">
									<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
										<path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
									</svg>
									<span class="side-menu__label">Home</span>
								</a>
							</li>
							<li class="slide">
								<a class="side-menu__item has-link" href="../logout.php">
									<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
										<path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/>
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
				<!-- Page Header -->
				<div class="page-header-section">
					<h1 class="page-title">My Courses</h1>
					<p class="page-subtitle">Continue learning and enhance your skills with our professional courses</p>
				</div>

				<!-- Details Card -->
				<div class="details-card">
					<!-- Courses Display -->
					<?php if(!empty($userCourses)): ?>
						<!-- Courses Section -->
						<div class="info-section">
							<div class="info-section-title">Active Courses</div>
							
							<!-- Courses Table/List -->
							<div class="courses-container">
								<?php 
								$courseIndex = 0;
								foreach($userCourses as $course): 
								$courseIndex++;
								?>
									<div class="course-item">
										<div class="course-header-info">
											<div class="course-details-info">
												<h4 class="course-name"><?php echo htmlspecialchars($course['course_id']); ?></h4>
												<p class="course-batch-info"><?php echo htmlspecialchars($course['batch_name'] ?? 'Professional Training'); ?></p>
											</div>
										</div>

										<div class="course-meta-info">
											<?php if(!empty($course['approvedDate'])): ?>
												<div class="course-meta-item">
													<span class="meta-label">Enrolled</span>
													<span class="meta-value"><?php echo date('M d, Y', strtotime($course['approvedDate'])); ?></span>
												</div>
											<?php endif; ?>
											<div class="course-meta-item">
												<span class="meta-label">Status</span>
												<span class="meta-value status-active">Active</span>
											</div>
										</div>

										<div class="course-action">
											<a href="../main.php?ci=<?php echo urlencode($course['course_id']); ?>" class="btn-course-action">
												Continue Learning →
											</a>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php else: ?>
						<!-- Empty State -->
						<div class="empty-state-container">
							<div class="empty-state">
								<div class="empty-state-icon">📚</div>
								<h3>No Courses Yet</h3>
								<p>You don't have any courses assigned. Please contact your administrator to get started.</p>
							</div>
						</div>
					<?php endif; ?>
					</div>
				</div>
			</div>
			<!-- /APP-CONTENT -->
		</div>
	</div>
	</div>

	<!-- BACK-TO-TOP -->
	<a href="#top" id="back-to-top"><i class="fa fa-long-arrow-up"></i></a>

	<!-- JQUERY JS -->
	<script src="../assets/js/jquery.min.js"></script>

	<!-- BOOTSTRAP JS -->
	<script src="../assets/plugins/bootstrap/js/popper.min.js"></script>
	<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>

	<!-- CUSTOM JS -->
	<script src="../assets/js/custom.js"></script>

	<!-- Sidebar Toggle Script -->
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// Sidebar toggle functionality
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
			
			// Close sidebar when overlay is clicked
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

	<!-- Dropdown Toggle -->
	<script>
		function toggleDropdown() {
			var dropdown = document.getElementById('profileDropdown');
			if (dropdown) {
				dropdown.classList.toggle('show');
			}
		}

		// Close dropdown when clicking outside
		document.addEventListener('click', function(event) {
			var dropdown = document.getElementById('profileDropdown');
			var profileBtn = document.querySelector('.profile-btn');
			if (dropdown && profileBtn && !dropdown.contains(event.target) && !profileBtn.contains(event.target)) {
				dropdown.classList.remove('show');
			}
		});

		// Hide loader on page load
		window.addEventListener('load', function() {
			var loader = document.getElementById('global-loader');
			if(loader) {
				loader.style.display = 'none';
			}
		});
	</script>

</body>

</html>
