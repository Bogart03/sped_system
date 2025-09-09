<?php 
session_start();
if(!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type'] != 3){
    header("location: login.php");
    exit;
}
include('header.php');
include('nav_bar.php');
include('db_connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Color Match Game</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body { font-size: 1.3rem; background: #fff; transition: background-color 0.5s; }
.option-btn { width: 80px; height: 80px; margin:5px; border-radius:10px; border:none; cursor:pointer; transition: transform 0.2s; }
.option-btn:hover { transform: scale(1.1); box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
#scorePanel, #finalResult { margin-top:20px; }
#colorHint { width: 40px; height: 40px; display:inline-block; border-radius:5px; margin-left:10px; border:2px solid #333; vertical-align: middle; }
#feedback { font-weight:bold; font-size:1.3rem; margin-top:10px; min-height:24px; }
</style>
</head>
<body>

<div class="container py-5">
    <div class="card shadow text-center">
        <div class="card-body">
            <h3><i class="bi bi-palette-fill"></i> Color Match Game</h3>
            <p>Click the correct color before time runs out!</p>

            <div id="gameArea">
                <h4 id="question">Select the color: <span id="colorHint"></span></h4>
                <div id="options" class="d-flex justify-content-center flex-wrap"></div>
                <div id="feedback"></div>
            </div>

            <div id="scorePanel">
                <p><strong><i class="bi bi-check-circle-fill text-success"></i> Score:</strong> <span id="score">0</span></p>
                <p><strong><i class="bi bi-bar-chart-fill"></i> Attempts:</strong> <span id="attempts">0</span></p>
                <div class="progress">
                    <div id="timerBar" class="progress-bar bg-success" role="progressbar" style="width:100%"></div>
                </div>
                <p><strong><i class="bi bi-clock-fill"></i> Time Left:</strong> <span id="timer">5</span>s</p>
            </div>

            <div id="finalResult" class="text-primary fs-4"></div>

            <button class="btn btn-primary mt-3" id="restartBtn"><i class="bi bi-arrow-repeat"></i> Restart</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
let allColors = ["red","blue","green","yellow","purple","orange","pink","brown","cyan","magenta"];
let activeColors = allColors.slice(0,4); // start with 4 colors
let correctColor, score=0, attempts=0, timeLeft=5, timer, gameOver=false;
let baseTime = 5; // starting seconds per question

const questionEl = document.getElementById('question');
const colorHintEl = document.getElementById('colorHint');
const optionsEl = document.getElementById('options');
const scoreEl = document.getElementById('score');
const attemptsEl = document.getElementById('attempts');
const feedbackEl = document.getElementById('feedback');
const timerEl = document.getElementById('timer');
const timerBar = document.getElementById('timerBar');
const restartBtn = document.getElementById('restartBtn');
const finalResultEl = document.getElementById('finalResult');

function newQuestion(){
    // increase difficulty
    if(score >= 3 && activeColors.length < 6) activeColors = allColors.slice(0,6);
    if(score >= 6 && activeColors.length < 8) activeColors = allColors.slice(0,8);
    if(score >= 10) activeColors = allColors;

    correctColor = activeColors[Math.floor(Math.random()*activeColors.length)];
    colorHintEl.style.backgroundColor = correctColor;
    feedbackEl.textContent='';
    optionsEl.innerHTML = '';

    let shuffled = [...activeColors].sort(()=>Math.random()-0.5);
    shuffled.forEach(c=>{
        let btn = document.createElement('button');
        btn.className='option-btn';
        btn.style.backgroundColor=c;
        btn.onclick = ()=>checkAnswer(c);
        optionsEl.appendChild(btn);
    });

    // adjust time for difficulty: decrease by 0.5 sec every 3 correct, minimum 2 sec
    timeLeft = Math.max(baseTime - 0.5 * Math.floor(score/3), 2);
    timerEl.textContent = timeLeft;
    timerBar.style.width = "100%";
}

function startTimer(){
    clearInterval(timer);
    timer = setInterval(()=>{
        timeLeft -= 0.1;
        if(timeLeft<0) timeLeft=0;
        timerEl.textContent = Math.ceil(timeLeft);
        timerBar.style.width = (timeLeft/(Math.max(baseTime,2)) * 100)+'%';
        if(timeLeft<=0){ clearInterval(timer); endGame(); }
    },100);
}

function checkAnswer(selected){
    if(gameOver) return;
    attempts++;
    attemptsEl.textContent = attempts;
    if(selected === correctColor){
        score++;
        scoreEl.textContent = score;
        feedbackEl.textContent="✅ Correct!";
        feedbackEl.style.color="green";
        document.body.style.backgroundColor = "#d4edda";
        confetti({ particleCount: 50, spread: 70, origin: { y: 0.6 } });
    } else {
        feedbackEl.textContent="❌ Wrong!";
        feedbackEl.style.color="red";
        document.body.style.backgroundColor = "#f8d7da";
    }
    setTimeout(()=>{
        document.body.style.backgroundColor = "white";
        if(!gameOver) newQuestion();
    },600);
}

function endGame(){
    gameOver=true;
    clearInterval(timer);
    document.getElementById('gameArea').style.display='none';
    document.getElementById('scorePanel').style.display='none';
    saveResult(score, attempts);
    finalResultEl.innerHTML=`🎯 Time's up! Score: ${score}/${attempts}`;
}

function saveResult(score, attempts){
    const xhr = new XMLHttpRequest();
    xhr.open("POST","save_color_result.php",true);
    xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
    xhr.send("score="+encodeURIComponent(score)+"&attempts="+encodeURIComponent(attempts));
}

restartBtn.onclick=()=>location.reload();

window.onload = ()=>{
    newQuestion();
    startTimer();
}
</script>
</body>
</html>
