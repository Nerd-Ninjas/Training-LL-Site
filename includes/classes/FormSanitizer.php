<?php
class FormSanitizer {

   public static function sanitizeFormString($inputText) {
        $inputText = strip_tags($inputText);
        //$inputText = str_replace(" ", "", $inputText);
		$inputText = trim($inputText);
        //$inputText = strtolower($inputText);
        //$inputText = ucfirst($inputText);
        return $inputText;
    }

    public static function sanitizeFormUser($inputText) {
        $inputText = strip_tags($inputText);
        $inputText = str_replace(" ", "", $inputText);
        return $inputText;
    }

    public static function sanitizeFormPassword($inputText) {
        $inputText = strip_tags($inputText);
        return $inputText;
    }

    public static function sanitizeFormEmail($inputText) {
        $inputText = strip_tags($inputText);
        $inputText = str_replace(" ", "", $inputText);
        return $inputText;
    }
	
	public static function sanitizeFormMobile($inputText) {
        $inputText = strip_tags($inputText);
        $inputText = str_replace(" ", "", $inputText);
        return $inputText;
    }
	
	public static function sanitizeFormUsername() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 15; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    $inputText = strip_tags(implode($pass)); //turn the array into a string
	$inputText = str_replace(" ", "", $inputText);
    return $inputText;			
	}
	
	public static function sanitizeFormSessionName() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 6; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    $inputText = strip_tags(implode($pass)); //turn the array into a string
	$inputText = str_replace(" ", "", $inputText);
    return $inputText;			
	}

}
?>