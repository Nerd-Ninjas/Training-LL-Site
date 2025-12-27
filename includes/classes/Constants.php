<?php
class Constants {
    public static $firstNameCharacters = "Your first name must be between 2 and 25 characters";
	public static $firstNameTamilCharacters="Your First Name cannot be blank";
	public static $lastNameTamilCharacters="Your Last Name cannot be blank";
    public static $lastNameCharacters = "Your last name must be between 2 and 25 characters";
    public static $usernameCharacters = "Your username must be between 2 and 25 characters";
    public static $usernameTaken = "Username already in use- Kindly Re-Submit";
    public static $emailsDontMatch = "Your emails don't match";
    public static $emailInvalid = "Invalid email";
    public static $emailTaken = "Email already in use";
    public static $passwordsDontMatch = "Passwords don't match";
    public static $passwordLength = "Your password must be between 5 and 25 characters";
    public static $loginFailed = "Your username or password was incorrect";
    public static $passwordIncorrect = "Your old password is incorrect";
	public static $mobileNumberWronglength="Mobile Number should be 10 digits";
	public static $mobileNumberTaken = "Mobile Number is already in use";
	public static $otpLength="OTP is a 6 digit number";
	public static $wrongOtp="Wrong OTP";
	public static $correctOTP="OTP Verified";
	public static $stdRequired="Standard Required";
	public static $genderRequired="Gender Required";
	public static $mobileNumberNotExist="Your Mobile number has not been registered";
	public static $avatarFailed="Avatar Fetch failed";
	public static $falseEntry="False Entry tried";
	public static $subjectFetch="Subject Fetch Failed";
}
?>