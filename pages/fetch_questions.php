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

if (isset($_GET['quiz_id'])) {
    $quiz_id = $_GET['quiz_id']; // No need to cast to integer, as it is alphanumeric

    $sql = "SELECT * FROM questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $quiz_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $questions = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($questions);
    } else {
        echo "Error executing query: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "quiz_id parameter is missing.";
}

$conn->close();
?>
