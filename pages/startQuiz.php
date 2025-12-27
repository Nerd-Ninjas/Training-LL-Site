<!DOCTYPE html>
<html lang="en">
<?php 
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");
$account = new Account($con);
$tool_id=$_GET['id'];

$quizDetails=$account->getQuizDetails($tool_id);
$quiz_id = $quizDetails['quiz_id'];
$title = $quizDetails['title'];
$description = $quizDetails['description'];
$num_questions = $quizDetails['questions'];
$duration = $quizDetails['time']; // Assuming duration is in minutes
$category = $quizDetails['category']; // Example additional data
$passing_score = $quizDetails['passingScore']; // Example additional data
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Quiz</title>

</head>
<body>
    <div class="page-header">
      <div>
        <h1 class="page-title">Quiz</h1>
      </div>
      <div class="ms-auto pageheader-btn">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="javascript:void(0);">Menu</a>
          </li>
          <li class="breadcrumb-item active" aria-current="page">Quiz</li>
        </ol>
      </div>
    </div>
    <!-- PAGE-HEADER END -->
    <div class="row">
      <div class="card">

        <div class="card-body text-center">
            <h3><?php echo $title; ?></h3>
            <p><?php echo $description; ?></p>
			<div class="table-responsive">
            <table class="table text-nowrap text-md-nowrap table-bordered">
                <tr><td style="width:50%"><strong>Number of Questions:</strong></td><td> <?php echo $num_questions; ?></td></tr>
                <tr><td style="width:50%"><strong>Duration:</strong> </td><td><?php echo $duration; ?> minutes</td></tr>
                <tr><td style="width:50%"><strong>Passing Score:</strong></td><td> <?php echo $passing_score; ?>%</td></tr>
            </table>
			</div>
		<input type="hidden" id="quiz_id" value="<?php echo $quiz_id;?>">
        <input type="hidden" id="session_id" value="<?php echo $_SESSION['username'];?>">
        <button class="btn btn-primary" id="startQuiz">Start Quiz</button>
        </div>


    </div>

    <script>
        document.getElementById('startQuiz').addEventListener('click', function() {
           const quizId = encodeURIComponent(document.getElementById('quiz_id').value);
		   const sessionId = encodeURIComponent(document.getElementById('session_id').value);
		   const quizWindow = window.open(`pages/quiz_new.php?quiz_id=${quizId}&session_id=${sessionId}`, '_blank', 'width=800,height=600');
            quizWindow.addEventListener('load', () => {
                quizWindow.document.documentElement.requestFullscreen();
            });
        });

        // Simulate progress bar (for demo purposes)
        window.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('progressBar');
            setTimeout(() => {
                progressBar.style.width = '100%';
            }, 500);
        });
    </script>
</body>
</html>
