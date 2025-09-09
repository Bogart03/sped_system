<?php 
session_start();
if(!isset($_SESSION['login_user_type']) || $_SESSION['login_user_type'] != 3){
    header("location: login.php");
    exit;
}
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('header.php') ?>
    <title>Memory Game</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ✅ Bootstrap & Icons -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <style>
        body { background: #f0f8ff; font-family: Arial, sans-serif; }
       #game-board {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); /* auto-fit grid */
    gap: 15px;
    justify-content: center;
    margin: 20px auto;
    width: 100%;
    max-width: 600px; /* keeps it centered */
}

.game-card {
    aspect-ratio: 1 / 1; /* always square */
    background: #007bff;
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    font-size: clamp(24px, 5vw, 40px); /* responsive font */
    cursor: pointer;
    border-radius: 15px;
    transition: 0.3s ease;
    box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    text-align: center;
    line-height: 1.2;
}

.game-card span {
    display: block;
    font-size: clamp(14px, 3vw, 20px); /* responsive text */
    margin-top: 5px;
}

        .game-card.flipped {
            background: #fff;
            color: #000;
            border: 3px solid #007bff;
        }
        .game-card.flipped.correct {
            background: #28a745 !important;
            color: #fff !important;
            animation: pulse 0.5s;
        }
        .game-card.flipped.wrong {
            background: #dc3545 !important;
            color: #fff !important;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .stats {
            display: flex;
            justify-content: space-around;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .progress { height: 18px; border-radius: 8px; margin-bottom: 15px; }
        .progress-bar { transition: width 1s linear; }

        #messageModal .modal-content {
            background: #fffbea;
            border-radius: 20px;
            text-align: center;
            padding: 30px;
        }
        #messageModal h2 { font-size: 28px; margin-bottom: 10px; }
        #messageModal .emoji { font-size: 70px; margin-bottom: 15px; }
    </style>
</head>
<body>

<?php include('nav_bar.php'); ?>

<div class="container-fluid admin">
    <div class="col-md-12 alert alert-primary text-center">
        <h3 class="mb-0"><i class="fas fa-brain"></i> Memory Game for SPED Students</h3>
    </div>

    <div class="card">
        <div class="card-body text-center">
            
            <!-- ✅ Difficulty Selection -->
            <div class="mb-3">
                <label><strong>🎮 Choose Difficulty:</strong></label>
                <select id="difficulty" class="form-control w-auto d-inline-block">
                    <option value="easy">Easy (6 pairs, 3 min)</option>
                    <option value="medium" selected>Medium (8 pairs, 2 min)</option>
                    <option value="hard">Hard (10 pairs, 1.5 min)</option>
                </select>
                <button id="startGame" class="btn btn-success ml-2">Start Game</button>
            </div>

            <!-- ✅ Game Stats -->
            <div class="stats">
                <div><i class="fas fa-sync-alt"></i> Moves: <span id="move-count">0</span></div>
                <div><i class="fas fa-clock"></i> Time Left: <span id="timer">00:00</span></div>
            </div>

            <!-- ✅ Progress Bar -->
            <div class="progress">
                <div id="progress-bar" class="progress-bar bg-success" style="width:100%"></div>
            </div>

            <p class="mb-4 lead">🎯 Match all the pairs before time runs out!</p>

            <div id="game-board"></div>

            <div class="mt-4">
                <button id="restart" class="btn btn-lg btn-primary" disabled>
                    <i class="fas fa-sync-alt"></i> Restart Game
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ✅ Friendly Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <span class="emoji" id="msgEmoji">🎉</span>
      <h2 id="msgTitle">You Won!</h2>
      <p id="msgText">Great job completing the game!</p>
      <button class="btn btn-success" data-dismiss="modal">OK</button>
    </div>
  </div>
</div>

<script>
    const board = document.getElementById('game-board');
    const restartBtn = document.getElementById('restart');
    const startBtn = document.getElementById('startGame');
    const moveCountEl = document.getElementById('move-count');
    const timerEl = document.getElementById('timer');
    const progressBar = document.getElementById('progress-bar');
    const difficultySelect = document.getElementById('difficulty');

    // ✅ All Symbols
    const allSymbols = [
        {emoji:'🍎', label:'Apple'}, {emoji:'🍌', label:'Banana'},
        {emoji:'🍇', label:'Grapes'}, {emoji:'🐶', label:'Dog'},
        {emoji:'🐱', label:'Cat'}, {emoji:'🐸', label:'Frog'},
        {emoji:'🍉', label:'Watermelon'}, {emoji:'🍓', label:'Strawberry'},
        {emoji:'🦁', label:'Lion'}, {emoji:'🐢', label:'Turtle'}
    ];

    let cards = [];
    let flippedCards = [];
    let matchedPairs = 0;
    let moves = 0;
    let timerInterval;
    let timeLeft = 0;
    let totalTime = 0;
    let timerStarted = false;

    // ✅ Sounds
    const soundFlip = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-fast-small-sweep-transition-166.mp3");
    const soundCorrect = new Audio("https://www.myinstants.com/media/sounds/correct.mp3");
    const soundWrong = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-wrong-answer-fail-notification-946.mp3");
    const soundWin = new Audio("https://www.myinstants.com/media/sounds/applause.mp3");

    function shuffle(array) {
        array.sort(() => Math.random() - 0.5);
    }

    function setupGame(level){
        let selected = [];
        if(level === "easy"){
            selected = allSymbols.slice(0,6);
            totalTime = 180; // 3 minutes
        } else if(level === "medium"){
            selected = allSymbols.slice(0,8);
            totalTime = 120; // 2 minutes
        } else {
            selected = allSymbols.slice(0,10);
            totalTime = 90; // 1.5 minutes
        }
        cards = [...selected, ...selected];
        resetTimer();
        createBoard();
        restartBtn.disabled = false;
    }

    function createBoard() {
        shuffle(cards);
        board.innerHTML = '';
        cards.forEach(obj => {
            const card = document.createElement('div');
            card.classList.add('game-card');
            card.dataset.symbol = obj.emoji;
            card.dataset.label = obj.label;
            card.tabIndex = 0;
            card.addEventListener('click', flipCard);
            card.addEventListener('keydown', (e) => {
                if(e.key === "Enter") card.click();
            });
            board.appendChild(card);
        });
    }

    function startTimer() {
        if (!timerStarted) {
            timerStarted = true;
            timerInterval = setInterval(() => {
                timeLeft--;
                timerEl.textContent = formatTime(timeLeft);
                updateProgressBar();

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    showMessage("⏳", "Time’s Up!", "Don’t worry, try again!");
                    disableBoard();
                }
            }, 1000);
        }
    }

    function resetTimer() {
        clearInterval(timerInterval);
        timeLeft = totalTime;
        timerEl.textContent = formatTime(timeLeft);
        progressBar.style.width = "100%";
        progressBar.className = "progress-bar bg-success";
        timerStarted = false;
    }

    function updateProgressBar() {
        let percentage = (timeLeft / totalTime) * 100;
        progressBar.style.width = percentage + "%";

        if (percentage <= 30) {
            progressBar.className = "progress-bar bg-danger";
        } else if (percentage <= 60) {
            progressBar.className = "progress-bar bg-warning";
        } else {
            progressBar.className = "progress-bar bg-success";
        }
    }

    function formatTime(sec) {
        let m = Math.floor(sec / 60);
        let s = sec % 60;
        return (m < 10 ? "0" + m : m) + ":" + (s < 10 ? "0" + s : s);
    }

    function disableBoard() {
        const allCards = document.querySelectorAll('.game-card');
        allCards.forEach(card => card.removeEventListener('click', flipCard));
    }

    function flipCard() {
        if(this.classList.contains('flipped') || flippedCards.length === 2) return;
        
        soundFlip.play();
        startTimer();
        
        this.innerHTML = this.dataset.symbol + "<span>" + this.dataset.label + "</span>";
        this.classList.add('flipped');
        flippedCards.push(this);

        if(flippedCards.length === 2){
            moves++;
            moveCountEl.textContent = moves;
            if(flippedCards[0].dataset.symbol === flippedCards[1].dataset.symbol){
                flippedCards.forEach(c => c.classList.add("correct"));
                flippedCards = [];
                matchedPairs++;
                soundCorrect.play();
                if(matchedPairs === cards.length/2){
                    clearInterval(timerInterval);
                    soundWin.play();
                    showMessage("🎉","Great Job!","You matched all pairs in " + moves + " moves!");
                    saveResult(moves);
                }
            } else {
                flippedCards.forEach(c => c.classList.add("wrong"));
                soundWrong.play();
                setTimeout(() => {
                    flippedCards.forEach(card => {
                        card.innerHTML = '';
                        card.classList.remove('flipped','wrong');
                    });
                    flippedCards = [];
                }, 1200);
            }
        }
    }

    function showMessage(emoji, title, text){
        document.getElementById("msgEmoji").textContent = emoji;
        document.getElementById("msgTitle").textContent = title;
        document.getElementById("msgText").textContent = text;
        $('#messageModal').modal('show');
    }

    function saveResult(moves){
        const timeTaken = totalTime - timeLeft;
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "save_memory_result.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("moves=" + moves + "&time_taken=" + timeTaken + "&difficulty=" + difficultySelect.value);
    }

    restartBtn.addEventListener('click', () => {
        flippedCards = [];
        matchedPairs = 0;
        moves = 0;
        moveCountEl.textContent = moves;
        setupGame(difficultySelect.value);
    });

    startBtn.addEventListener('click', () => {
        flippedCards = [];
        matchedPairs = 0;
        moves = 0;
        moveCountEl.textContent = moves;
        setupGame(difficultySelect.value);
    });
</script>

</body>
</html>
