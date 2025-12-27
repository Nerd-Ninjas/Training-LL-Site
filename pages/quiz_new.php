<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cybervault's Training Dashboard - Quiz</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
   body {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0;
    padding-top: 90px; /* Pushes content below the fixed header */
    width: 100%;
}

/* Header Styling */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 80px;  /* Ensure consistent height */
    background-color: #fff;
    z-index: 1000;
    width: 100%;
}

/* Logo Adjustments */
.left-logo, .right-logo {
    width: 120px;
    height: auto;
}

.title {
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    flex-grow: 1;
    padding: 0 10px;
}

/* Quiz Container */
#quizContainer {
    position: relative;
    display: flex;
    flex-wrap: wrap;
    width: 90%;
    max-width: 1200px;
    justify-content: space-between;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    min-height: 500px; /* Prevents shrinking */
}

 /* Question Content */
#questionContent {
    flex: 1;
    padding: 20px;
    min-width: 300px;
}

        #questionContent h2 {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        #questionContent form label {
            display: block;
            margin: 10px 0;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        #questionContent form label input {
            margin-right: 10px;
        }
/* Navigation Section */
#questionNavContainer {
    width: 250px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    border-left: 1px solid #ddd;
}   
#timer {
            text-align: left;
            font-size: 1.2em;
            margin-bottom: 20px;
            padding: 10px;
            width: 100%;
        }
        #time {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
        }

        /* Updated styles for 5 questions per row */
/* Question Navigation Grid */
#questionNav {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 8px;
    margin-bottom: 10px;
    width: 100%;
}
/* Buttons inside Question Navigation */
#questionNav button {
    width: 100%;
    padding: 10px;
    font-size: 0.8em;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-align: center;
}

        #questionNav button.attended {
            background-color: #4CAF50;
            color: #fff;
        }
        #questionNav button.unattended {
            background-color: #FF9F9F;
            color: #fff;
        }
        #questionNav button.watched {
            background-color: #EEDD00;
            color: #fff;
        }
        #legend {
            text-align: left;
            margin-top: 10px;
            width: 100%;
        }
        #legend div {
            display: flex;
            align-items: center;
            margin: 5px 0;
        }
        #legend span {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border-radius: 4px;
        }
        #legend .attended {
            background-color: #4CAF50;
        }
        #legend .unattended {
            background-color: #FF9F9F;
        }
        #legend .watched {
            background-color: #EEDD00;
        }
        #navigation {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            width: 100%;
        }
        #navigation button {
            padding: 10px 20px;
            font-size: 1em;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 10px;
        }
        #prevBtn {
            background-color: #2196F3;
            color: #fff;
        }
        #nextBtn {
            background-color: #4CAF50;
            color: #fff;
        }
        #submitModal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }
        #submitModalContent {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
        }
        #submitModalClose {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        #submitModalClose:hover,
        #submitModalClose:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
/* Responsive Fixes */
@media screen and (max-width: 768px) {
    .header {
        flex-direction: column;
        height: auto;
        text-align: center;
        padding: 10px;
    }

    .left-logo, .right-logo {
        width: 100px;
    }

    .title {
        font-size: 18px;
    }

    body {
        padding-top: 100px; /* Pushes content further down */
    }

    #quizContainer {
        flex-direction: column;
        align-items: center;
        width: 95%;
        padding: 15px;
    }

    #questionNavContainer {
        width: 100%;
        border-left: none;
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }

    #questionNav {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media screen and (max-width: 480px) {
    body {
        padding-top: 120px; /* Pushes further on small screens */
    }

    #quizContainer {
        min-height: 600px; /* Prevents overlap by increasing height */
    }

    #questionNav {
        grid-template-columns: repeat(2, 1fr);
    }
}    
</style>
</head>
<?php
require_once("../includes/config.php");
require_once("../includes/classes/Account.php");
require_once("../includes/classes/FormSanitizer.php");
$user_id = $_GET['session_id'];
$quiz_id = $_GET['quiz_id'];
$account = new Account($con);
$quizDetails=$account->getQuizInfo($quiz_id);
if(!$quizDetails){
$quizDetails=$account->getExamInfo($quiz_id);
}
$time=$quizDetails['time']*60;
$attempt_Id = FormSanitizer::sanitizeFormSessionName();
?>
<body>       
<div class="header">
    <img src="https://training.learnlike.in/assets/images/brand/logo-big.png" class="header-brand-img right-logo" alt="logo">
</div>
<div class="title"><?php echo $quizDetails['title']; ?></div>

    <div id="quizContainer">
	
        <div id="questionContent"></div>
        <div id="questionNavContainer">
            <div id="timer">Time left:<span id="time"></span></div>
            <div id="questionNav"></div>
            <div id="legend">
                <div><span class="attended"></span>Answered</div>
                <div><span class="watched"></span>Viewed</div>
                <div><span class="unattended"></span>Unattended</div>
            </div>
        </div>
    </div>
    <div id="navigation">
        <button id="prevBtn">Previous</button>
        <button id="nextBtn">Next</button>
    </div>
    <div id="submitModal">
        <div id="submitModalContent">
            <span id="submitModalClose">&times;</span>
            <p>Are you sure you want to submit the quiz?</p>
            <button class="btn btn-success" id="confirmSubmitBtn">Yes, submit</button>
            <button class="btn btn-danger" id="cancelSubmitBtn">No, go back</button>
        </div>
    </div>


<script>
    const userId = "<?php echo $user_id; ?>";
    console.log("User Id:", userId);
    const attemptId = "<?php echo $attempt_Id; ?>";
    let questions = [];
    let currentQuestionIndex = 0;
    let userAnswers = {};
    let viewedQuestions = new Set(); // Track viewed questions
    const totalQuizTime = "<?php echo $time; ?>";
    const urlParams = new URLSearchParams(window.location.search);
    const quizId = urlParams.get('quiz_id');

    document.addEventListener('DOMContentLoaded', function () {
        fetchQuestions();
        startTimer(totalQuizTime);
        setupModal();
    });

    function fetchQuestions() {
        fetch(`fetch_questions.php?quiz_id=${encodeURIComponent(quizId)}`)
            .then(response => response.json())
            .then(data => {
                questions = data;
                displayQuestion(currentQuestionIndex);
                displayQuestionNav();
            })
            .catch(error => console.error('Error fetching questions:', error));
    }

    function displayQuestion(index) {
        const questionContent = document.getElementById('questionContent');
        const question = questions[index];

        questionContent.innerHTML = `
            <h4><strong>Question ${index + 1}</strong> : ${question.question_text}</h4>
            <form style="padding-top:10px;">
                <label><input type="radio" name="option${question.id}" value="A"> ${question.option_a}</label>
                <label><input type="radio" name="option${question.id}" value="B"> ${question.option_b}</label>
                <label><input type="radio" name="option${question.id}" value="C"> ${question.option_c}</label>
                <label><input type="radio" name="option${question.id}" value="D"> ${question.option_d}</label>
            </form>
        `;

        const options = document.getElementsByName(`option${question.id}`);
        options.forEach(option => {
            option.addEventListener('change', function () {
                userAnswers[question.id] = option.value;
                updateQuestionNav();
            });
        });

        if (userAnswers[question.id]) {
            options.forEach(option => {
                if (option.value === userAnswers[question.id]) {
                    option.checked = true;
                }
            });
        }

        updateQuestionNav(); // Update navigation buttons to reflect changes immediately
    }

    function displayQuestionNav() {
        const questionNav = document.getElementById('questionNav');
        questionNav.innerHTML = '';

        questions.forEach((question, index) => {
            const button = document.createElement('button');
            button.textContent = index + 1;
            button.classList.add(getQuestionStatus(index));
            button.addEventListener('click', function () {
                markAsWatched(currentQuestionIndex);
                currentQuestionIndex = index;
                displayQuestion(index);
            });
            questionNav.appendChild(button);
        });

        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        prevBtn.addEventListener('click', function () {
            if (currentQuestionIndex > 0) {
                markAsWatched(currentQuestionIndex);
                currentQuestionIndex--;
                displayQuestion(currentQuestionIndex);
            }
        });

        nextBtn.addEventListener('click', function () {
            if (currentQuestionIndex < questions.length - 1) {
                markAsWatched(currentQuestionIndex);
                currentQuestionIndex++;
                displayQuestion(currentQuestionIndex);
            } else {
                showSubmitModal();
            }
        });

        updateNextButton();
    }

    function markAsWatched(index) {
        const questionId = questions[index].id;
        viewedQuestions.add(questionId);
    }

    function getQuestionStatus(index) {
        const questionId = questions[index].id;
        if (userAnswers[questionId]) {
            return 'attended';
        } else if (viewedQuestions.has(questionId)) {
            return 'watched';
        } else {
            return 'unattended';
        }
    }

    function updateQuestionNav() {
        const buttons = document.getElementById('questionNav').children;
        for (let i = 0; i < buttons.length; i++) {
            const newStatus = getQuestionStatus(i);
            if (buttons[i].classList[0] !== newStatus) {
                buttons[i].classList.remove('attended', 'watched', 'unattended');
                buttons[i].classList.add(newStatus);
            }
        }
        updateNextButton();
    }

    function updateNextButton() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        
        // Handle Previous Button
        if (currentQuestionIndex === 0) {
            prevBtn.disabled = true;
            prevBtn.style.backgroundColor = "#ccc"; // Change color to indicate it's disabled
        } else {
            prevBtn.disabled = false;
            prevBtn.style.backgroundColor = ""; // Revert to default color
        }

        // Handle Next Button
        if (currentQuestionIndex === questions.length - 1) {
            nextBtn.textContent = 'Submit';
        } else {
            nextBtn.textContent = 'Next';
        }
    }

    function startTimer(duration) {
        let timer = duration, minutes, seconds;
        const display = document.getElementById('time');
        
        const interval = setInterval(function () {
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            display.textContent = minutes + ":" + seconds;

            if (--timer < 0) {
                clearInterval(interval);
                submitQuiz();
            }
        }, 1000);
    }

    function setupModal() {
        const modal = document.getElementById('submitModal');
        const span = document.getElementById('submitModalClose');
        const confirmBtn = document.getElementById('confirmSubmitBtn');
        const cancelBtn = document.getElementById('cancelSubmitBtn');

        span.onclick = function() {
            modal.style.display = "none";
        }

        cancelBtn.onclick = function() {
            modal.style.display = "none";
        }

        confirmBtn.onclick = function() {
            submitQuiz();
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }

    function showSubmitModal() {
        const modal = document.getElementById('submitModal');
        modal.style.display = "block";
    }

    function submitQuiz() {
        const promises = [];
        for (const questionId in userAnswers) {
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('attempt_id', attemptId);
            formData.append('quiz_id', quizId);
            formData.append('question_id', questionId);
            formData.append('selected_option', userAnswers[questionId]);
            
            const promise = fetch('record_answer.php', {
                method: 'POST',
                body: formData
            }).catch(error => console.error('Error recording answer:', error));

            promises.push(promise);
        }

        Promise.all(promises).then(() => {
            window.location.href = `quiz_results.php?quiz_id=${encodeURIComponent(quizId)}&attempt_id=${encodeURIComponent(attemptId)}`;
        });
    }
</script>




</body>
</html>
