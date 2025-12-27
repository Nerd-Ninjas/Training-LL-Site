<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once("includes/config.php");
require_once("includes/classes/Account.php");
$account = new Account($con);
$lastActive=date('Y-m-d H:i:s', $_SESSION["last_login_timestamp"]);
$result=$account->logoutConfirm($_SESSION["username"],$_SESSION["keyval"],$lastActive);
if($result){
unset($_SESSION["username"]);
unset($_SESSION["keyval"]);
unset($_SESSION["last_login_timestamp"]);
//$result=$account->logoutTamil($username,$otp);
header("Location: index.php");
exit;
}
?>