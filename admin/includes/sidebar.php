
<!-- APP-SIDEBAR-->

<?php
// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sticky">
	<div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
	<div class="app-sidebar">
		<div class="side-header">
			<a class="header-brand1" href="index.php">
				<img src="../assets/images/brand/full-logo-dark.png" class="header-brand-img desktop-logo" alt="logo">
				<img src="../assets/images/brand/LL-logo-light.png" class="header-brand-img toggle-logo" alt="logo">
				<img src="../assets/images/brand/LL-logo-light.png" class="header-brand-img light-logo" alt="logo">
				<img src="../assets/images/brand/full-logo-light.png" class="header-brand-img light-logo1" alt="logo">
			</a><!-- LOGO -->
		</div>
		<div class="main-sidemenu">
			<div class="slide-left disabled" id="slide-left">
				<svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
					<path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
				</svg>
			</div>
			<ul class="side-menu">
				<li>
					<h3>Menu</h3>
				</li>
				<li class="slide <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
					<a class="side-menu__item has-link" data-bs-toggle="slide" href="index.php">
						<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
							<path d="M3 13h2v8H3zm4-8h2v16H7zm4-2h2v18h-2zm4 4h2v14h-2zm4-2h2v16h-2z"/>
						</svg>
						<span class="side-menu__label">Dashboard</span>
					</a>
				</li>
				<li>
					<h3>Administration</h3>
				</li>
				<li class="slide <?php echo ($current_page == 'user_management.php' || $current_page == 'user-details.php') ? 'active' : ''; ?>">
					<a class="side-menu__item" data-bs-toggle="slide" href="javascript: void(0);">
						<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
							<path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
						</svg>
						<span class="side-menu__label">Users Management</span>
						<i class="angle fa fa-angle-right"></i>
					</a>
					<ul class="slide-menu">
						<li class="side-menu-label1">
							<a href="javascript:void(0);">Users Management</a>
						</li>
						<li>
							<a href="user_management.php" class="slide-item <?php echo ($current_page == 'user_management.php') ? 'active' : ''; ?>">User Management</a>
						</li>
						<li>
							<a href="user-details.php" class="slide-item <?php echo ($current_page == 'user-details.php') ? 'active' : ''; ?>">View User Details</a>
						</li>
					</ul>
				</li>
				<li class="slide <?php echo ($current_page == 'programme_management.php' || $current_page == 'download_csv_template.php' || $current_page == 'batch_management.php') ? 'active' : ''; ?>">
					<a class="side-menu__item" data-bs-toggle="slide" href="javascript: void(0);">
						<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
							<path d="M4 6h16V4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4v2h8v-2h4c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 12V6h16v12H4z"/>
						</svg>
						<span class="side-menu__label">Programme Management</span>
						<i class="angle fa fa-angle-right"></i>
					</a>
					<ul class="slide-menu">
						<li class="side-menu-label1">
							<a href="javascript:void(0);">Programmes</a>
						</li>
						<li>
							<a href="programme_management.php" class="slide-item <?php echo ($current_page == 'programme_management.php') ? 'active' : ''; ?>">Manage Programmes</a>
						</li>
						<li>
							<a href="batch_management.php" class="slide-item <?php echo ($current_page == 'batch_management.php') ? 'active' : ''; ?>">Batch Management</a>
						</li>
						<li>
							<a href="download_csv_template.php" class="slide-item <?php echo ($current_page == 'download_csv_template.php') ? 'active' : ''; ?>">Download CSV Template</a>
						</li>
					</ul>
				</li>
				<li>
					<h3>System</h3>
				</li>
				<li class="slide">
					<a class="side-menu__item" data-bs-toggle="slide" href="javascript: void(0);">
						<svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24">
							<path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.64l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.49.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.22-.07.5.12.64l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.64l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.49-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.5-.12-.64l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
						</svg>
						<span class="side-menu__label">Settings</span>
						<i class="angle fa fa-angle-right"></i>
					</a>
					<ul class="slide-menu">
						<li class="side-menu-label1">
							<a href="javascript:void(0);">Settings</a>
						</li>
						<li>
							<a href="javascript:void(0);" class="slide-item" onclick="alert('Coming Soon'); return false;">General Settings</a>
						</li>
						<li>
							<a href="javascript:void(0);" class="slide-item" onclick="alert('Coming Soon'); return false;">System Configuration</a>
						</li>
					</ul>
				</li>
			</ul>
			<div class="slide-right" id="slide-right">
				<svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
					<path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
				</svg>
			</div>
		</div>
	</div>
</div>
<!-- /APP-SIDEBAR-->
<style>
	.side-menu__icon {
		width: 24px;
		height: 24px;
		fill: currentColor;
		stroke: currentColor;
	}
	
	.side-menu__icon path {
		fill: currentColor;
	}
	
	.side-menu .slide.active > .side-menu__item .angle {
		transform: rotate(90deg);
	}
	
	.side-menu .slide.active > .slide-menu {
		display: block;
	}
</style>