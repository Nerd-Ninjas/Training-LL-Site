<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");
require_once("../includes/classes/FormSanitizer.php");
$account = new Account($con);

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

$user_id = $conn->real_escape_string($_SESSION['username']);
$quiz_id = $conn->real_escape_string($_GET['quiz_id']);
$attempt_id = $conn->real_escape_string($_GET['attempt_id']);
$quizDetails=$account->getQuizInfo($quiz_id);
if(!$quizDetails){
$quizDetails=$account->getExamInfo($quiz_id);
}


// Fetch user answers
$sql = "SELECT question_id, selected_option FROM user_answers WHERE user_id = ? AND quiz_id = ? AND attempt_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $user_id, $quiz_id, $attempt_id);
$stmt->execute();
$result = $stmt->get_result();
$user_answers = [];
while ($row = $result->fetch_assoc()) {
    $user_answers[$row['question_id']] = $row['selected_option'];
}
$stmt->close();

// Fetch correct answers, explanation, and statistics for each question
$sql = "SELECT q.id, q.correct_option, q.explanation, 
       COUNT(ua.selected_option) AS total_attempts, 
       SUM(CASE WHEN ua.selected_option = q.correct_option THEN 1 ELSE 0 END) AS correct_count
        FROM questions q 
        LEFT JOIN user_answers ua ON q.id = ua.question_id
        WHERE q.quiz_id = ?
        GROUP BY q.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$correct_answers = [];
while ($row = $result->fetch_assoc()) {
    $correct_answers[$row['id']] = [
        'option' => $row['correct_option'],
        'explanation' => $row['explanation'],
        'correct_count' => $row['correct_count'],
        'total_attempts' => $row['total_attempts']
    ];
}
$stmt->close();

// Fetch all questions
$sql = "SELECT id, question_text, option_a, option_b, option_c, option_d FROM questions WHERE quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();

$conn->close();

// Calculate the score
$score = 0;
foreach ($user_answers as $question_id => $selected_option) {
    if ($selected_option == $correct_answers[$question_id]['option']) {
        $score++;
    }
}

$total_questions = count($questions);
$correct_percentage = ($score / $total_questions) * 100;
$pass_percentage = $quizDetails['passingScore'];
$pass_fail = $correct_percentage >= $pass_percentage ? "Passed" : "Failed";
$pass_fail_class = $correct_percentage >= $pass_percentage ? "pass" : "fail";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        #summaryContainer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        #summaryContainer h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
        }
        #summaryContainer p {
            margin: 10px 0;
            font-size: 20px;
        }
        #summaryContainer p.pass {
            color: #4CAF50;
            font-weight: bold;
        }
        #summaryContainer p.fail {
            color: #FF5252;
            font-weight: bold;
        }
        #chartContainer {
            width: 300px;
            height: 300px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        #answersContainer {
            width: 100%;
        }
        .question {
            background: #fff;
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .question h2 {
            margin-top: 0;
            font-size: 18px;
        }
        .question p {
            margin: 10px 0;
            font-size: 16px;
        }
        .status-label {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 14px;
            color: #fff;
        }
        .correct {
            background-color: #4CAF50;
        }
        .wrong {
            background-color: #FF5252;
        }
        .export-pdf {
            text-align: right;
            margin: 20px 0;
        }
        .export-pdf button {
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="https://training.learnlike.in/assets/images/brand/logo-big.png" style="width:180px;height:50px;" class="header-brand-img desktop-logo" alt="logo">
    </div>
    <div class="export-pdf">
        <button onclick="exportAsPDF()">Export as PDF</button>
    </div>
<?php   $userDetails=$account->getUserDetails($user_id); 
	$First_Name=$userDetails['firstName'];
	$Last_Name=$userDetails['lastName'];

?>

    <div id="summaryContainer">
        <div>
            <h2><?php echo $quizDetails['title']; ?> - Results (Attempt ID: <?php echo $attempt_id; ?>)</h2>
	    <p>Name: <b><?php echo $First_Name.' '.$Last_Name; ?></b></p>	
            <p>Score: <?php echo $score; ?> / <?php echo $total_questions; ?></p>
            <p>Percentage: <?php echo number_format($correct_percentage, 2); ?>%</p>
            <p class="<?php echo $pass_fail_class; ?>">Status: <strong><?php echo $pass_fail; ?></strong></p>
        </div>
        <div id="chartContainer">
            <canvas id="resultsChart"></canvas>
        </div>
    </div>
    <div id="answersContainer">
        <?php foreach ($questions as $index => $question): 
            $user_answer = htmlspecialchars($user_answers[$question['id']] ?? 'Not Answered');
            $correct_answer = htmlspecialchars($correct_answers[$question['id']]['option']);
            $is_correct = $user_answer == $correct_answer;
            $status_class = $is_correct ? 'correct' : 'wrong';
            $status_text = $is_correct ? 'Correct' : 'Wrong';
            $correct_count = $correct_answers[$question['id']]['correct_count'];
            $total_attempts = $correct_answers[$question['id']]['total_attempts'];
            $correct_percentage = ($correct_count / $total_attempts) * 100;
        ?>
            <div class="question">
                <h2><b>Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question_text']); ?></b></h2>
                <p>Your answer: <?php echo $user_answer . ' (' . htmlspecialchars($question['option_' . strtolower($user_answer)]) . ')'; ?></p>
                <p>Correct answer: <?php echo $correct_answer . ' (' . htmlspecialchars($question['option_' . strtolower($correct_answer)]) . ')'; ?></p>
                <p><strong>Explanation:</strong> <?php echo htmlspecialchars($correct_answers[$question['id']]['explanation']); ?></p>
                <p><?php echo number_format($correct_percentage, 2); ?>% of users answered this question correctly.</p>
                <div class="status-label <?php echo $status_class; ?>"><?php echo $status_text; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        const ctx = document.getElementById('resultsChart').getContext('2d');
        const data = {
            labels: ['Correct', 'Wrong'],
            datasets: [{
                label: 'Quiz Results',
                data: [<?php echo $score; ?>, <?php echo $total_questions - $score; ?>],
                backgroundColor: ['#4CAF50', '#FF5252'],
                borderWidth: 1
            }]
        };
        const config = {
            type: 'pie',
            data: data,
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw;
                                label += ' (';
                                label += Math.round(context.raw / <?php echo $total_questions; ?> * 100);
                                label += '%)';
                                return label;
                            }
                        }
                    }
                }
            }
        };
        new Chart(ctx, config);

        function exportAsPDF() {
            window.print();
        }
    </script>
</body>
</html>
