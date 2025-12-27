<?php
class Account {

    private $con;
    private $errorArray = array();

    public function __construct($con) {
        $this->con = $con;
    }

    public function updateDetails($fn, $ln, $em, $un) {
        $this->validateFirstName($fn);
        $this->validateLastName($ln);
        $this->validateNewEmail($em, $un);

        if(empty($this->errorArray)) {
            $query = $this->con->prepare("UPDATE users SET firstName=:fn, lastName=:ln, email=:em
                                            WHERE username=:un");
            $query->bindValue(":fn", $fn);
            $query->bindValue(":ln", $ln);
            $query->bindValue(":em", $em);
            $query->bindValue(":un", $un);

            return $query->execute();
        }

        return false;
    }

    public function register($fn, $ln, $un, $em, $em2, $pw, $pw2) {
        $this->validateFirstName($fn);
        $this->validateLastName($ln);
        $this->validateUsername($un);
        $this->validateEmails($em, $em2);
        $this->validatePasswords($pw, $pw2);

        if(empty($this->errorArray)) {
            return $this->insertUserDetails($fn, $ln, $un, $em, $pw);
        }

        return false;
    }

	public function validate($fn, $ln, $un, $em, $mn) {
        $this->validateFirstName($fn);
        $this->validateLastName($ln);
       	$this->validateUsername($un);
        $this->validateEmails($em);
		$this->validateMobilenumber($mn);

        if(empty($this->errorArray)) {
			return true;
        }

        return false;
    }
	
	public function registerTamil($fn,$un,$mn,$otp,$bc,$mc) {
        $this->validateFirstTamilName($fn);
		$this->validateUsername($un);	
		$this->validateMobilenumber($mn);

        if(empty($this->errorArray)) {
            return $this->insertUserDetailsTamil($fn,$un,$mn,$otp,$bc,$mc);
        }

        return false;
    }
	public function updateBasicInfo($fn,$un,$mn,$otp) {
        $this->validateFirstTamilName($fn);
		$this->validateUsername($un);	
		$this->validateMobilenumber($mn);

        if(empty($this->errorArray)) {
            return $this->updateUserDetailsTamil($fn,$un,$mn,$otp);
        }

        return false;
    }
	
	
	
		public function validateTamil($fn,$un,$mn) {
        $this->validateFirstTamilName($fn);
		$this->validateUsername($un);	
		$this->validateMobilenumber($mn);

        if(empty($this->errorArray)) {
			return $this->sendMobileOTP($mn);
        }

        return false;
    }
	
		public function validateloginTamil($mn) {
		$this->validateMobilenumberLogin($mn);

        if(empty($this->errorArray)) {
			return $this->sendMobileOTP($mn);
        }

        return false;
    }
	
	public function informFalseOTP($otp) {
        $this->validateOTP($otp);
        if(empty($this->errorArray)) {
			return true;
        }
        return false;
    }
	
	public function informCorrectOTP($otp) {
        $this->correctOTP($otp);
        if(empty($this->errorArray)) {
			return true;
        }
        return false;
    }
	
	public function sessionClearRegister() {
		 unset($_SESSION["firstName"]);	
		 unset($_SESSION["mobileNumber"]);
	}	

	public function sendMobileOTPEdit($mn) {
				$otp = rand(100000, 999999);
				$_SESSION['session_otp'] = $otp;
		
				/*$username = "rdeepak22@gmail.com";
				$hash = "763e0102617718559f98b846690bc7c6bee0b60c0a6c9d075078b04d3bed5b66";
				$test = "0";
				$sender = "TXTLCL"; // 
				$numbers = $mn; 
				$message = "Your One Time Password for KIT-Learn Like App " . $otp;
				$message = urlencode($message);
			   	$data = "username=".$username."&hash=".$hash."&message=".$message."&sender=".$sender."&numbers=".$numbers."&test=".$test;
				$ch = curl_init('http://api.textlocal.in/send/?');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				curl_close($ch);*/
				$result=1;
		
		 if($result) {
            return true;
        }
		return false;
    }
	
	private function sendMobileOTP($mn) {
				$otp = rand(100000, 999999);
				$_SESSION['session_otp'] = $otp;
	
				/*$username = "rdeepak22@gmail.com";
				$hash = "763e0102617718559f98b846690bc7c6bee0b60c0a6c9d075078b04d3bed5b66";
				$test = "0";
				$sender = "TXTLCL"; // 
				$numbers = $mn; 
				$message = "Your One Time Password for KIT-Learn Like App " . $otp;
				$message = urlencode($message);
			   	$data = "username=".$username."&hash=".$hash."&message=".$message."&sender=".$sender."&numbers=".$numbers."&test=".$test;
				$ch = curl_init('http://api.textlocal.in/send/?');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				curl_close($ch);*/
				$result=1;
		
		 if($result) {
            return true;
        }
		return false;
    }
	
	public function resendOTP($otp,$mn){
		
				$_SESSION['session_otp'] = $otp;
		
				/*$username = "rdeepak22@gmail.com";
				$hash = "763e0102617718559f98b846690bc7c6bee0b60c0a6c9d075078b04d3bed5b66";
				$test = "0";
				$sender = "TXTLCL";  
				$numbers = $mn; 
				$message = "Your One Time Password for KIT-Learn Like App " . $otp;
				$message = urlencode($message);
			   	$data = "username=".$username."&hash=".$hash."&message=".$message."&sender=".$sender."&numbers=".$numbers."&test=".$test;
				$ch = curl_init('http://api.textlocal.in/send/?');
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);
				curl_close($ch);*/
				$result=1;
		
		 if($result) {
            return true;
        }
		return false;
		
		
	}	

	public function validateOtherInfoTamil($ln, $em, $std, $gn, $un, $gr) {
        $this->validateLastTamilName($ln);
        $this->validateEmails($em);
		$this->validateStandards($std);
		$this->validateGender($gn);

        if(empty($this->errorArray)) {
			return $this->updateOtherInfoTamil($ln, $em, $std, $gn, $un, $gr);

        }

        return false;
    }
	public function validateOtherInfoTamilEdit($ln, $em, $std, $gn, $un, $gr) {
        $this->validateLastTamilName($ln);
        $this->validateNewEmail($em, $un);
		$this->validateStandards($std);
		$this->validateGender($gn);

        if(empty($this->errorArray)) {
			return $this->updateOtherInfoTamil($ln, $em, $std, $gn, $un, $gr);

        }

        return false;
    }
	  private function updateOtherInfoTamil($ln, $em, $std, $gn, $un, $gr) {
            $query = $this->con->prepare("UPDATE users SET lastName=:ln, email=:em, gender=:gn, std=:std, avatarID=:gn, groupCode=:gr
                                            WHERE username=:un");
       
            $query->bindValue(":ln", $ln);
            $query->bindValue(":em", $em);
			$query->bindValue(":gn", $gn);
			$query->bindValue(":std", $std);
            $query->bindValue(":un", $un);
		  	$query->bindValue(":gr", $gr);

            return $query->execute();
    }
	
	  public function updateOtherInfoWithoutEmailTamil($ln, $std, $gn, $un, $gr) {
            $query = $this->con->prepare("UPDATE users SET lastName=:ln, gender=:gn, std=:std, avatarID=:gn, groupCode=: gr
                                            WHERE username=:un");
       
            $query->bindValue(":ln", $ln);
			$query->bindValue(":gn", $gn);
			$query->bindValue(":std", $std);
            $query->bindValue(":un", $un);
		  	$query->bindValue(":gr", $gr);

            return $query->execute();
    }	
	
	 public function loginTamil($mn) {

        $query = $this->con->prepare("SELECT * FROM users WHERE mobileNumber=:mn");
        $query->bindValue(":mn", $mn);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }

        array_push($this->errorArray, Constants::$loginFailed);
        return false;
    }
	
	 public function indexCheckTamil($un) {
        $query = $this->con->prepare("SELECT * FROM users WHERE username=:un");
        $query->bindValue(":un", $un);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }

        session_destroy();
        return false;
    }
	
	public function indexLoginCheckTamil($un,$otp) {
        $query = $this->con->prepare("SELECT * FROM currentusers WHERE username=:un AND SignInOTP=:otp");
        $query->bindValue(":un", $un);
		$query->bindValue(":otp", $otp);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
     	session_destroy();
        return false;
    }
	
	public function loginMoreInfoTamil($un,$otp) {
        $query = $this->con->prepare("SELECT * FROM currentusers WHERE username=:un AND SignInOTP=:otp");
        $query->bindValue(":un", $un);
		$query->bindValue(":otp", $otp);
   
        $query->execute();
		 
       if($query->rowCount() == 1) {
            return true;
        }

        return false;
    }
	
	
	
	 public function avatarFetch($av) {
        $query = $this->con->prepare("SELECT * FROM avatar WHERE id=:av");
        $query->bindValue(":av", $av);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }

        array_push($this->errorArray, Constants::$avatarFailed);
        return false;
    }
	
	public function avatarChangeFetch($gen) {
        $query = $this->con->prepare("SELECT * FROM avatar WHERE gender=:gen OR gender=4");
        $query->bindValue(":gen", $gen);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() > 0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }

        return false;
    }
	
	public function updateUserAvatar($un,$aid) {
          
        $query = $this->con->prepare("UPDATE users SET avatarID=:aid WHERE username=:un");
        $query->bindValue(":un", $un);
        $query->bindValue(":aid", $aid); 

        return $query->execute();
    }
	
    public function login($un, $pw) {
        $pw = hash("sha512", $pw);

        $query = $this->con->prepare("SELECT * FROM users WHERE username=:un AND password=:pw");
        $query->bindValue(":un", $un);
        $query->bindValue(":pw", $pw);

        $query->execute();

        if($query->rowCount() == 1) {
            return true;
        }

        array_push($this->errorArray, Constants::$loginFailed);
        return false;
    }
	
	 public function loginEmail($em, $pw) {
        $pw = hash("sha512", $pw);

        $query = $this->con->prepare("SELECT * FROM users WHERE email=:em AND password=:pw");
        $query->bindValue(":em", $em);
        $query->bindValue(":pw", $pw);
        $query->execute();
		$result = array();

        if($query->rowCount() > 0) {
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        array_push($this->errorArray, Constants::$loginFailed);
        return false;
    }
	
	public function loginConfirm($un, $pw, $ky) {
        $pw = hash("sha512", $pw);
		$isLoggedIn=1;
        $query = $this->con->prepare("UPDATE users SET isLoggedIn=:isl,loginDateTime=NOW(),visit=visit+1 WHERE username=:un AND password=:pw");
        $query->bindValue(":isl", $isLoggedIn);
        $query->bindValue(":un", $un);
        $query->bindValue(":pw", $pw);
        $query->execute();
		
        if($query->rowCount() == 1) {
			$query_insert=$this->con->prepare("INSERT INTO currentusers (username,keyval)
                                        VALUES ( :un, :ky)");
			$query_insert->bindValue(":un", $un);								
			$query_insert->bindValue(":ky", $ky);
			$query_insert->execute();
            return true;
        }

        array_push($this->errorArray, Constants::$loginFailed);
        return false;
    }
	
	public function logoutConfirm($un,$ky,$la) {
		$isLoggedIn=0;
        $query = $this->con->prepare("UPDATE users SET isLoggedIn=:isl,logoutDateTime=NOW() WHERE username=:un");
        $query->bindValue(":isl", $isLoggedIn);
        $query->bindValue(":un", $un);

        $query->execute();

        if($query->rowCount() == 1) {
			$query_update=$this->con->prepare("UPDATE currentusers SET logoutDate=NOW(),lastActive=:la WHERE username=:un AND keyval=:ky");
			$query_update->bindValue(":la", $la);
			$query_update->bindValue(":un", $un);								
			$query_update->bindValue(":ky", $ky);
			$query_update->execute();
            return true;
        }

        array_push($this->errorArray, Constants::$loginFailed);
        return false;
    }
    private function insertUserDetails($fn, $ln, $un, $em, $pw) {
        
        $pw = hash("sha512", $pw);
        
        $query = $this->con->prepare("INSERT INTO users (firstName, lastName, username, email, password)
                                        VALUES (:fn, :ln, :un, :em, :pw)");
        $query->bindValue(":fn", $fn);
        $query->bindValue(":ln", $ln);
        $query->bindValue(":un", $un);
        $query->bindValue(":em", $em);
        $query->bindValue(":pw", $pw);

        return $query->execute();
    }
	
	 private function insertUserDetailsTamil($fn,$un,$mn,$otp,$bc,$mc) {
        
        $sn=1;   
        $query = $this->con->prepare("INSERT INTO users (firstName, username, mobileNumber, SignUpOTP, isLoggedIn, boardCode, mediumCode)
                                        VALUES (:fn, :un, :mn, :otp, :sn, :bc, :mc)");
        $query->bindValue(":fn", $fn);
        $query->bindValue(":un", $un);
        $query->bindValue(":mn", $mn);
        $query->bindValue(":otp", $otp);
		$query->bindValue(":sn", $sn); 
		$query->bindValue(":bc", $bc); 
		$query->bindValue(":mc", $mc);  

        return $query->execute();
    }
	
	public function updateUserDetailsTamil($fn,$un,$mn,$otp) {
          
        $query = $this->con->prepare("UPDATE users SET firstName=:fn,mobileNumber=:mn WHERE username=:un");
        $query->bindValue(":fn", $fn);
        $query->bindValue(":un", $un);
        $query->bindValue(":mn", $mn); 

        return $query->execute();
    }
	
	public function insertCurrentUsersTamil($un,$otp) {
   
        $query = $this->con->prepare("INSERT INTO currentusers (username, SignInOTP)
                                        VALUES (:un, :otp)");
        $query->bindValue(":un", $un);
        $query->bindValue(":otp", $otp); 

        return $query->execute();
    }
	
	
	private function validateFirstTamilName($fn) {
        if($fn=="") {
            array_push($this->errorArray, Constants::$firstNameTamilCharacters);
        }
    }
	
	private function validateLastTamilName($ln) {
        if($ln=="") {
            array_push($this->errorArray, Constants::$lastNameTamilCharacters);
        }
    }
	private function validateStandards($std){
        if($std=="") {
            array_push($this->errorArray, Constants::$stdRequired);
        }
    }
	
	private function validateGender($gn){
        if($gn=="") {
            array_push($this->errorArray, Constants::$genderRequired);
        }
    }
		
	
	
    private function validateFirstName($fn) {
        if(strlen($fn) < 2 || strlen($fn) > 25) {
            array_push($this->errorArray, Constants::$firstNameCharacters);
        }
    }

    private function validateLastName($ln) {
        if(strlen($ln) < 2 || strlen($ln) > 25) {
            array_push($this->errorArray, Constants::$lastNameCharacters);
        }
    }
	
	private function validateOTP($otp) {
        if(strlen($otp) < 6) {
            array_push($this->errorArray, Constants::$otpLength);
        }
		array_push($this->errorArray, Constants::$wrongOtp);
		
    }
	
	private function correctOTP($otp) {
		array_push($this->errorArray, Constants::$correctOTP);
		
    }

    private function validateUsername($un) {
        if(strlen($un) < 2 || strlen($un) > 25) {
            array_push($this->errorArray, Constants::$usernameCharacters);
            return;
        }

        $query = $this->con->prepare("SELECT * FROM users WHERE username=:un");
        $query->bindValue(":un", $un);

        $query->execute();
        
        if($query->rowCount() != 0) {
            array_push($this->errorArray, Constants::$usernameTaken);
        }
    }
	
	private function validateMobilenumber($mn) {
        if(strlen($mn) < 10) {
            array_push($this->errorArray, Constants::$mobileNumberWronglength);
            return;
        }

        $query = $this->con->prepare("SELECT * FROM users WHERE mobileNumber=:mn");
        $query->bindValue(":mn", $mn);

        $query->execute();
        
        if($query->rowCount() != 0) {
            array_push($this->errorArray, Constants::$mobileNumberTaken);
        }
    }
	
	private function validateMobilenumberLogin($mn) {
        if(strlen($mn) < 10) {
            array_push($this->errorArray, Constants::$mobileNumberWronglength);
            return;
        }
		$query = $this->con->prepare("SELECT * FROM users WHERE mobileNumber=:mn");
        $query->bindValue(":mn", $mn);

        $query->execute();
        
        if($query->rowCount() == 0) {
            array_push($this->errorArray, Constants::$mobileNumberNotExist);
        }

	}	

    private function validateEmails($em) {
  
        if(!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            array_push($this->errorArray, Constants::$emailInvalid);
            return;
        }

        $query = $this->con->prepare("SELECT * FROM users WHERE email=:em");
        $query->bindValue(":em", $em);

        $query->execute();
        
        if($query->rowCount() != 0) {
            array_push($this->errorArray, Constants::$emailTaken);
        }
    }

    private function validateNewEmail($em, $un) {

        if(!filter_var($em, FILTER_VALIDATE_EMAIL)) {
            array_push($this->errorArray, Constants::$emailInvalid);
            return;
        }

        $query = $this->con->prepare("SELECT * FROM users WHERE email=:em AND username != :un");
        $query->bindValue(":em", $em);
        $query->bindValue(":un", $un);

        $query->execute();
        
        if($query->rowCount() != 0) {
            array_push($this->errorArray, Constants::$emailTaken);
        }
    }

    private function validatePasswords($pw, $pw2) {
        if($pw != $pw2) {
            array_push($this->errorArray, Constants::$passwordsDontMatch);
            return;
        }

        if(strlen($pw) < 5 || strlen($pw) > 25) {
            array_push($this->errorArray, Constants::$passwordLength);
        }
    }

    public function getError($error) {
        if(in_array($error, $this->errorArray)) {
            return "<span class='errorMessage'>$error</span>";
        }
    }

    public function getFirstError() {
        if(!empty($this->errorArray)) {
            return $this->errorArray[0];
        }
    }

    public function updatePassword($oldPw, $pw, $pw2, $un) {
        $this->validateOldPassword($oldPw, $un);
        $this->validatePasswords($pw, $pw2);

        if(empty($this->errorArray)) {
            $query = $this->con->prepare("UPDATE users SET password=:pw WHERE username=:un");
            $pw = hash("sha512", $pw);
            $query->bindValue(":pw", $pw);
            $query->bindValue(":un", $un);

            return $query->execute();
        }

        return false;
    }

    public function validateOldPassword($oldPw, $un) {
        $pw = hash("sha512", $oldPw);

        $query = $this->con->prepare("SELECT * FROM users WHERE username=:un AND password=:pw");
        $query->bindValue(":un", $un);
        $query->bindValue(":pw", $pw);

        $query->execute();

        if($query->rowCount() == 0) {
            array_push($this->errorArray, Constants::$passwordIncorrect);
        }
    }
	
	 public function logoutTamil($un,$otp) {
        if($un) {
            $query = $this->con->prepare("UPDATE currentusers SET logoutDate=NOW() WHERE username=:un AND SignInOTP=:otp");
            $query->bindValue(":un", $un);
			$query->bindValue(":otp", $otp);
			
            return $query->execute();
        }

        return false;
    }
	
	public function groupFetch($gc) {

        $query = $this->con->prepare("SELECT * FROM groupdivision WHERE groupCode=:gc");
        $query->bindValue(":gc", $gc);

        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function notificationUserFetch($un,$gc,$std) {
		$td=date("Y-m-d");
        $query = $this->con->prepare("SELECT * FROM notificationuser WHERE username=:un AND groupCode=:gc AND std=:std AND DATE(validity)>=:td ORDER by viewed ASC, validity ASC");
		$query->bindValue(":un", $un);
        $query->bindValue(":gc", $gc);
		$query->bindValue(":std", $std);
		$query->bindValue(":td", $td);

        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
			}
            return $result;
        }

        return false;
    }
	public function notificationFetch($id) {
        $query = $this->con->prepare("SELECT * FROM notification WHERE id=:id");
		$query->bindValue(":id", $id);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function notificationUserSingleFetch($nc) {
        $query = $this->con->prepare("SELECT * FROM notificationuser WHERE notiCode=:nc");
		$query->bindValue(":nc", $nc);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function notificationCountFetch() {
        $query = $this->con->prepare("SELECT * FROM notificationuser WHERE viewed=0");
		$query->bindValue(":nc", $nc);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function notificationUserUpdate($ni,$nc,$un) {
		$vi=1;
        $query = $this->con->prepare("UPDATE notificationuser SET viewed=:vi,viewedDate=NOW() WHERE notiId=:ni AND notiCode=:nc AND username=:un");
		$query->bindValue(":ni", $ni);
		$query->bindValue(":un", $un);
		$query->bindValue(":vi", $vi);
		$query->bindValue(":nc", $nc);
      
        $query->execute();
	
    }
	
	public function retrieveSubject($sub) {
        $query = $this->con->prepare("SELECT * FROM subject WHERE subjectCode=:su");
		$query->bindValue(":su", $sub);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
		public function retrieveUnit($uni) {
        $query = $this->con->prepare("SELECT * FROM unit WHERE unitCode=:un");
		$query->bindValue(":un", $uni);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveTopic($top) {
        $query = $this->con->prepare("SELECT * FROM topic WHERE topicCode=:top");
		$query->bindValue(":top", $top);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveSubTopic($subTop) {
        $query = $this->con->prepare("SELECT * FROM subtopic WHERE subTopicCode=:stop");
		$query->bindValue(":stop", $subTop);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveScript($scr) {
        $query = $this->con->prepare("SELECT * FROM scriptmaster WHERE scriptId=:scr");
		$query->bindValue(":scr", $scr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveScriptReview($scr) {
        $query = $this->con->prepare("SELECT * FROM scriptmaster WHERE scriptId=:scr AND status=4 OR status=8");
		$query->bindValue(":scr", $scr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrievePostScriptReview($pscr) {
        $query = $this->con->prepare("SELECT * FROM postprocscriptmaster WHERE postScriptId=:pscr AND status=4 OR status=8 OR status=15");
		$query->bindValue(":pscr", $pscr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrievePostScriptFinal($pscr) {
        $query = $this->con->prepare("SELECT * FROM postprocscriptmaster WHERE postScriptId=:pscr AND status=19");
		$query->bindValue(":pscr", $pscr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveScriptApprove($scr) {
        $query = $this->con->prepare("SELECT * FROM scriptmaster WHERE scriptId=:scr AND status=6 OR status=12 or status=14 or status=15");
		$query->bindValue(":scr", $scr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
		public function retrievePostScriptAssigned($pscr) {
        $query = $this->con->prepare("SELECT * FROM postprocscriptmaster WHERE postScriptId=:pscr AND status=6");
		$query->bindValue(":pscr", $pscr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }

	
	public function retrieveScriptApproved($scr) {
        $query = $this->con->prepare("SELECT * FROM scriptmaster WHERE scriptId=:scr AND status=14");
		$query->bindValue(":scr", $scr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrievePostScriptApproved($pscr) {
        $query = $this->con->prepare("SELECT * FROM postprocscriptmaster WHERE postScriptId=:pscr AND status=6 OR status=14");
		$query->bindValue(":pscr", $pscr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	
	
	public function retrievePostProcData($pvi) {
        $query = $this->con->prepare("SELECT * FROM postprocvideo WHERE postProcVideoId=:pvi AND status=1");
		$query->bindValue(":pvi", $pvi);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveScriptMain($scr) {
        $query = $this->con->prepare("SELECT * FROM scriptmaster WHERE scriptId=:scr AND status<4 AND status>1");
		$query->bindValue(":scr", $scr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrievePostScriptMain($pscr) {
        $query = $this->con->prepare("SELECT * FROM postprocscriptmaster WHERE postScriptId=:pscr AND status<4 AND status>1");
		$query->bindValue(":pscr", $pscr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveSubtitleMain($si) {
        $query = $this->con->prepare("SELECT * FROM subtitlemaster WHERE subtitleId=:si AND status<4 AND status>1");
		$query->bindValue(":si", $si);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function reviewedSubtitleMainAttach($si) {
        $query = $this->con->prepare("SELECT * FROM subtitlemaster WHERE subtitleId=:si AND status=5 OR status=7");
		$query->bindValue(":si", $si);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function reviewSubtitleMain($si) {
        $query = $this->con->prepare("SELECT * FROM subtitlemaster WHERE subtitleId=:si AND status=4 OR status=8");
		$query->bindValue(":si", $si);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function approvedSubtitleMain($si) {
        $query = $this->con->prepare("SELECT * FROM subtitlemaster WHERE subtitleId=:si AND status=6");
		$query->bindValue(":si", $si);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function reviewedSubtitleMain($si) {
        $query = $this->con->prepare("SELECT * FROM subtitlemaster WHERE subtitleId=:si AND status=5 OR status=7");
		$query->bindValue(":si", $si);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function assignSubtitleMain($si) {
        $query = $this->con->prepare("SELECT * FROM subtitleassign WHERE subtitleId=:si AND status=4 OR status=8");
		$query->bindValue(":si", $si);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveScriptReviewed($scr) {
        $query = $this->con->prepare("SELECT * FROM scriptmaster WHERE scriptId=:scr AND status=5 OR status=7");
		$query->bindValue(":scr", $scr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrievePostScriptReviewed($pscr) {
        $query = $this->con->prepare("SELECT * FROM postprocscriptmaster WHERE postScriptId=:pscr AND status=5 OR status=7");
		$query->bindValue(":pscr", $pscr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveAssignedScript($scr) {
        $query = $this->con->prepare("SELECT * FROM scriptassign WHERE scriptId=:scr");
		$query->bindValue(":scr", $scr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrievePrevAssignedScript($rwi) {
        $query = $this->con->prepare("SELECT * FROM scriptassign WHERE rawVideoId=:rwi");
		$query->bindValue(":rwi", $rwi);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveAssignedPostScript($pscr) {
        $query = $this->con->prepare("SELECT * FROM postprocscriptassign WHERE postScriptId=:pscr");
		$query->bindValue(":pscr", $pscr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveAssignedSubtitle($si) {
        $query = $this->con->prepare("SELECT * FROM subtitleassign WHERE subtitleId=:si");
		$query->bindValue(":si", $si);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveAssignedPostScriptMain($rvi) {
        $query = $this->con->prepare("SELECT * FROM postprocscriptassign WHERE rawVideoId=:rvi");
		$query->bindValue(":rvi", $rvi);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveAttachmentId($scr) {
        $query = $this->con->prepare("SELECT * FROM scriptattach WHERE scriptId=:scr ORDER BY scriptAttachId DESC");
		$query->bindValue(":scr", $scr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCommentId($scr) {
        $query = $this->con->prepare("SELECT * FROM scriptcomments WHERE scriptId=:scr ORDER BY scriptId DESC");
		$query->bindValue(":scr", $scr);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	
	
	
	public function retrieveRawVideo($rwi) {
        $query = $this->con->prepare("SELECT * FROM rawvideo WHERE rawVideoId=:rwi");
		$query->bindValue(":rwi", $rwi);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrievePostProcessVideo($pwi) {
        $query = $this->con->prepare("SELECT * FROM postprocvideo WHERE postProcVideoId=:pwi");
		$query->bindValue(":pwi", $pwi);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveFinishedVideoData($vi) {
        $query = $this->con->prepare("SELECT * FROM finishedvideo WHERE videoId=:vi");
		$query->bindValue(":vi", $vi);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveFinishedVideo($pwi) {
        $query = $this->con->prepare("SELECT * FROM finishedvideo WHERE postProcVideoId=:pwi");
		$query->bindValue(":pwi", $pwi);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrievePostProcVideo($rvi) {
        $query = $this->con->prepare("SELECT * FROM postprocvideo WHERE rawVideoId=:rvi");
		$query->bindValue(":rvi", $rvi);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveFinishedVideoMain($rvi) {
        $query = $this->con->prepare("SELECT * FROM finishedvideo WHERE rawVideoId=:rvi");
		$query->bindValue(":rvi", $rvi);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	
	/*Animator Index Page*/
	
	public function retrieveCountAcceptedVideo($ei) {
        $query = $this->con->prepare("SELECT count(*) as accept_vid FROM scriptassign WHERE animatorTo=:ai AND status=14");$query->bindValue(":ai", $ei); 	
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountUploadedVideo($ei) {
        $query = $this->con->prepare("SELECT count(*) as uploaded_vid FROM postprocvideo WHERE uploadedBy=:ai AND status=1");$query->bindValue(":ai", $ei); 	
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountReworkVideo($ei) {
        $query = $this->con->prepare("SELECT count(*) as rework_vid FROM postprocscriptassign WHERE status=6 OR status=14 AND assignedTo=:ai");
		$query->bindValue(":ai", $ei); 	
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountAnimFinVideo($ei) {
        $query = $this->con->prepare("SELECT count(*) as fin_vid FROM postprocscriptassign WHERE animatorTo=:ai AND status=19");
		$query->bindValue(":ai", $ei); 	
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	/*User Index Contents*/
	
	public function retrieveCountTotalAssignedScripts($ei) {
        $query = $this->con->prepare("SELECT count(*) as total_assigned_scripts FROM scriptassign WHERE assignedTo=:ato"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	public function retrieveCountUserAssignedScripts($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_assigned_scripts FROM scriptassign WHERE assignedTo=:ato AND status=2 OR status=3"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	public function retrieveCountUserReviewedScripts($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_reviewed_scripts FROM scriptassign WHERE assignedTo=:ato AND status=5 OR status=7"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	public function retrieveCountUserApprovedScripts($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_approved_scripts FROM scriptassign WHERE assignedTo=:ato AND (status=6 OR status=12 OR status=14 OR status=15)"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	/*Post Script*/
	
	public function retrieveCountTotalAssignedPostScripts($ei) {
        $query = $this->con->prepare("SELECT count(*) as total_assigned_postscripts FROM postprocscriptassign WHERE assignedTo=:ato"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	public function retrieveCountUserAssignedPostScripts($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_assigned_postscripts FROM postprocscriptassign WHERE assignedTo=:ato AND status=2 OR status=3"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	public function retrieveCountUserReviewedPostScripts($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_reviewed_postscripts FROM postprocscriptassign WHERE assignedTo=:ato AND status=5 OR status=7"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	public function retrieveCountUserApprovedPostScripts($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_approved_postscripts FROM postprocscriptassign WHERE assignedTo=:ato AND status=19"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	/*Subtitle*/
	public function retrieveCountTotalAssignedSubtitles($ei) {
        $query = $this->con->prepare("SELECT count(*) as total_assigned_subtitles FROM subtitleassign WHERE assignedTo=:ato"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	public function retrieveCountUserAssignedSubtitles($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_assigned_subtitles FROM subtitleassign WHERE assignedTo=:ato AND status=2 OR status=3"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	public function retrieveCountUserReviewedSubtitles($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_reviewed_subtitles FROM subtitleassign WHERE assignedTo=:ato AND status=5 OR status=7"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	public function retrieveCountUserApprovedSubtitles($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_approved_subtitles FROM subtitleassign WHERE assignedTo=:ato AND status=6"); 	
		$query->bindValue(":ato", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	public function retrieveCountUserMonthlyUsage($ei) {
        $query = $this->con->prepare("SELECT count(*) as user_usage FROM currentusers WHERE username=:un GROUP BY MONTH(loginDate)"); 	
		$query->bindValue(":un", $ei); 
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	/*Admin Index Page*/
	
	public function retrieveCountRawVideo() {
        $query = $this->con->prepare("SELECT count(id) as total_rw FROM rawvideo WHERE approved!=0"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountPostProcess() {
        $query = $this->con->prepare("SELECT count(id) as total_pps FROM postprocvideo WHERE approved!=0"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountFinishedVideo() {
        $query = $this->con->prepare("SELECT count(id) as total_fin FROM finishedvideo WHERE approved!=0"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountSubtitle() {
        $query = $this->con->prepare("SELECT count(id) as total_sub FROM subtitlemaster WHERE approved!=0"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	/*Script Details*/
	
	public function retrieveCountTotalScripts() {
        $query = $this->con->prepare("SELECT count(*) as total_scripts FROM scriptassign"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	public function retrieveCountCreatedScripts() {
        $query = $this->con->prepare("SELECT count(*) as created_scripts FROM scriptassign WHERE status=0 or status=1"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	public function retrieveCountScriptAssigned() {
        $query = $this->con->prepare("SELECT count(*) as script_assigned FROM scriptassign WHERE status=2"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountScriptCreated() {
        $query = $this->con->prepare("SELECT count(*) as script_created FROM scriptassign WHERE status<2"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountScriptWIP() {
        $query = $this->con->prepare("SELECT count(*) as script_wip FROM scriptassign WHERE status=3"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	
	public function retrieveCountScriptReviewSubmitted() {
        $query = $this->con->prepare("SELECT count(*) as script_review_sub FROM scriptassign WHERE status=4"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountScriptReviewed() {
        $query = $this->con->prepare("SELECT count(*) as script_reviewed FROM scriptassign WHERE status=5"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountScriptReReviewed() {
        $query = $this->con->prepare("SELECT count(*) as script_rereviewed FROM scriptassign WHERE status=7"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountScriptReviewResubmitted() {
        $query = $this->con->prepare("SELECT count(*) as script_review_resub FROM scriptassign WHERE status=8"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountScriptApproved() {
        $query = $this->con->prepare("SELECT count(*) as script_approved FROM scriptassign WHERE status=6"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountScriptAccepted() {
        $query = $this->con->prepare("SELECT count(*) as script_accepted FROM scriptassign WHERE status=14"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountScriptSubmitted() {
        $query = $this->con->prepare("SELECT count(*) as script_submitted FROM scriptassign WHERE status=15"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountScriptReported() {
        $query = $this->con->prepare("SELECT count(*) as script_reported FROM scriptassign WHERE status=9"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	/*Post Script Details*/
	
	public function retrieveCountTotalPostScripts() {
        $query = $this->con->prepare("SELECT count(*) as total_post_scripts FROM postprocscriptassign"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	public function retrieveCountCreatedPostScripts() {
        $query = $this->con->prepare("SELECT count(*) as created_post_scripts FROM postprocscriptassign WHERE status=0 or status=1"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	public function retrieveCountPostScriptAssigned() {
        $query = $this->con->prepare("SELECT count(*) as post_script_assigned FROM postprocscriptassign WHERE status=2"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountPostScriptWIP() {
        $query = $this->con->prepare("SELECT count(*) as post_script_wip FROM postprocscriptassign WHERE status=3"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
		
	public function retrieveCountPostScriptReviewSubmitted() {
        $query = $this->con->prepare("SELECT count(*) as post_script_review_sub FROM postprocscriptassign WHERE status=4"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountPostScriptReviewed() {
        $query = $this->con->prepare("SELECT count(*) as post_script_reviewed FROM postprocscriptassign WHERE status=5"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountPostScriptReReviewed() {
        $query = $this->con->prepare("SELECT count(*) as post_script_rereviewed FROM postprocscriptassign WHERE status=7");
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountPostScriptReviewResubmitted() {
        $query = $this->con->prepare("SELECT count(*) as post_script_review_resub FROM postprocscriptassign WHERE status=8"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountPostScriptAnimAssigned() {
        $query = $this->con->prepare("SELECT count(*) as post_script_anim_assigned FROM postprocscriptassign WHERE status=6"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountPostScriptAnimSubmitted() {
        $query = $this->con->prepare("SELECT count(*) as post_script_anim_submitted FROM postprocscriptassign WHERE status=15"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountPostScriptFinalized() {
        $query = $this->con->prepare("SELECT count(*) as post_script_finalized FROM postprocscriptassign WHERE status=19"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	/*Subtitle Details*/
	
	public function retrieveCountTotalSubtitles() {
        $query = $this->con->prepare("SELECT count(*) as total_subtitles FROM subtitleassign"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	public function retrieveCountCreatedSubtitles() {
        $query = $this->con->prepare("SELECT count(*) as created_subtitles FROM subtitleassign WHERE status=0 or status=1"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

     return false;
    }
	
	public function retrieveCountSubtitlesAssigned() {
        $query = $this->con->prepare("SELECT count(*) as subtitles_assigned FROM subtitleassign WHERE status=2"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }

	public function retrieveCountSubtitlesWIP() {
        $query = $this->con->prepare("SELECT count(*) as subtitles_wip FROM subtitleassign WHERE status=3"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	
	public function retrieveCountSubtitlesReviewSubmitted() {
        $query = $this->con->prepare("SELECT count(*) as subtitles_review_sub FROM subtitleassign WHERE status=4"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountSubtitlesReviewed() {
        $query = $this->con->prepare("SELECT count(*) as subtitles_reviewed FROM subtitleassign WHERE status=5"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountSubtitlesReReviewed() {
        $query = $this->con->prepare("SELECT count(*) as subtitles_rereviewed FROM subtitleassign WHERE status=7"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	public function retrieveCountSubtitlesReviewResubmitted() {
        $query = $this->con->prepare("SELECT count(*) as subtitles_review_resub FROM subtitleassign WHERE status=8"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	public function retrieveCountSubtitlesApproved() {
        $query = $this->con->prepare("SELECT count(*) as subtitles_approved FROM subtitleassign WHERE status=6"); 	
      
        $query->execute();
		 $result = array();
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	

	/*others*/
	public function retrieveStd($id) {
        $query = $this->con->prepare("SELECT * FROM std WHERE id=:id");
		$query->bindValue(":id", $id);
      
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
			}
            return $result;
        }

        return false;
    }
	
	
	public function getUserDetails($un) {
        $query = $this->con->prepare("SELECT * FROM users WHERE username=:un");
        $query->bindValue(":un", $un);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
	
	public function getUserInfo($ei) {
        $query = $this->con->prepare("SELECT * FROM users WHERE empId=:ei");
        $query->bindValue(":ei", $ei);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
	public function getRoleInfo($ri) {
        $query = $this->con->prepare("SELECT * FROM role WHERE id=:ri");
        $query->bindValue(":ri", $ri);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
	
	
/*Microsoft Tools for Education */	

	public function getUserProgrammes($un) {
        $query = $this->con->prepare("SELECT * FROM users_programmes WHERE username=:un");
        $query->bindValue(":un", $un);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
	
		public function getCoursesDetails($ci) {
        $query = $this->con->prepare("SELECT * FROM courses_master WHERE course_id=:ci");
        $query->bindValue(":ci", $ci);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
		public function getUserCoursesTools($ci) {
        $query = $this->con->prepare("SELECT * FROM tools_master WHERE course_id=:ci AND type=1");
        $query->bindValue(":ci", $ci);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }
	public function getUserCoursesQuizes($ci) {
        $query = $this->con->prepare("SELECT * FROM tools_master WHERE course_id=:ci AND type=2");
        $query->bindValue(":ci", $ci);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }

	public function getUserCoursesOthers($ci) {
        $query = $this->con->prepare("SELECT * FROM tools_master WHERE course_id=:ci AND type=3");
        $query->bindValue(":ci", $ci);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }

	
	
	public function getPreclassroomDetails($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_preclass_master WHERE tool_id=:ti");
        $query->bindValue(":ti", $ti);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
	}
		public function getCourseMaterials($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_course_material_master WHERE tool_id=:ti");
        $query->bindValue(":ti", $ti);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
	}
	public function getToolTitle($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_master WHERE tool_id=:ti");
        $query->bindValue(":ti", $ti);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }	

	public function getToolAssignment($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_assignment_master WHERE tool_id=:ti AND task_id=1");
        $query->bindValue(":ti", $ti);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }
		public function getToolAssignmentTwo($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_assignment_master WHERE tool_id=:ti AND task_id=2");
        $query->bindValue(":ti", $ti);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }
	
	public function getAssignmentHeading($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_assignment_heading WHERE tool_id=:ti AND task_id=1");
        $query->bindValue(":ti", $ti);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
		public function getAssignmentHeadingTwo($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_assignment_heading WHERE tool_id=:ti AND task_id=2");
        $query->bindValue(":ti", $ti);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
		public function getAssignmentRubrics($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_assignment_rubrics WHERE tool_id=:ti");
        $query->bindValue(":ti", $ti);
   
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }
	
		public function getAssignmentFileLink($ti,$un) {
        $query = $this->con->prepare("SELECT * FROM tools_assignment_filelink WHERE tool_id=:ti and username=:un");
        $query->bindValue(":ti", $ti);
		$query->bindValue(":un", $un);
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
	
	public function welcomePageContents($ci) {
        $query = $this->con->prepare("SELECT * FROM welcome_page_master WHERE course_id=:ci");
        $query->bindValue(":ci", $ci);
        $query->execute();
		$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }

public function getPaymentReceipts($ci,$un) {
        $query = $this->con->prepare("SELECT * FROM users_payments_courses WHERE course_id=:ci and username=:un");
        $query->bindValue(":ci", $ci);
	$query->bindValue(":un", $un);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }
public function getReceiptDetails($ri) {
        $query = $this->con->prepare("SELECT * FROM users_payments_courses WHERE receiptId=:ri");
        $query->bindValue(":ri", $ri);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }

public function getCertificates($ci,$un) {
        $query = $this->con->prepare("SELECT * FROM users_certificates WHERE course_id=:ci and username=:un and approved=1");
        $query->bindValue(":ci", $ci);
	$query->bindValue(":un", $un);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }

public function getCertificate($ci) {
        $query = $this->con->prepare("SELECT * FROM users_certificates WHERE certificateId=:ci and approved=1");
        $query->bindValue(":ci", $ci);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }


public function getBatchDetails($un,$ci) {
        $query = $this->con->prepare("SELECT * FROM user_batch_mapping WHERE course_id=:ci and username=:un");
        $query->bindValue(":ci", $ci);
	$query->bindValue(":un", $un);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }

public function checkRecordedVideo($ci,$bi) {
        $query = $this->con->prepare("SELECT * FROM recorded_video_master WHERE course_id=:ci and batch_unique_id=:bi");
        $query->bindValue(":ci", $ci);
	$query->bindValue(":bi", $bi);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }

public function getRecordedVideo($id) {
        $query = $this->con->prepare("SELECT * FROM recorded_video_master WHERE videoId=:id");
        $query->bindValue(":id", $id);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }


public function checkPreclassNotes($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_preclass_master WHERE tool_id=:ti");
        $query->bindValue(":ti", $ti);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
public function checkCourseMaterial($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_course_material_master WHERE tool_id=:ti");
        $query->bindValue(":ti", $ti);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }	
public function checkAssignment($ti) {
        $query = $this->con->prepare("SELECT * FROM tools_assignment_heading WHERE tool_id=:ti");
        $query->bindValue(":ti", $ti);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
public function checkQuiz($ti) {
        $query = $this->con->prepare("SELECT * FROM course_tool_quiz_master WHERE tool_id=:ti");
        $query->bindValue(":ti", $ti);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() >0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }


public function getQuizDetails($ti) {
        $query = $this->con->prepare("SELECT * FROM course_tool_quiz_master WHERE tool_id=:ti");
        $query->bindValue(":ti", $ti);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
public function getExamDetails($ci) {
        $query = $this->con->prepare("SELECT * FROM course_quiz_master WHERE course_id=:ci");
        $query->bindValue(":ci", $ci);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }

public function getQuizInfo($qi) {
        $query = $this->con->prepare("SELECT * FROM course_tool_quiz_master WHERE quiz_id=:qi");
        $query->bindValue(":qi", $qi);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
public function getExamInfo($qi) {
        $query = $this->con->prepare("SELECT * FROM course_quiz_master WHERE quiz_id=:qi");
        $query->bindValue(":qi", $qi);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() == 1) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result=$row;
       
			}
            return $result;
        }
        return false;
    }
public function getPreviousAttempts($un,$qi) {
        $query = $this->con->prepare("SELECT * FROM quiz_results WHERE username=:un and quiz_id=:qi ORDER BY created_at DESC LIMIT 3");
        $query->bindValue(":un", $un);
	$query->bindValue(":qi", $qi);
        $query->execute();
	$result = array();
		 
        if($query->rowCount() > 0) {
			
			while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $result[]=$row;
       
			}
            return $result;
        }
        return false;
    }
public function getExamStatus($un, $qi) {
    $query = $this->con->prepare("SELECT * FROM quiz_results WHERE username = :un AND quiz_id = :qi ORDER BY created_at DESC LIMIT 1");
    $query->bindParam(":un", $un);
    $query->bindParam(":qi", $qi);
    $query->execute();

    return $query->fetch(PDO::FETCH_ASSOC) ?: false; // Fetch result or return false if no data
}

public function getLeaderboard($quiz_id) {
    $query = $this->con->prepare("
        SELECT qr1.* FROM quiz_results qr1
        INNER JOIN (
            SELECT username, MAX(percentage) AS max_percentage, quiz_id
            FROM quiz_results
            WHERE status = 'Passed' AND quiz_id = :quiz_id
            GROUP BY username, quiz_id
        ) qr2 
        ON qr1.username = qr2.username 
        AND qr1.percentage = qr2.max_percentage 
        AND qr1.quiz_id = qr2.quiz_id
        WHERE qr1.status = 'Passed'
        ORDER BY qr1.percentage DESC, qr1.created_at DESC
    ");

    $query->bindParam(":quiz_id", $quiz_id);
    $query->execute();
    
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

public function getUserRank($quiz_id, $user_id) {
    // Check if user has any recorded percentage
    $userQuery = $this->con->prepare("
        SELECT percentage 
        FROM quiz_results 
        WHERE quiz_id = :quiz_id 
        AND username = :user_id 
        AND status = 'Passed'
        ORDER BY percentage DESC 
        LIMIT 1
    ");
    $userQuery->bindParam(":quiz_id", $quiz_id);
    $userQuery->bindParam(":user_id", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->fetch(PDO::FETCH_ASSOC);

    if (!$userResult) {
        return "Not Ranked"; // User has no passed attempts
    }

    $userPercentage = $userResult['percentage'];

    // Count users with higher percentage
    $rankQuery = $this->con->prepare("
        SELECT COUNT(*) + 1 AS rank 
        FROM quiz_results 
        WHERE quiz_id = :quiz_id 
        AND percentage > :user_percentage 
        AND status = 'Passed'
    ");
    $rankQuery->bindParam(":quiz_id", $quiz_id);
    $rankQuery->bindParam(":user_percentage", $userPercentage);
    $rankQuery->execute();

    $rankResult = $rankQuery->fetch(PDO::FETCH_ASSOC);
    return $rankResult ? $rankResult['rank'] : "Not Ranked";
}

	

}

?>