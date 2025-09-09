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
  <title>My Game Results</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background: #f8f9fa; }
    .result-card {
      cursor: pointer;
      transition: transform .2s;
    }
    .result-card:hover {
      transform: scale(1.05);
    }
  </style>
</head>
<body>
<?php include('nav_bar.php'); ?>

<div class="container py-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold"><i class="fas fa-chart-line"></i> My Game Results</h2>
    <p class="text-muted">Check your performance across different games</p>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#resultsModal">
      📊 View Results
    </button>
  </div>

  <!-- Results Modal -->
  <div class="modal fade" id="resultsModal" tabindex="-1" aria-labelledby="resultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 shadow">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fas fa-poll"></i> Select a Game Result</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">

            <!-- Memory Game -->
            <div class="col-md-6">
              <div class="card result-card" onclick="window.location='memory_results.php'">
                <div class="card-body text-center">
                  <i class="fas fa-brain fa-2x text-primary"></i>
                  <h6 class="mt-2">Memory Game</h6>
                </div>
              </div>
            </div>

            <!-- Math Quiz -->
            <div class="col-md-6">
              <div class="card result-card" onclick="window.location='math_results.php'">
                <div class="card-body text-center">
                  <i class="fas fa-calculator fa-2x text-success"></i>
                  <h6 class="mt-2">Math Quiz</h6>
                </div>
              </div>
            </div>

            <!-- Word Match -->
            <div class="col-md-6">
              <div class="card result-card" onclick="window.location='word_results.php'">
                <div class="card-body text-center">
                  <i class="fas fa-font fa-2x text-warning"></i>
                  <h6 class="mt-2">Word Match</h6>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
