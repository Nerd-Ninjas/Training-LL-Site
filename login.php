<?php
require_once("includes/config.php");
require_once("includes/head_login.php"); 
require_once("includes/classes/FormSanitizer.php");
require_once("includes/classes/Constants.php");
require_once("includes/classes/Account.php");
if($_SESSION["username"]){
header("Location: main.php?ci=AIJM");
}
$account = new Account($con);

    if(isset($_POST["submitButton"])) {
		
		$email= FormSanitizer::sanitizeFormEmail($_POST["email"]);
		$password= FormSanitizer::sanitizeFormPassword($_POST["password"]);
		//echo hash('sha512',$password);
		
		$success = $account->loginEmail($email,$password);

        if($success) {
			$username=$success['username'];
			$key= FormSanitizer::sanitizeFormSessionName();
			$successConfirm = $account->loginConfirm($username,$password,$key);
			$_SESSION["username"] = $username;
			$_SESSION["keyval"] = $key;
			$_SESSION["last_login_timestamp"]=time();
			header("Location: main.php?ci=AIJM");
        }
		else{
			$errorMsg="Invalid Username or Password";
			header("refresh:3,login.php");
		}	
	
	}
?>
<body class="ltr login-img light-mode">
<!-- GLOABAL LOADER -->
    <div id="global-loader" >
      <img src="assets/images/loader.svg" class="loader-img" alt="Loader">
    </div>
    <!-- /GLOABAL LOADER -->
<!-- PAGE -->
    <div class="page">
      <div>
        <!-- CONTAINER OPEN -->

        <div class="container-login100">
          <div class="wrap-login100 p-0">
            <div class="card-body">

			<?php
				if(isset($errorMsg))
				{
					?>
					<div class="alert alert-danger">
						<strong>WRONG ! <?php echo $errorMsg; ?></strong>
					</div>
					<?php
				}
				if(isset($updateMsg)){
				?>
					<div class="alert alert-success">
						<strong>SUCCESS ! <?php echo $updateMsg; ?></strong>
					</div>
				<?php
				}
			?>
              <form class="login100-form validate-form" method="post" class="form-horizontal" enctype="multipart/form-data">
               <span class="login100-form-title"> <img src="assets/images/brand/full-logo-light.png" class="header-brand-img" alt=""></span>
			   
                <div class="wrap-input100 validate-input" data-bs-validate="Valid email is required: ex@abc.xyz">
                  <input class="input100" type="text" name="email" placeholder="Email">
                  <span class="focus-input100"></span>
                  <span class="symbol-input100">
                    <i class="zmdi zmdi-email" aria-hidden="true"></i>
                  </span>
                </div>
                <div class="wrap-input100 validate-input" data-bs-validate="Password is required">
                  <input class="input100" type="password" name="password" placeholder="Password">
                  <span class="focus-input100"></span>
                  <span class="symbol-input100">
                    <i class="zmdi zmdi-lock" aria-hidden="true"></i>
                  </span>
                </div>
                <div class="text-end pt-1">
                  <p class="mb-0">
                    <a href="javascript:void(0);" class="text-primary ms-1">Forgot Password?</a>
                  </p>
                </div>
                <div class="container-login100-form-btn">
				<input type="submit"  name="submitButton" class="login100-form-btn btn-primary" value="Login">	
                </div>
                <div class="text-center pt-3">
                  <p class="text-dark mb-0">Not a member? <a href="javascript:void(0);" class="text-primary ms-1">Create an Account</a>
                  </p>
                </div>
              </form>
            </div>
            <div class="card-footer">
              <div class="d-flex justify-content-center my-3">
                <a href="javascript:void(0);" class="social-login  text-center me-4">
                  <i class="fa fa-google"></i>
                </a>
                <a href="javascript:void(0);" class="social-login  text-center me-4">
                  <i class="fa fa-facebook"></i>
                </a>
                <a href="javascript:void(0);" class="social-login  text-center">
                  <i class="fa fa-twitter"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
        <!-- CONTAINER CLOSED -->
      </div>
    </div>
<?php require_once("includes/script_login.php");  ?>
  </body>
</html>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
          document.body.classList.remove('dark-mode');
      });
</script>