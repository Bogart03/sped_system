<?php 
session_start();
if(!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type'] != 3){
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php include('header.php') ?>
  <meta charset="UTF-8" />
  <title>🧮 Math Quiz</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    body { font-size: 1.3rem; background: #fff; transition: background-color 0.5s ease; }
    .option-btn { font-size: 1.5rem; padding: 15px 25px; min-width: 100px; }
    #question { font-size: 2rem; margin: 20px 0; }
    #feedback { font-size: 1.5rem; margin-top: 15px; font-weight: bold; }
    #timerBar { height: 20px; }
    #finalResult { font-size: 1.8rem; margin-top: 20px; font-weight: bold; }
  </style>
</head>
<body>
<?php include('nav_bar.php'); ?>

<div class="container py-5">
  <div class="card shadow text-center">
    <div class="card-body">
      <h3>🧮 Math Quiz</h3>
      <p>Choose the correct answer before time runs out!</p>
      
      <div id="quizArea">
        <h4 id="question"></h4>
        <div id="options" class="d-flex flex-wrap justify-content-center gap-3 mt-4"></div>
        <div id="feedback"></div>
      </div>

      <div id="scorePanel" class="mt-4">
        <p><strong>✅ Score:</strong> <span id="score">0</span></p>
        <p><strong>📊 Attempts:</strong> <span id="attempts">0</span></p>
        <div class="progress mt-2">
          <div id="timerBar" class="progress-bar bg-success" role="progressbar" style="width:100%"></div>
        </div>
        <p class="mt-2"><strong>⏱ Time Left:</strong> <span id="timer">60</span>s</p>
      </div>

      <div id="finalResult" class="text-primary"></div>

      <button class="btn btn-primary mt-3" id="restartBtn">🔄 Restart</button>
    </div>
  </div>
</div>

<!-- Confetti -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
let num1, num2, correctAnswer;
let score = 0, attempts = 0;
let timeLeft = 60, timer, gameOver = false;

const questionEl   = document.getElementById('question');
const optionsEl    = document.getElementById('options');
const scoreEl      = document.getElementById('score');
const attemptsEl   = document.getElementById('attempts');
const feedbackEl   = document.getElementById('feedback');
const timerEl      = document.getElementById('timer');
const timerBar     = document.getElementById('timerBar');
const restartBtn   = document.getElementById('restartBtn');
const finalResultEl= document.getElementById('finalResult');

// ===== Cooldown helpers =====
function setCooldown(minutes){
  const expireTime = Date.now() + minutes * 60 * 1000;
  localStorage.setItem("quizCooldown", expireTime);
}

function getCooldownRemaining(){
  const expireTime = localStorage.getItem("quizCooldown");
  if(!expireTime) return 0;
  const diff = Math.floor((expireTime - Date.now()) / 1000);
  return diff > 0 ? diff : 0;
}

function formatTime(seconds){
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}:${s < 10 ? '0'+s : s}`;
}

// ===== Quiz functions =====
function newQuestion(){
  num1 = Math.floor(Math.random() * 10) + 1;
  num2 = Math.floor(Math.random() * 10) + 1;
  correctAnswer = num1 + num2;

  questionEl.textContent = `${num1} + ${num2} = ?`;
  feedbackEl.textContent = '';

  // Accessibility (read aloud)
  let utterance = new SpeechSynthesisUtterance(`${num1} plus ${num2}, what is the answer?`);
  speechSynthesis.speak(utterance);

  // Multiple-choice options
  optionsEl.innerHTML = '';
  let answers = [correctAnswer];
  while(answers.length < 4){
    let wrong = Math.floor(Math.random() * 20) + 1;
    if(!answers.includes(wrong)) answers.push(wrong);
  }
  answers.sort(() => Math.random() - 0.5);

  answers.forEach(ans => {
    let btn = document.createElement('button');
    btn.className = "btn btn-outline-primary option-btn";
    btn.textContent = ans;
    btn.onclick = () => checkAnswer(ans);
    optionsEl.appendChild(btn);
  });
}

function startTimer(){
  timer = setInterval(() => {
    timeLeft--;
    timerEl.textContent = timeLeft;
    timerBar.style.width = (timeLeft/60 * 100) + "%";
    if(timeLeft <= 0){
      clearInterval(timer);
      endGame();
    }
  }, 1000);
}

function checkAnswer(selected){
  if(gameOver) return;
  attempts++;
  attemptsEl.textContent = attempts;

  if(selected === correctAnswer){
    score++;
    scoreEl.textContent = score;
    feedbackEl.textContent = "🌟 Great job!";
    feedbackEl.style.color = "green";
    document.body.style.backgroundColor = "#d4edda";
    confetti({ particleCount: 60, spread: 60, origin: { y: 0.7 } });
  } else {
    feedbackEl.textContent = "❌ Oops, keep trying!";
    feedbackEl.style.color = "red";
    document.body.style.backgroundColor = "#f8d7da";
  }

  setTimeout(() => { 
    document.body.style.backgroundColor = "white"; 
    if(!gameOver) newQuestion(); 
  }, 700);
}

function endGame(){
  gameOver = true;

  // Hide quiz area & score panel
  document.getElementById("quizArea").style.display = "none";
  document.getElementById("scorePanel").style.display = "none";

  // Set cooldown (1 min)
  setCooldown(1);
  let waitTime = getCooldownRemaining();

  // Hide restart initially
  restartBtn.style.display = "none";

  const percent = attempts > 0 ? Math.round((score/attempts) * 100) : 0;
  let encouragement = "";
  if(percent >= 80){
    encouragement = ["🎉 Excellent work!", "🌟 You're a math star!", "👏 Fantastic job!"][Math.floor(Math.random()*3)];
    confetti({ particleCount: 200, spread: 120, origin: { y: 0.6 } });
  } else if(percent >= 50){
    encouragement = ["👍 Good effort! Keep practicing!", "💡 You're improving!", "🌈 Great try, don’t give up!"][Math.floor(Math.random()*3)];
  } else {
    encouragement = ["💪 Don’t worry, you’ll get better!", "🌱 Every mistake helps you grow!", "🚀 Keep going, you’re learning!"][Math.floor(Math.random()*3)];
  }

  finalResultEl.innerHTML = `
    ⏳ Time’s up!<br>
    Final Score: ${score} out of ${attempts} (${percent}%)<br>
    <span class="text-success">${encouragement}</span><br>
    <em id="cooldownMsg">Restart will be available in ${formatTime(waitTime)}.</em>
  `;
  finalResultEl.classList.add("animate__animated", "animate__pulse");

  // Countdown updater
  const cooldownMsg = document.getElementById("cooldownMsg");
  const countdown = setInterval(() => {
    waitTime = getCooldownRemaining();
    if(waitTime > 0){
      cooldownMsg.textContent = `Restart will be available in ${formatTime(waitTime)}.`;
    } else {
      clearInterval(countdown);
      cooldownMsg.textContent = "✅ Restart is now available!";
      restartBtn.style.display = "inline-block";
      restartBtn.disabled = false;
      restartBtn.textContent = "🔄 Restart";
      localStorage.removeItem("quizCooldown");
    }
  }, 1000);

  saveResult(score, attempts);
}

function saveResult(score, totalQuestions){
  const xhr = new XMLHttpRequest();
  xhr.open("POST", "save_math_result.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.send("score=" + encodeURIComponent(score) + "&total_questions=" + encodeURIComponent(totalQuestions));
}

restartBtn.addEventListener('click', () => location.reload());

// ===== Restore cooldown if refresh =====
window.addEventListener("load", () => {
  let remaining = getCooldownRemaining();
  if(remaining > 0){
    document.getElementById("quizArea").style.display = "none";
    document.getElementById("scorePanel").style.display = "none";
    restartBtn.style.display = "none";
    finalResultEl.innerHTML = `<em id="cooldownMsg">Restart will be available in ${formatTime(remaining)}.</em>`;
    const cooldownMsg = document.getElementById("cooldownMsg");

    const countdown = setInterval(() => {
      remaining = getCooldownRemaining();
      if(remaining > 0){
        cooldownMsg.textContent = `Restart will be available in ${formatTime(remaining)}.`;
      } else {
        clearInterval(countdown);
        cooldownMsg.textContent = "✅ Restart is now available!";
        restartBtn.style.display = "inline-block";
        restartBtn.disabled = false;
        restartBtn.textContent = "🔄 Restart";
        localStorage.removeItem("quizCooldown");
      }
    }, 1000);
  } else {
    // Start new quiz if no cooldown
    newQuestion();
    startTimer();
  }
});
</script>
</body>
</html>
