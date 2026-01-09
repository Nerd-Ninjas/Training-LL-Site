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
$id = $userDetails['id'] ?? '';
$firstName = $userDetails['firstName'] ?? 'User';
$lastName = $userDetails['lastName'] ?? '';
$dob = $userDetails['dob'] ?? '';
$gender = $userDetails['gender'] ?? '';
$mobileNumber = $userDetails['mobileNumber'] ?? '';
$email = $userDetails['email'] ?? '';
$password = $userDetails['password'] ?? '';
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
?>
<!doctype html>
<html lang="en" dir="ltr">

<head>
	<!-- META DATA -->
	<meta charset="UTF-8">
	<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="description" content="My Profile - Learnlike Training Dashboard">
	<meta name="author" content="Learnlike">
	<meta name="keywords" content="admin, dashboard, training, profile">
	<meta property="og:url" content="https://training.learnlike.in" />
	<meta property="og:type" content="article" />
	<meta property="og:title" content="My Profile - Learnlike Training Dashboard" />
	<meta property="og:description" content="" />
	<meta property="og:image" content="https://learnlike.in/assets/themes/pan/img/LL-logo-light-new.png" />

	<!-- FAVICON -->
	<link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/favicon.ico"/>

	<!-- TITLE -->
	<title>My Profile - Learnlike Training Dashboard</title>

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
		/* PROFILE CONTENT STYLES */
		:root{--purple:#6b46c1;--purple-light:#f3e8ff;--bg:#f5f5f7;--white:#ffffff;--text:#374151;--muted:#9ca3af;--border:#e5e7eb}

		/* breadcrumb */
		.breadcrumb-section{display:flex;align-items:center;gap:12px;margin-bottom:20px;font-size:13px;margin-top:20px}
		.breadcrumb-section a{color:var(--purple);text-decoration:none;cursor:pointer}
		.breadcrumb-section .sep{color:var(--muted)}
		
		/* page title */
		.profile-page-title{font-size:28px;font-weight:700;margin-bottom:24px;color:#1f2937}
		
		/* card */
		.profile-card-container{background:var(--white);border-radius:12px;border:1px solid var(--border);padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.05)}
		
		/* profile header card */
		.profile-header{display:flex;gap:24px;align-items:flex-start}
		.profile-avatar-img{width:120px;height:120px;border-radius:50%;overflow:hidden;flex-shrink:0;border:4px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
		.profile-avatar-img img{width:100%;height:100%;object-fit:cover}
		.profile-info-section{flex:1}
		.profile-name-text{margin:0;font-size:22px;font-weight:700;color:#1f2937}
		.profile-role-text{color:var(--purple);font-weight:600;margin-top:4px}
		.profile-subtitle-text{color:var(--muted);font-size:14px;margin-top:8px}
		
		/* form grid */
		.profile-form-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:16px}
		.profile-form-group{display:flex;flex-direction:column}
		.profile-form-label{font-size:12px;color:var(--muted);text-transform:uppercase;font-weight:600;margin-bottom:8px;letter-spacing:0.5px}
		.profile-form-value{font-size:15px;font-weight:600;color:#1f2937}
		
		/* section header */
		.profile-section-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
		.profile-section-title{font-size:18px;font-weight:700;color:#1f2937}
		.profile-edit-btn{background:var(--white);border:1px solid var(--border);color:var(--purple);padding:8px 16px;border-radius:8px;cursor:pointer;font-weight:600;font-size:13px;transition:all 0.2s}
		.profile-edit-btn:hover{background:var(--purple-light)}
		
		/* responsive */
		@media (max-width:900px){.profile-form-grid{grid-template-columns:repeat(1,1fr)}}

		/* SIDEBAR STYLES */
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

			.page-main.sidebar-open {
				overflow: hidden;
			}
		}

		/* SIDEBAR MENU STYLES */
		.side-menu__icon {
			width: 24px;
			height: 24px;
			fill: currentColor;
			stroke: currentColor;
		}
		
		.side-menu__icon path {
			fill: currentColor;
		}
		.page-container {
			padding: 20px;
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
							<h1 class="profile-page-title">My Profile</h1>
						</div>

						<!-- Profile Card -->
						<div class="profile-card-container">
							<div class="profile-header">
								<div class="profile-avatar-img">
									<img id="avatar" src="<?php echo htmlspecialchars($filePath); ?>" alt="profile-avatar">
								</div>
								<div class="profile-info-section">
									<h2 class="profile-name-text" id="name"><?php echo htmlspecialchars($firstName . " " . $lastName); ?></h2>
									<div class="profile-role-text" id="role">User</div>
									<div class="profile-subtitle-text" id="location">Member</div>
									
									<div class="profile-form-grid" style="margin-top:20px;grid-template-columns:repeat(2,1fr)">
										<div class="profile-form-group">
											<div class="profile-form-label">Email Address</div>
											<div class="profile-form-value" id="email"><?php echo htmlspecialchars($email); ?></div>
										</div>
										<div class="profile-form-group">
											<div class="profile-form-label">Username</div>
											<div class="profile-form-value" id="username"><?php echo htmlspecialchars($username); ?></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Personal Information Card -->
						<div class="profile-card-container">
							<div class="profile-section-header">
								<h3 class="profile-section-title">Personal Information</h3>
								<button class="profile-edit-btn">Edit</button>
							</div>
							<div class="profile-form-grid">
								<div class="profile-form-group">
									<div class="profile-form-label">ID</div>
									<div class="profile-form-value" id="userId"><?php echo htmlspecialchars($id); ?></div>
								</div>
								<div class="profile-form-group">
									<div class="profile-form-label">First Name</div>
									<div class="profile-form-value" id="firstName"><?php echo htmlspecialchars($firstName); ?></div>
								</div>
								<div class="profile-form-group">
									<div class="profile-form-label">Last Name</div>
									<div class="profile-form-value" id="lastName"><?php echo htmlspecialchars($lastName); ?></div>
								</div>
								<div class="profile-form-group">
									<div class="profile-form-label">Date of Birth</div>
									<div class="profile-form-value" id="dob"><?php echo htmlspecialchars($dob); ?></div>
								</div>
								<div class="profile-form-group">
									<div class="profile-form-label">Gender</div>
									<div class="profile-form-value" id="gender"><?php echo htmlspecialchars($gender); ?></div>
								</div>
								<div class="profile-form-group">
									<div class="profile-form-label">Username</div>
									<div class="profile-form-value" id="usernameInfo"><?php echo htmlspecialchars($username); ?></div>
								</div>
								<div class="profile-form-group">
									<div class="profile-form-label">Email Address</div>
									<div class="profile-form-value" id="emailInfo"><?php echo htmlspecialchars($email); ?></div>
								</div>
								<div class="profile-form-group">
									<div class="profile-form-label">Mobile Number</div>
									<div class="profile-form-value" id="mobileNumber"><?php echo htmlspecialchars($mobileNumber); ?></div>
								</div>
								<div class="profile-form-group">
									<div class="profile-form-label">Password</div>
									<div class="profile-form-value" id="password">••••••••</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /APP-CONTENT -->
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
</body>

</html>
