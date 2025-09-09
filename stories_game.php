<?php
session_start();
include("db_connect.php");

if(!isset($_SESSION['login_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['login_id'];

// Questions with English + Tagalog + Image/Icon
$questions = [
    [
        "question_en" => "✨ Special Word: Which one means 'Happy'?",
        "question_tl" => "✨ Espesyal na Salita: Alin ang ibig sabihin ng 'Masaya'?",
        "image" => "images/happy.png",
        "choices" => [
            ["en" => "Sad", "tl" => "Malungkot", "img" => "images/sad.png"],
            ["en" => "Joyful", "tl" => "Masaya", "img" => "images/happy.png"],
            ["en" => "Angry", "tl" => "Galit", "img" => "images/angry.png"],
            ["en" => "Tired", "tl" => "Pagod", "img" => "images/tired.png"]
        ],
        "answer" => "Joyful"
    ],
    [
        "question_en" => "📖 Story: Juan found a 🍌 banana in the garden. What did he find?",
        "question_tl" => "📖 Kuwento: Nakakita si Juan ng 🍌 saging sa hardin. Ano ang nakita niya?",
        "image" => "images/banana.png",
        "choices" => [
            ["en" => "Apple", "tl" => "Mansanas", "img" => "images/apple.png"],
            ["en" => "Banana", "tl" => "Saging", "img" => "images/banana.png"],
            ["en" => "Car", "tl" => "Sasakyan", "img" => "images/car.png"],
            ["en" => "Rose", "tl" => "Rosas", "img" => "images/rose.png"]
        ],
        "answer" => "Banana"
    ],
    [
        "question_en" => "✨ Special Word: Which one means 'Big'?",
        "question_tl" => "✨ Espesyal na Salita: Alin ang ibig sabihin ng 'Malaki'?",
        "image" => "images/big.png",
        "choices" => [
            ["en" => "Small", "tl" => "Maliit", "img" => "images/small.png"],
            ["en" => "Tiny", "tl" => "Napakaliit", "img" => "images/tiny.png"],
            ["en" => "Large", "tl" => "Malaki", "img" => "images/big.png"],
            ["en" => "Little", "tl" => "Kaunti", "img" => "images/little.png"]
        ],
        "answer" => "Large"
    ],
    [
        "question_en" => "📖 Story: Maria saw a 🐱 cat sleeping on the chair. What animal did Maria see?",
        "question_tl" => "📖 Kuwento: Nakakita si Maria ng 🐱 pusa na natutulog sa upuan. Anong hayop ang nakita niya?",
        "image" => "images/cat.png",
        "choices" => [
            ["en" => "Dog", "tl" => "Aso", "img" => "images/dog.png"],
            ["en" => "Cat", "tl" => "Pusa", "img" => "images/cat.png"],
            ["en" => "Bird", "tl" => "Ibon", "img" => "images/bird.png"],
            ["en" => "Fish", "tl" => "Isda", "img" => "images/fish.png"]
        ],
        "answer" => "Cat"
    ],
    [
        "question_en" => "✨ Special Word: Which word means 'Fast'?",
        "question_tl" => "✨ Espesyal na Salita: Aling salita ang ibig sabihin ng 'Mabilis'?",
        "image" => "images/fast.png",
        "choices" => [
            ["en" => "Quick", "tl" => "Mabilis", "img" => "images/fast.png"],
            ["en" => "Slow", "tl" => "Mabagal", "img" => "images/slow.png"],
            ["en" => "Stop", "tl" => "Hinto", "img" => "images/stop.png"],
            ["en" => "Late", "tl" => "Huli", "img" => "images/late.png"]
        ],
        "answer" => "Quick"
    ],
    [
        "question_en" => "📖 Story: Pedro rode a 🚲 bicycle to school. What did he ride?",
        "question_tl" => "📖 Kuwento: Sumakay si Pedro ng 🚲 bisikleta papunta sa paaralan. Ano ang kanyang sinakyan?",
        "image" => "images/bicycle.png",
        "choices" => [
            ["en" => "Car", "tl" => "Kotche", "img" => "images/car.png"],
            ["en" => "Bus", "tl" => "Bus", "img" => "images/bus.png"],
            ["en" => "Bicycle", "tl" => "Bisikleta", "img" => "images/bicycle.png"],
            ["en" => "Motorcycle", "tl" => "Motorsiklo", "img" => "images/motorcycle.png"]
        ],
        "answer" => "Bicycle"
    ]
];

// Handle submit
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
  <title>Special Words & Stories Game</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background: #fef9e7; }
    .game-card { border-radius: 15px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); background: #fff; margin-bottom: 20px; font-size: 1.2em; }
    .form-check img { width: 40px; height: 40px; margin-right: 10px; }
    .question-img { max-width: 120px; margin-bottom: 15px; display: block; }
    h2 { color: #e67e22; }
    .speak-btn { background: #3498db; color: white; border: none; border-radius: 8px; padding: 4px 8px; margin-left: 6px; font-size: 0.9em; cursor: pointer; }
    .speak-btn.tl { background: #e67e22; }
  </style>
</head>
<body class="container mt-4">
  <h2 class="text-center mb-4">🌟 Special Words & Stories Game 🌟</h2>

  <form method="post" id="quizForm">
    <?php foreach($questions as $index => $q): ?>
      <div class="game-card" id="question-<?php echo $index; ?>">
        <h5>
          <?php echo $q['question_en']; ?>
          <button type="button" class="speak-btn" onclick="speakText('<?php echo addslashes($q['question_en']); ?>', 'en-US')">🔊 EN</button>
          <button type="button" class="speak-btn tl" onclick="speakText('<?php echo addslashes($q['question_tl']); ?>', 'fil-PH')">🔊 TL</button>
        </h5>
        <?php if(!empty($q['image'])): ?>
          <img src="<?php echo $q['image']; ?>" alt="question image" class="question-img">
        <?php endif; ?>
        <div>
          <?php foreach($q['choices'] as $choice): ?>
            <div class="form-check d-flex align-items-center mb-2">
              <input class="form-check-input me-2" type="radio" name="answers[<?php echo $index; ?>]" value="<?php echo $choice['en']; ?>" required>
              <label class="form-check-label me-2">
                <img src="<?php echo $choice['img']; ?>" alt="choice image">
                <?php echo $choice['en']; ?> (<?php echo $choice['tl']; ?>)
              </label>
              <button type="button" class="speak-btn" onclick="speakText('<?php echo addslashes($choice['en']); ?>', 'en-US')">🔊 EN</button>
              <button type="button" class="speak-btn tl" onclick="speakText('<?php echo addslashes($choice['tl']); ?>', 'fil-PH')">🔊 TL</button>
            </div>
          <?php endforeach; ?>
          <input type="hidden" id="correct-<?php echo $index; ?>" value="<?php echo $q['answer']; ?>">
        </div>
      </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn-success w-100 p-3 fs-4">✅ Submit Answers</button>
  </form>

  <script>
    function speakText(text, lang) {
      const msg = new SpeechSynthesisUtterance(text);
      msg.lang = lang;
      msg.pitch = 1;
      msg.rate = 0.9;
      window.speechSynthesis.cancel();
      window.speechSynthesis.speak(msg);
    }
  </script>
</body>
</html>
