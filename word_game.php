<?php
session_start();
include("db_connect.php");

// Redirect if not logged in
if(!isset($_SESSION['login_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['login_id'];

// Questions with images + multiple choices
$questions = [
    [
        "question" => "Which one is an 🍎 Apple?",
        "image" => "images/apple.png",
        "choices" => ["Fruit", "Animal", "Car", "Flower"],
        "answer" => "Fruit"
    ],
    [
        "question" => "Which one is a 🐶 Dog?",
        "image" => "images/dog.png",
        "choices" => ["Fruit", "Animal", "Car", "Star"],
        "answer" => "Animal"
    ],
    [
        "question" => "🚗 Car is a type of?",
        "image" => "images/car.png",
        "choices" => ["Vehicle", "Flower", "Star", "Animal"],
        "answer" => "Vehicle"
    ],
    [
        "question" => "🌹 Rose is a type of?",
        "image" => "images/rose.png",
        "choices" => ["Fruit", "Flower", "Vehicle", "Star"],
        "answer" => "Flower"
    ],
    [
        "question" => "☀️ Sun is a type of?",
        "image" => "images/sun.png",
        "choices" => ["Flower", "Fruit", "Star", "Car"],
        "answer" => "Star"
    ]
];

// Handle form submit (server-side save)
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $answers = $_POST['answers'] ?? [];
    $correct = 0;

    foreach($questions as $index => $q){
        $userAnswer = $answers[$index] ?? '';
        if($userAnswer === $q['answer']){
            $correct++;
        }
    }

    $total = count($questions);

    // Save result
    $stmt = $conn->prepare("INSERT INTO word_game_results (user_id, correct, total_questions) VALUES (?,?,?)");
    $stmt->bind_param("iii", $user_id, $correct, $total);
    $stmt->execute();

    $_SESSION['message'] = "🎉 You scored $correct / $total!";
    header("Location: word_results.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Fun Learning Game</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background: #fdf6e3; }
    .game-card { 
        border-radius: 15px; 
        padding: 20px; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.2); 
        background: #fff; 
        margin-bottom: 20px;
    }
    img { max-width: 150px; margin: 10px; }
    .correct { background-color: #d4edda; border-radius: 8px; padding: 5px; }
    .wrong { background-color: #f8d7da; border-radius: 8px; padding: 5px; }
  </style>
</head>
<body class="container mt-4">
  <h2 class="text-center mb-4">🎮 Fun Learning Game</h2>

  <!-- Sound Effects -->
  <audio id="ding" src="sounds/correct.mp3"></audio>
  <audio id="buzz" src="sounds/wrong.mp3"></audio>
  <audio id="submitSound" src="sounds/submit.mp3"></audio>

  <form method="post" id="quizForm">
    <?php foreach($questions as $index => $q): ?>
      <div class="game-card" id="question-<?php echo $index; ?>">
        <h5><?php echo $q['question']; ?></h5>
        <?php if(!empty($q['image'])): ?>
            <img src="<?php echo $q['image']; ?>" alt="question image">
        <?php endif; ?>
        <div>
          <?php foreach($q['choices'] as $choice): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio" 
                     name="answers[<?php echo $index; ?>]" 
                     value="<?php echo $choice; ?>" 
                     required>
              <label class="form-check-label">
                <?php echo $choice; ?>
              </label>
            </div>
          <?php endforeach; ?>
          <!-- Store correct answer in hidden input (for JS check) -->
          <input type="hidden" id="correct-<?php echo $index; ?>" value="<?php echo $q['answer']; ?>">
        </div>
      </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn-success w-100 p-3 fs-4" onclick="playSubmitEffect(event)">✅ Submit Answers</button>
  </form>

  <!-- Confetti JS -->
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
  <script>
    const ding = document.getElementById("ding");
    const buzz = document.getElementById("buzz");
    const submitSound = document.getElementById("submitSound");

    function playSubmitEffect(event) {
      event.preventDefault(); // stop instant submit
      submitSound.play();

      // Confetti burst 🎉
      confetti({
        particleCount: 80,
        spread: 70,
        origin: { y: 0.6 }
      });

      // Highlight correct/wrong answers
      let questions = document.querySelectorAll("[id^='question-']");
      questions.forEach((q, index) => {
        let correctAnswer = document.getElementById("correct-" + index).value;
        let selected = q.querySelector("input[type='radio']:checked");
        if (selected) {
          let label = selected.parentElement;
          if (selected.value === correctAnswer) {
            label.classList.add("correct");
            ding.play();
          } else {
            label.classList.add("wrong");
            buzz.play();
            // also highlight the correct answer
            let correctOption = q.querySelector("input[value='" + correctAnswer + "']");
            if (correctOption) {
              correctOption.parentElement.classList.add("correct");
            }
          }
        }
      });

      // Delay submit so effects show
      setTimeout(() => {
        document.getElementById("quizForm").submit();
      }, 1800);
    }
  </script>
</body>
</html>
