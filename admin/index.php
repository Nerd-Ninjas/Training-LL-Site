<?php
require_once("../includes/config.php"); 
require_once("../includes/classes/FormSanitizer.php");
require_once("../includes/classes/Constants.php");
require_once("../includes/classes/Account.php");

// Check if user is logged in and is admin (type = 1)
if(!$_SESSION["username"] || $_SESSION["userType"] != 1) {
    header("Location: ../login.php");
    exit;
}

$account = new Account($con);
$username = $_SESSION["username"];
$userDetails = $account->getUserDetails($username);
$firstName = $userDetails['firstName'];
$lastName = $userDetails['lastName'];
$email = $userDetails['email'];
$avatarID = $userDetails['avatarID'];

$avatarDetails = $account->avatarFetch($avatarID);
$filePath = $avatarDetails['filePath'];

// Get statistics
$query_total_users = $con->prepare("SELECT COUNT(*) as total FROM users");
$query_total_users->execute();
$total_users = $query_total_users->fetch(PDO::FETCH_ASSOC)['total'];

$query_approved = $con->prepare("SELECT COUNT(*) as total FROM users WHERE approved=1");
$query_approved->execute();
$approved_users = $query_approved->fetch(PDO::FETCH_ASSOC)['total'];

$query_pending = $con->prepare("SELECT COUNT(*) as total FROM users WHERE approved=0 AND type=0");
$query_pending->execute();
$pending_users = $query_pending->fetch(PDO::FETCH_ASSOC)['total'];

$query_logged_in = $con->prepare("SELECT COUNT(*) as total FROM users WHERE isLoggedIn=1");
$query_logged_in->execute();
$logged_in_users = $query_logged_in->fetch(PDO::FETCH_ASSOC)['total'];
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
	<meta name="keywords" content="admin, ajax admin, dashboard, ui, admin template, ajax, admin dashboard, clean, responsive, admin panel, admin dashboard ajax, admin panel template, ajax bootstrap admin template, ajax template.">
	<meta property="og:url" content="https://training.learnlike.in" />
    	<meta property="og:type" content="article" />
    	<meta property="og:title" content="Learnlike's Training Dashboard" />
    	<meta property="og:description" content="" />
    	<meta property="og:image" content="https://learnlike.in/assets/themes/pan/img/LL-logo-light-new.png" />

	<!-- FAVICON -->
	<link rel="shortcut icon" type="image/x-icon" href="../assets/images/brand/LL-logo-light.png"/>

	<!-- TITLE -->
	<title>Admin Dashboard - Learnlike's Training</title>

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

	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-DJNBDE3NZJ"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'G-DJNBDE3NZJ');
	</script>
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
										<div class="dropdown d-flex">
											<a href="javascript: void(0);" class="nav-link icon" data-bs-toggle="dropdown">
												<svg xmlns="http://www.w3.org/2000/svg" class="header-icon" viewBox="0 0 24 24"><path d="M20.54 5.2L3.74 20.75h16.8V5.2M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-3 15h-8v-6h8v6z"/></svg>
											</a>
											<div class="dropdown-menu dropdown-menu-end p-0">
												<a href="../logout.php" class="dropdown-item d-flex">
													<span class="avatar-sm me-2" style="background: #f64e60;">
														<i class="fe fe-log-out"></i>
													</span>
													<div>
														<strong>Logout</strong>
													</div>
												</a>
											</div>
										</div>
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
												<a class="dropdown-item" href="../logout.php">
													<svg class="svg-icon me-2" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"></path><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"></path></svg>Logout
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
						<a href="index.php" class="side-menu__item active">
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
							<?php if($pending_users > 0): ?>
								<span class="badge badge-danger ms-auto">
									<?php echo $pending_users; ?>
								</span>
							<?php endif; ?>
						</a>
					</li>
				</ul>
			</aside>
			<!-- /APP-SIDEBAR-->

			<!--app-content open-->
			<div class="app-content main-content mt-0">
				<div class="side-app">
					<!-- CONTAINER -->
					<div class="main-container container-fluid">

						<!-- PAGE-HEADER -->
						<div class="page-header">
							<div class="row align-items-end">
								<div class="col-md-8">
									<div class="page-header-title">
										<i class="icon icon-layers me-2"></i>
										<div class="d-inline-block">
											<h3>Admin Dashboard</h3>
											<span class="text-muted">Welcome back, <?php echo htmlspecialchars($firstName); ?>!</span>
										</div>
									</div>
								</div>
								<div class="col-md-4 ms-auto">
									<div class="btn-list">
										<a href="user_management.php" class="btn btn-primary">
											<i class="icon icon-user me-1"></i> Manage Users
										</a>
									</div>
								</div>
							</div>
						</div>
						<!-- /PAGE-HEADER -->

						<!-- ROW -->
						<div class="row">
							<div class="col-md-6 col-lg-3">
								<div class="card">
									<div class="card-body">
										<div class="counter-status d-flex align-items-end justify-content-between">
											<div>
												<h2 class="counter mb-0"><?php echo $total_users; ?></h2>
												<p class="text-muted mb-0">Total Users</p>
											</div>
											<div class="icon-bg bg-primary-transparent">
												<i class="icon icon-users text-primary"></i>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-lg-3">
								<div class="card">
									<div class="card-body">
										<div class="counter-status d-flex align-items-end justify-content-between">
											<div>
												<h2 class="counter mb-0"><?php echo $approved_users; ?></h2>
												<p class="text-muted mb-0">Approved Users</p>
											</div>
											<div class="icon-bg bg-success-transparent">
												<i class="icon icon-check text-success"></i>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-lg-3">
								<div class="card">
									<div class="card-body">
										<div class="counter-status d-flex align-items-end justify-content-between">
											<div>
												<h2 class="counter mb-0"><?php echo $pending_users; ?></h2>
												<p class="text-muted mb-0">Pending Approvals</p>
											</div>
											<div class="icon-bg bg-warning-transparent">
												<i class="icon icon-clock text-warning"></i>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-6 col-lg-3">
								<div class="card">
									<div class="card-body">
										<div class="counter-status d-flex align-items-end justify-content-between">
											<div>
												<h2 class="counter mb-0"><?php echo $logged_in_users; ?></h2>
												<p class="text-muted mb-0">Active Users</p>
											</div>
											<div class="icon-bg bg-info-transparent">
												<i class="icon icon-activity text-info"></i>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /ROW -->

						<!-- ROW -->
						<div class="row">
							<div class="col-12">
								<div class="card">
									<div class="card-header border-bottom">
										<h3 class="card-title">Recent Users</h3>
									</div>
									<div class="card-body">
										<div class="table-responsive">
											<table class="table table-hover mb-0">
												<thead>
													<tr>
														<th>Name</th>
														<th>Email</th>
														<th>Mobile</th>
														<th>Status</th>
														<th>Joined Date</th>
														<th>Action</th>
													</tr>
												</thead>
												<tbody>
													<?php
													$query_recent = $con->prepare("SELECT id, firstName, lastName, email, mobileNumber, approved, createdDate FROM users WHERE type=0 ORDER BY createdDate DESC LIMIT 10");
													$query_recent->execute();
													while($user = $query_recent->fetch(PDO::FETCH_ASSOC)) {
														$status = $user['approved'] == 1 ? 'Approved' : 'Pending';
														$status_class = $user['approved'] == 1 ? 'bg-success' : 'bg-warning';
														?>
														<tr>
															<td><?php echo htmlspecialchars($user['firstName'] . " " . $user['lastName']); ?></td>
															<td><?php echo htmlspecialchars($user['email']); ?></td>
															<td><?php echo htmlspecialchars($user['mobileNumber']); ?></td>
															<td><span class="badge <?php echo $status_class; ?>"><?php echo $status; ?></span></td>
															<td><?php echo date('d M, Y', strtotime($user['createdDate'])); ?></td>
															<td>
																<a href="user-details.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">View</a>
															</td>
														</tr>
													<?php } ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /ROW -->

					</div>
					<!-- /CONTAINER -->
				</div>
			</div>
			<!--app-content close-->

		</div>
		<!-- /PAGE-MAIN -->
	</div>
	<!-- /PAGE -->

	<!-- FOOTER -->
	<footer class="footer">
		<div class="container-fluid">
			<div class="row align-items-center">
				<div class="col-md-12 text-center">
					<span class="text-muted">Copyright © 2025. All rights reserved.</span>
				</div>
			</div>
		</div>
	</footer>
	<!-- /FOOTER -->

	<!-- JQUERY JS -->
	<script src="../assets/js/jquery.min.js"></script>
	<!-- POPPER JS -->
	<script src="../assets/plugins/bootstrap/js/popper.min.js"></script>
	<!-- BOOTSTRAP JS -->
	<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>
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

</body>
</html>
