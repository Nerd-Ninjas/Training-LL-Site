<!DOCTYPE html>
<html lang="en">
<?php 
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");
$account = new Account($con);
$tool_id=$_GET['id'];

$quizDetails=$account->getExamDetails($tool_id);
$quiz_id = $quizDetails['quiz_id'];
$title = $quizDetails['title'];
$description = $quizDetails['description'];
$num_questions = $quizDetails['questions'];
$duration = $quizDetails['time']; // Assuming duration is in minutes
$category = $quizDetails['category']; // Example additional data
$passing_score = $quizDetails['passingScore']; // Example additional data
$user_id=$_SESSION['username'];
$examStatus=$account->getExamStatus($user_id,$quiz_id);

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Quiz</title>

</head>
<body>
    <div class="page-header">
      <div>
        <h1 class="page-title">Course Certification Exam</h1>
      </div>
      <div class="ms-auto pageheader-btn">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <a href="javascript:void(0);">Exam / Quiz</a>
          </li>
	  <li class="breadcrumb-item">
            <a href="javascript:void(0);">Exam</a>
          </li>

          <li class="breadcrumb-item active" aria-current="page">Course Certification Exam</li>
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
		<?php if($examStatus){ ?>
		<tr><td style="width:50%"><strong>Your Score:</strong></td><td style="color: <?php echo ($examStatus['status'] === 'Passed') ? 'green' : 'red'; ?>;font-weight:bold;"><?php echo $examStatus['percentage']; ?>%</td></tr>
		<tr><td style="width:50%"><strong>Status</strong></td><td style="color: <?php echo ($examStatus['status'] === 'Passed') ? 'green' : 'red'; ?>;font-weight:bold;"> <?php echo $examStatus['status']; ?></td></tr>
		
		<?php }else{ ?>
		<tr><td style="width:50%"><strong>Status</strong></td><td>Not Attended</td></tr>
		<?php } ?>
		<?php if($examStatus['status'] =='Passed'){ ?>

		<tr><td style="width:50%"><strong>Certificate</strong></td><td> <a href="certificate_list.php?id=<?php echo $tool_id;?>&&ui=<?php echo $user_id;?>">Click Here</a></td></tr>			
		<?php } ?>
            </table>
			</div>
		<input type="hidden" id="quiz_id" value="<?php echo $quiz_id;?>">
        <input type="hidden" id="session_id" value="<?php echo $_SESSION['username'];?>">
	<?php if($examStatus['status']=='Passed'){ ?>
        <button class="btn btn-primary" id="startQuiz" disabled>Start Exam</button>
	<?php }elseif($examStatus['status']=='Failed'){ ?>
	<button class="btn btn-primary" id="startQuiz">Retake Exam</button>
	<?php }else{ ?>
	<button class="btn btn-primary" id="startQuiz">Start Exam</button>
	<?php } ?>
        </div>


    </div>
<?php
$user_id=$_SESSION['username']; 
$previousAttempts=$account->getPreviousAttempts($user_id,$quiz_id);
?>
    <div class="row">
      <div class="card">

        <div class="card-body text-center">
	        <h3>Exam Results</h3>
			<div class="table-responsive">
            			<table class="table text-nowrap text-md-nowrap table-bordered">
    <thead>
        <tr>
            <th>#</th>
	    <th>Date & Time</th>	
            <th>Attempt Id</th>
            <th>Score</th>
            <th>Total Questions</th>
            <th>Percentage</th>
            <th>Status</th>
	    <th>View</th> 	
        </tr>
    </thead>
    <tbody>
        <?php 
        $i = 1;
        foreach ($previousAttempts as $attempt) { ?>
            <tr>
                <td><?php echo $i; ?></td>
		<td><?php echo htmlspecialchars($attempt['created_at']); ?></td>
                <td><?php echo htmlspecialchars($attempt['attempt_id']); ?></td>
                <td><?php echo htmlspecialchars($attempt['score']); ?></td>
                <td><?php echo htmlspecialchars($attempt['total_questions']); ?></td>
                <td><?php echo htmlspecialchars($attempt['percentage']); ?></td>
		<td style="color: <?php echo ($attempt['status'] === 'Passed') ? 'green' : 'red'; ?>;">
    			<?php echo htmlspecialchars($attempt['status']); ?>
		</td>
		<td>
                  <a href="startExam.php?id=<?php echo $tool_id; ?>" onclick="openResultsPopup('<?php echo $attempt['attempt_id']; ?>', '<?php echo $attempt['quiz_id']; ?>')"
 	class="btn btn-icon btn-primary">
                    <i class="fe fe-eye"></i>
                  </a>
                </td>

            </tr>
        <?php $i++; } ?>
    </tbody>
</table>
			</div>

	
	</div>
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
<script>
function openResultsPopup(attemptId,quizId) {
    var popup = window.open("pages/view_results.php?attempt_id=" + attemptId + "&quiz_id=" + quizId, "", "width=800,height=600,scrollbars=no,resizable=no");
    if (popup) {
        popup.focus();
    } else {
        alert("Please allow pop-ups for this website.");
    }
}
</script>

</body>
</html>
