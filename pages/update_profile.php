<?php
require_once("../includes/config.php");
require_once("../includes/classes/FormSanitizer.php");

// Check if user is logged in
if(!isset($_SESSION["username"]) || empty($_SESSION["username"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if this is an AJAX request
if($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get current user ID from session
$user_id = $_SESSION['id'] ?? null;
if(!$user_id) {
    // Try to get user ID from username
    try {
        $user_query = $con->prepare("SELECT id FROM users WHERE username = ?");
        $user_query->execute([$_SESSION['username']]);
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        $user_id = $user['id'] ?? null;
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
}

if(!$user_id) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

if($_POST['action'] === 'update_profile') {
    try {
        // Sanitize inputs
        $firstName = FormSanitizer::sanitizeFormString($_POST['firstName'] ?? '');
        $lastName = FormSanitizer::sanitizeFormString($_POST['lastName'] ?? '');
        $email = FormSanitizer::sanitizeFormString($_POST['email'] ?? '');
        $mobileNumber = FormSanitizer::sanitizeFormString($_POST['mobileNumber'] ?? '');
        
        $address = FormSanitizer::sanitizeFormString($_POST['address'] ?? '');
        $city = FormSanitizer::sanitizeFormString($_POST['city'] ?? '');
        $state = FormSanitizer::sanitizeFormString($_POST['state'] ?? '');
        $country = FormSanitizer::sanitizeFormString($_POST['country'] ?? '');
        $postalCode = FormSanitizer::sanitizeFormString($_POST['postalCode'] ?? '');
        
        $occupation = FormSanitizer::sanitizeFormString($_POST['occupation'] ?? '');
        $collegeName = FormSanitizer::sanitizeFormString($_POST['collegeName'] ?? '');
        
        // Update users table (basic info)
        $update_user = $con->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ?, mobileNumber = ? WHERE id = ?");
        if(!$update_user->execute([$firstName, $lastName, $email, $mobileNumber, $user_id])) {
            throw new Exception("Failed to update user information");
        }
        
        // Check if user_personal_info exists
        $check = $con->prepare("SELECT id FROM user_personal_info WHERE user_id = ?");
        $check->execute([$user_id]);
        $exists = $check->fetch(PDO::FETCH_ASSOC);
        
        if($exists) {
            // Update existing record
            $update_personal = $con->prepare("UPDATE user_personal_info SET address = ?, city = ?, state = ?, country = ?, postal_code = ?, occupation = ?, college_name = ? WHERE user_id = ?");
            if(!$update_personal->execute([$address, $city, $state, $country, $postalCode, $occupation, $collegeName, $user_id])) {
                throw new Exception("Failed to update personal information");
            }
        } else {
            // Insert new record
            $insert_personal = $con->prepare("INSERT INTO user_personal_info (user_id, address, city, state, country, postal_code, occupation, college_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if(!$insert_personal->execute([$user_id, $address, $city, $state, $country, $postalCode, $occupation, $collegeName])) {
                throw new Exception("Failed to add personal information");
            }
        }
        
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        exit;
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action']);
