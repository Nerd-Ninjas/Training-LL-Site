<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userAnswers = json_decode($_POST['userAnswers'], true);
    $correctAnswers = json_decode($_POST['correctAnswers'], true);

    $correctCount = 0;
    foreach ($userAnswers as $index => $userAnswer) {
        if ($userAnswer === $correctAnswers[$index]) {
            $correctCount++;
        }
    }
    $incorrectCount = count($correctAnswers) - $correctCount;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz Results</title>
  <style>
    .results-container {
      max-width: 800px;
      margin: auto;
      margin-top: 50px;
    }
  </style>
</head>
<body>

<div class="container results-container">
  <h2 class="text-center">Quiz Results</h2>
  <div id="results-summary">
    <p>Correct Answers: <?php echo $correctCount; ?></p>
    <p>Incorrect Answers: <?php echo $incorrectCount; ?></p>
  </div>
  <canvas id="quiz-chart" width="400" height="200"></canvas>
</div>
<a href="../main.php?ci=CVBG">Home</a>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const correctCount = <?php echo $correctCount; ?>;
  const incorrectCount = <?php echo $incorrectCount; ?>;

  const ctx = document.getElementById("quiz-chart").getContext("2d");
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Correct Answers", "Incorrect Answers"],
      datasets: [{
        label: "Quiz Results",
        data: [correctCount, incorrectCount],
        backgroundColor: ["green", "red"]
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          max: correctCount + incorrectCount
        }
      }
    }
  });
</script>

</body>
</html>
