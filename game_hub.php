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
  <?php include('header.php'); ?>
  <title>SPED Game Hub</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background: #f0f8ff; } /* light, calming background */
    .game-card {
      cursor: pointer;
      transition: transform .3s, box-shadow .3s;
      border-radius: 8px;
      padding: 15px 0;
      font-family: 'Comic Sans MS', cursive, sans-serif;
      color: #fff;
    }
    .game-card:hover {
      transform: scale(1.08);
      box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    }
    .game-icon {
      font-size: 4rem;
      margin-bottom: 10px;
    }
    .memory-game { background-color: #4dabf7; } /* blue */
    .math-quiz { background-color: #51cf66; }    /* green */
    .word-match { background-color: #19fca5ff; color: #000; } /* yellow with black text */
    h5 { font-size: 1.5rem; font-weight: bold; }
  </style>
</head>
<body>

<?php include('nav_bar.php'); ?>

<div class="container py-5">
  <div class="text-center mb-5">
    <h2 class="fw-bold"><i class="fas fa-gamepad"></i> Welcome to the Game Hub</h2>
    <p class="lead text-muted">Click a game to start playing!</p>
  </div>

  <div class="row g-4 justify-content-center">

    <!-- Memory Game -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card game-card memory-game text-center" onclick="window.location='memory_game.php'">
        <div class="card-body">
          <i class="fas fa-brain game-icon"></i>
          <h5>Memory Game</h5>
        </div>
      </div>
    </div>

    <!-- Math Quiz -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card game-card math-quiz text-center" onclick="window.location='math_game.php'">
        <div class="card-body">
          <i class="fas fa-calculator game-icon"></i>
          <h5>Math Quiz</h5>
        </div>
      </div>
    </div>

    <!-- Color Game -->
<div class="col-12 col-md-6 col-lg-4">
  <div class="card game-card word-match text-center" onclick="window.location='color_game.php'">
    <div class="card-body">
      <i class="fas fa-palette game-icon"></i>
      <h5>Color Game</h5>
    </div>
  </div>
</div>


  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
