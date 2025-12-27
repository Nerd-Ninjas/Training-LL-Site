<?php
require_once("includes/config.php");
require_once("includes/head_main.php"); 
require_once("includes/classes/FormSanitizer.php");
require_once("includes/classes/Constants.php");
require_once("includes/classes/Account.php");
if(!$_SESSION["username"]){
header("Location:login.php");
}

else{
$account = new Account($con);
$username=$_SESSION["username"];
$userDetails=$account->getUserDetails($username);
//$_GET['ci']='MSTE';
$_SESSION['course_id']=$_GET['ci'];
$course_id=$_SESSION['course_id'];
$programme_id=$_GET['pi'];
$userProgrammes=$account->getUserProgrammes($username);
$courseDetails=$account->getCoursesDetails($course_id);
$courseName=$courseDetails['course_name'];

?>

<body class="app sidebar-mini ltr">

	<!-- GLOBAL-LOADER -->
	<div id="global-loader">
		<img src="assets/images/loader.svg" class="loader-img" alt="Loader">
	</div>
	<!-- /GLOBAL-LOADER -->

	<!-- PAGE -->
	<div class="page">
		<div class="page-main">

		<?php 
		
			require_once("includes/header.php"); 
			require_once("includes/sidebar.php"); 
			
		
		?>

        

			<!--app-content open-->
			<div class="app-content main-content mt-0">
				<div class="side-app">
					 <!-- CONTAINER -->
					 <div class="main-container container-fluid">

						<!-- CONTENT -->
						<div class="inner-body" id="content">

						</div>
						<!-- CONTENT CLOSED-->

					</div>
				</div>
			</div>
			<!-- CONTAINER END -->
		</div>

		<!-- Country-selector modal-->
		<div class="modal fade" id="country-selector">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content country-select-modal">
                    <div class="modal-header">
                        <h6 class="modal-title">Choose Country</h6><button aria-label="Close" class="btn-close" data-bs-dismiss="modal" type="button"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">
                        <ul class="row row-sm p-3">
                            <li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block active">
                                    <span class="country-selector"><img alt="unitedstates" src="assets/images/flags/us_flag.jpg" class="me-2 language"></span>United States
                                </a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
                                    <span class="country-selector"><img alt="italy" src="assets/images/flags/italy_flag.jpg" class="me-2 language"></span>Italy
                                </a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
									<span class="country-selector"><img alt="spain" src="assets/images/flags/spain_flag.jpg" class="me-2 language"></span>Spain
								</a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
								    <span class="country-selector"><img alt="india" src="assets/images/flags/india_flag.jpg" class="me-2 language"></span>India
                               </a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
								    <span class="country-selector"><img alt="french" src="assets/images/flags/french_flag.jpg" class="me-2 language"></span>French
                                </a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
									<span class="country-selector"><img alt="russia" src="assets/images/flags/russia_flag.jpg" class="me-2 language"></span>Russia
								</a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
								    <span class="country-selector"><img alt="germany" src="assets/images/flags/germany_flag.jpg" class="me-2 language"></span>Germany
                               	</a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
									<span class="country-selector"><img alt="argentina" src="assets/images/flags/argentina_flag.jpg" class="me-2 language"></span>Argentina
								</a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
								    <span class="country-selector"><img alt="uae" src="assets/images/flags/uae_flag.jpg" class="me-2 language"></span>UAE
                               	</a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
									<span class="country-selector"><img alt="austria" src="assets/images/flags/austria_flag.jpg" class="me-2 language"></span>Austria
								</a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
									<span class="country-selector"><img alt="mexico" src="assets/images/flags/mexico_flag.jpg" class="me-2 language"></span>Mexico
								</a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
								    <span class="country-selector"><img alt="china" src="assets/images/flags/china_flag.jpg" class="me-2 language"></span>China
                               </a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
									<span class="country-selector"><img alt="poland" src="assets/images/flags/poland_flag.jpg" class="me-2 language"></span>Poland
                                </a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
									<span class="country-selector"><img alt="canada" src="assets/images/flags/canada_flag.jpg" class="me-2 language"></span>Canada
								</a>
							</li>
							<li class="col-lg-4 mb-2">
                                <a class="btn btn-country btn-lg btn-block">
									<span class="country-selector"><img alt="malaysia" src="assets/images/flags/malaysia_flag.jpg" class="me-2 language"></span>Malaysia
                                </a>
							</li>
						</ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Country-selector modal-->

		<!-- FOOTER -->
		<footer class="footer">
			<div class="container">
				<div class="row align-items-center flex-row-reverse">
					<div class="col-md-12 col-sm-12 text-center">
						<a href="https://learnlike.in" style="color:#6f42c1">Learnlike</a>  © All rights reserved 2025 | 
						<a href="#">Terms & Conditions</a> |
						<a href="#">Privacy Policy</a> 	</div>
				</div>
			</div>
		</footer>
		<!-- FOOTER END -->
	</div>

	<!-- BACK-TO-TOP -->
	<a href="#top" id="back-to-top"><i class="fa fa-long-arrow-up"></i></a>

	<!-- JQUERY JS -->
	<script src="assets/js/jquery.min.js"></script>

	<!-- BOOTSTRAP JS -->
	<script src="assets/plugins/bootstrap/js/popper.min.js"></script>
	<script src="assets/plugins/bootstrap/js/bootstrap.min.js"></script>

	<!-- SIDE-MENU JS-->
	<script src="assets/plugins/sidemenu/sidemenu.js"></script>

	<!-- PERFECT SCROLLBAR JS-->
	<script src="assets/plugins/p-scroll/perfect-scrollbar.js"></script>
	<script src="assets/plugins/p-scroll/pscroll.js"></script>

    <!-- STICKY JS -->
    <script src="assets/js/sticky.js"></script>

    <!-- COLOR THEME JS -->
    <script src="assets/js/themeColors.js"></script>

	<!-- CUSTOM JS -->
	<script src="assets/js/custom.js"></script>

	<!-- Ajax js -->
	<script src="assets/ajax/ajax.js"></script>

    <!-- SWITCHER JS -->
    <script src="assets/switcher/js/switcher.js"></script>

</body>
<?php } ?>
</html>