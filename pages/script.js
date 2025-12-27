let questions = [];
let currentQuestionIndex = 0;
let userAnswers = {};
const totalQuizTime = 15 * 60; // 15 minutes
const userId = window.opener.userId; // Retrieve user_id from the parent window


document.addEventListener('DOMContentLoaded', function () {
    fetchQuestions();
    startTimer(totalQuizTime);
});

function fetchQuestions() {
    fetch('fetch_questions.php')
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
        <h2>${question.question_text}</h2>
        <form>
            <label><input type="radio" name="option" value="A"> ${question.option_a}</label><br>
            <label><input type="radio" name="option" value="B"> ${question.option_b}</label><br>
            <label><input type="radio" name="option" value="C"> ${question.option_c}</label><br>
            <label><input type="radio" name="option" value="D"> ${question.option_d}</label>
        </form>
    `;

    const options = document.getElementsByName('option');
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
}

function displayQuestionNav() {
    const questionNav = document.getElementById('questionNav');
    questionNav.innerHTML = '';

    questions.forEach((question, index) => {
        const button = document.createElement('button');
        button.textContent = index + 1;
        button.classList.add(getQuestionStatus(index));
        button.addEventListener('click', function () {
            currentQuestionIndex = index;
            displayQuestion(index);
        });
        questionNav.appendChild(button);
    });

    document.getElementById('prevBtn').addEventListener('click', function () {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            displayQuestion(currentQuestionIndex);
        }
    });

    document.getElementById('nextBtn').addEventListener('click', function () {
        if (currentQuestionIndex < questions.length - 1) {
            currentQuestionIndex++;
            displayQuestion(currentQuestionIndex);
        } else {
            submitQuiz();
        }
    });
}

function getQuestionStatus(index) {
    const questionId = questions[index].id;
    if (userAnswers[questionId]) {
        return 'attended';
    } else if (currentQuestionIndex === index) {
        return 'watched';
    } else {
        return 'unattended';
    }
}

function updateQuestionNav() {
    const buttons = document.getElementById('questionNav').children;
    for (let i = 0; i < buttons.length; i++) {
        buttons[i].classList.remove('attended', 'watched', 'unattended');
        buttons[i].classList.add(getQuestionStatus(i));
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

function submitQuiz() {
    for (const questionId in userAnswers) {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('question_id', questionId);
        formData.append('selected_option', userAnswers[questionId]);

        fetch('record_answer.php', {
            method: 'POST',
            body: formData
        }).catch(error => console.error('Error recording answer:', error));
    }
    alert('Quiz submitted!');
    window.close(); // Close the quiz window after submission
}
