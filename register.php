<?php
require_once("includes/config.php");
require_once("includes/head_login.php"); 
require_once("includes/classes/FormSanitizer.php");
require_once("includes/classes/Constants.php");
require_once("includes/classes/Account.php");
if($_SESSION["username"]){
header("Location: main.php#index.php");
}
if(isset($_REQUEST['btn_insert']))
{

	$firstName=trim($_REQUEST['firstName']);
	$lastName=trim($_REQUEST['lastName']);
	$username= FormSanitizer::sanitizeFormUsername();
	$password=hash("sha512",$_REQUEST['password']);
	$email=trim($_REQUEST['email']);
	$mobileNumber=trim($_REQUEST['mobileNumber']);
	$gender=$_REQUEST['gender'];
	$createdBy='100000';
	$course_id='MSTE';
	$app=1;


if(!isset($errorMsg))
		{
			$insert_stmt=$con->prepare('INSERT INTO users(firstName,lastName,gender,avatarID,username,
			password,email,mobileNumber,createdBy) VALUES(:fn,:ln,:gen,:ava,:un,:pw,:em,:mn,:cb)'); //sql insert query					
			$insert_stmt->bindParam(':fn',$firstName);
			$insert_stmt->bindParam(':ln',$lastName);
			$insert_stmt->bindParam(':gen',$gender);
			$insert_stmt->bindParam(':ava',$gender);
			$insert_stmt->bindParam(':un',$username);
			$insert_stmt->bindParam(':pw',$password);
			$insert_stmt->bindParam(':em',$email);
			$insert_stmt->bindParam(':mn',$mobileNumber);
			$insert_stmt->bindParam(':cb',$createdBy);

			$insert_stmt1=$con->prepare('INSERT INTO users_courses(username,course_id,approved,approvedBy) VALUES(:un,:ci,:app,:cb)'); //sql insert query					
			$insert_stmt1->bindParam(':un',$username);
			$insert_stmt1->bindParam(':ci',$course_id);
			$insert_stmt1->bindParam(':app',$app);
			$insert_stmt1->bindParam(':cb',$createdBy);
				
			if(($insert_stmt->execute()) && ($insert_stmt1->execute()))
			{
				$insertMsg="Successfully added a New User"; //execute query success message
				header("refresh:3;register.php");
			}
			else{
				//echo "INSERT INTO users(firstName,lastName,gender,avatarID,username,password,email,mobileNumber,createdBy) VALUES($firstName,$lastName,$gender,$gender,$username,$password,$email,$mobileNumber,$createdBy)";
				
				$errorMsg="Failed to Add New User";
			}
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
				<div class="alert alert-primary">
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
          <form method="post" class="login100-form validate-form"  enctype="multipart/form-data">
            <span class="login100-form-title"> <img src="assets/images/brand/full-logo-light.png" class="header-brand-img" alt=""></span>
			
			
            <div class="wrap-input100 validate-input" data-bs-validate="Valid email is required: ex@abc.xyz">
              <input class="input100" type="text" name="firstName" placeholder="First Name" wfd-id="id6">
              <span class="focus-input100"></span>
              <span class="symbol-input100">
                <i class="mdi mdi-account" aria-hidden="true"></i>
              </span>
            </div>
			
			
			<div class="wrap-input100 validate-input" data-bs-validate="Valid email is required: ex@abc.xyz">
              <input class="input100" type="text" name="lastName" placeholder="Last Name" wfd-id="id6">
              <span class="focus-input100"></span>
              <span class="symbol-input100">
                <i class="mdi mdi-account-circle" aria-hidden="true"></i>
              </span>
            </div>
			
			
			<div class="wrap-input100 validate-input" data-bs-validate="Valid email is required: ex@abc.xyz">
             <select name="gender" id="gender" class="input100" title="Select Gender">
			 <option value="1">1-Male</option>
			 <option value="2">2-Female</option>
			 <option value="3">3-Transgender</option>
            </select>
              <span class="focus-input100"></span>
              <span class="symbol-input100">
                <i class="mdi mdi-gender-female" aria-hidden="true"></i>
              </span>
            </div>
			
			<div class="wrap-input100 validate-input" data-bs-validate="Valid email is required: ex@abc.xyz">
              <input class="input100" type="number" name="mobileNumber" placeholder="Mobile Number" wfd-id="id6">
              <span class="focus-input100"></span>
              <span class="symbol-input100">
                <i class="mdi mdi-phone" aria-hidden="true"></i>
              </span>
            </div>
			
            <div class="wrap-input100 validate-input" data-bs-validate="Valid email is required: ex@abc.xyz">
              <input class="input100" type="text" name="email" placeholder="Email" wfd-id="id7">
              <span class="focus-input100"></span>
              <span class="symbol-input100">
                <i class="zmdi zmdi-email" aria-hidden="true"></i>
              </span>
            </div>
            <div class="wrap-input100 validate-input" data-bs-validate="Password is required">
              <input class="input100" type="password" name="password" placeholder="Password" wfd-id="id8">
              <span class="focus-input100"></span>
              <span class="symbol-input100">
                <i class="zmdi zmdi-lock" aria-hidden="true"></i>
              </span>
            </div>

            <label class="custom-control custom-checkbox mt-4">
              <input type="checkbox" class="custom-control-input" wfd-id="id9">
              <span class="custom-control-label">Agree the <a href="terms.php">terms and policy</a>
              </span>
            </label>
            <div class="container-login100-form-btn">
			  <input type="submit"  name="btn_insert" class="login100-form-btn btn-primary" value="Register">	
            </div>
            <div class="text-center pt-3">
              <p class="text-dark mb-0">Already have account? <a href="login.php" class="text-primary ms-1">Sign In</a>
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