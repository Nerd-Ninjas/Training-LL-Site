<?php
$servername = "localhost";
$username = "superdeepakrs";
$password = "qM33Jh0MHSp&VpBLhwqg&tAz*f1tBZ#dvySlivBS";
$dbname = "learnlikes_gurukulam";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the POST variables are set and are of the correct type
if (isset($_POST['user_id']) && isset($_POST['attempt_id'])&& isset($_POST['quiz_id']) && isset($_POST['question_id']) && isset($_POST['selected_option'])) {
    $user_id = $_POST['user_id']; // No need to cast to integer, as it is alphanumeric
    $attempt_id = $_POST['attempt_id']; 
    $quiz_id = $_POST['quiz_id']; 
    $question_id = intval($_POST['question_id']);
    $selected_option = $_POST['selected_option'];
    
    // Log received data
    error_log("Received data: user_id = $user_id, attempt_id = $attempt_id, quiz_id = $quiz_id, question_id = $question_id, selected_option = $selected_option");

    // Validate the selected option
    if (in_array($selected_option, ['A', 'B', 'C', 'D'])) {
        $sql = "INSERT INTO user_answers (quiz_id, attempt_id, user_id, question_id, selected_option) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            error_log("Statement prepared successfully");
            $stmt->bind_param("sssis", $quiz_id, $attempt_id, $user_id, $question_id, $selected_option);

            if ($stmt->execute()) {
                echo "Answer recorded successfully";
                error_log("Answer recorded successfully: user_id = $user_id, attempt_id = $attempt_id, quiz_id = $quiz_id, question_id = $question_id, selected_option = $selected_option");
            } else {
                echo "Error executing statement: " . $stmt->error;
                error_log("Error executing statement: " . $stmt->error);
            }

            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
            error_log("Error preparing statement: " . $conn->error);
        }
    } else {
        echo "Invalid selected option value.";
        error_log("Invalid selected option value: $selected_option");
    }
} else {
    echo "Required POST variables are not set.";
    error_log("Required POST variables are not set.");
}

$conn->close();
?>
