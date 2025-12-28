 <?php
	  require_once("../includes/config.php");
	  require_once("../includes/head_main.php");
	  require_once("../includes/classes/Account.php");
	  
	  // Check if user is logged in
	  if(!isset($_SESSION["username"]) || empty($_SESSION["username"])){
		  header("Location:../login.php");
		  exit;
	  }
	  
	  $account = new Account($con);
	  $id=$_GET['id'];
	  $pdfViewer = $account->getPreclassroomDetails($id);
	  $titleQuery = $account->getToolTitle($id);
	  $title=$titleQuery['tool_name'];
	?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
	<link id="style" href="../assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" />

	<!-- STYLE CSS -->
	<link href="../assets/css/style.css" rel="stylesheet" />
	<link href="../assets/css/skin-modes.css" rel="stylesheet" />

	<!--- FONT-ICONS CSS -->
	<link href="../assets/css/icons.css" rel="stylesheet" />

    <!-- INTERNAL Switcher css -->
    <link href="../assets/switcher/css/switcher.css" rel="stylesheet" />
    <link href="../assets/switcher/demo.css" rel="stylesheet" />
	
	
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AJAX Quiz</title>

  <style>
    .quiz-container {
      max-width: 800px;
      margin: auto;
      margin-top: 50px;
    }
    #question-numbers {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }
    #question-numbers button {
      margin: 0 5px;
    }
    #question-numbers button.attended {
      background-color: green;
      color: white;
    }
    #question-numbers button.unattended {
      background-color: white;
    }
    #question-numbers button.viewed {
      background-color: orange;
      color: white;
    }
  </style>
</head>
<body >

<div class="container quiz-container">
  <h2 class="text-center">AJAX Quiz</h2>
  <div id="question-numbers"></div>
  <div id="quiz" class="card">
    <div id="question-container" class="card-body">
      <h5 class="card-title" id="question">Loading question...</h5>
      <div id="options" class="form-check">
        <!-- Options will be inserted here using AJAX -->
      </div>
      <button id="prev-btn" class="btn btn-secondary mt-3" onclick="prevQuestion()" disabled>Previous</button>
      <button id="next-btn" class="btn btn-primary mt-3" onclick="nextQuestion()">Next</button>
      <div id="quiz-results" style="display: none;">
        <h4 class="mt-4">Quiz Results</h4>
        <div id="results-summary"></div>
        <canvas id="quiz-chart" width="400" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const quizData = [
    {
      question: "What is the capital of France?",
      options: ["Berlin", "Madrid", "Paris", "Rome"],
      correctAnswer: "Paris"
    },
    {
      question: "Which planet is known as the Red Planet?",
      options: ["Earth", "Mars", "Jupiter", "Venus"],
      correctAnswer: "Mars"
    },
    {
      question: "What is the largest mammal?",
      options: ["Elephant", "Whale", "Giraffe", "Hippopotamus"],
      correctAnswer: "Whale"
    },
    {
      question: "Which country is known as the Land of the Rising Sun?",
      options: ["China", "India", "Japan", "South Korea"],
      correctAnswer: "Japan"
    },
    {
      question: "Who wrote 'Romeo and Juliet'?",
      options: ["William Shakespeare", "Charles Dickens", "Jane Austen", "Mark Twain"],
      correctAnswer: "William Shakespeare"
    }
  ];

  let currentQuestion = 0;
  let userAnswers = new Array(quizData.length).fill(null);

  function loadQuestion() {
    const questionContainer = $("#question");
    const optionsContainer = $("#options");
    const questionNumbersContainer = $("#question-numbers");

    questionContainer.text(quizData[currentQuestion].question);
    optionsContainer.empty();
    questionNumbersContainer.empty();

    quizData.forEach((question, index) => {
      const questionNumberButton = $("<button>")
        .text(index + 1)
        .addClass("btn")
        .click(() => goToQuestion(index));

      if (userAnswers[index] !== null) {
        questionNumberButton.addClass("attended");
      } else if (index === currentQuestion) {
        questionNumberButton.addClass("viewed");
      } else {
        questionNumberButton.addClass("unattended");
      }

      questionNumbersContainer.append(questionNumberButton);
    });

    quizData[currentQuestion].options.forEach((option, index) => {
      const optionHtml = `
        <div class="form-check">
          <input class="form-check-input" type="radio" name="option" id="option${index}" value="${option}">
          <label class="form-check-label" for="option${index}">${option}</label>
        </div>
      `;
      optionsContainer.append(optionHtml);
    });

    const selectedAnswer = userAnswers[currentQuestion];
    if (selectedAnswer !== null) {
      $(`input[name='option'][value='${selectedAnswer}']`).prop("checked", true);
    }

    $("#prev-btn").prop("disabled", currentQuestion === 0);
  }

  function goToQuestion(questionIndex) {
    currentQuestion = questionIndex;
    loadQuestion();
  }

  function nextQuestion() {
    const selectedOption = $("input[name='option']:checked").val();

    if (!selectedOption) {
      alert("Please select an option");
      return;
    }

    userAnswers[currentQuestion] = selectedOption;

    currentQuestion++;
    if (currentQuestion < quizData.length) {
      loadQuestion();
    } else {
      showResults();
    }
  }

  function prevQuestion() {
    currentQuestion--;
    loadQuestion();
  }

  function showResults() {
    const resultsContainer = $("#quiz-results");
    const resultsSummary = $("#results-summary");
    resultsContainer.show();
    resultsSummary.empty();

    const correctAnswers = userAnswers.filter(
      (answer, index) => answer === quizData[index].correctAnswer
    ).length;

    quizData.forEach((question, index) => {
      const userAnswer = userAnswers[index];
      const correctAnswer = question.correctAnswer;
      const resultItem = `
        <div>
          <h5>Question ${index + 1}: ${question.question}</h5>
          <p>Your Answer: ${userAnswer}</p>
          <p>Correct Answer: ${correctAnswer}</p>
        </div>
      `;
      resultsSummary.append(resultItem);
    });

    const ctx = document.getElementById("quiz-chart").getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Correct Answers", "Incorrect Answers"],
        datasets: [{
          label: "Quiz Results",
          data: [correctAnswers, quizData.length - correctAnswers],
          backgroundColor: ["green", "red"]
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            max: quizData.length
          }
        }
      }
    });
  }

  loadQuestion();
</script>

	<script src="../assets/plugins/bootstrap/js/bootstrap.min.js"></script>



    <!-- COLOR THEME JS -->
    <script src="../assets/js/themeColors.js"></script>

	<!-- CUSTOM JS -->
	<script src="../assets/js/custom.js"></script>

	<!-- Ajax js -->
	<script src="../assets/ajax/ajax.js"></script>

    <!-- SWITCHER JS -->
    <script src="../assets/switcher/js/switcher.js"></script>
	<script>
        // Function to apply the received mode to the iframe's body
        function applyMode(event) {
            if (event.data && event.data.mode) {
                document.body.classList.remove('dark-mode', 'light-mode');
                document.body.classList.add(event.data.mode);
            }
        }

        // Listen for messages from the parent document
        window.addEventListener('message', applyMode, false);
    </script>

</body>
</html>
