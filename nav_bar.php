<nav class="navbar navbar-header navbar-light bg-primary">
    <div class="container-fluid">
        <div class="navbar-header">
            <p class="navbar-text pull-right text-white"><h3>SPED System</h3></p>
        </div>
        <div class="nav navbar-nav navbar-right">
            <a href="logout.php" class="text-dark">
                <?php echo isset($_SESSION['login_name']) ? $_SESSION['login_name'] : 'Logout'; ?>
                <i class="fa fa-power-off"></i>
            </a>
        </div>
    </div>
</nav>

<div id="sidebar" class="bg-light">

    <!-- Common Link -->
    <div id="sidebar-field">
        <a href="home.php" class="sidebar-item text-dark">
            <div class="sidebar-icon"><i class="fa fa-home"></i></div> Home
        </a>
    </div>

    <!-- ================= ADMIN / FACULTY SIDEBAR ================= -->
    <?php if(in_array($_SESSION['login_user_type'], [1, 2])): ?>
        <div id="sidebar-field">
            <a href="faculty.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-users"></i></div> Faculty List
            </a>
        </div>

        <div id="sidebar-field">
            <a href="student.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-users"></i></div> Student List
            </a>
        </div>

        <div id="sidebar-field">
            <a href="quiz.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-list"></i></div> Quiz List
            </a>
        </div>

        <div id="sidebar-field">
            <a href="history.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-history"></i></div> Quiz Records
            </a>
        </div>

        <!-- Game Results -->
        <div id="sidebar-field">
            <a href="memory_results.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-brain"></i></div> Memory Results
            </a>
        </div>

        <div id="sidebar-field">
            <a href="math_results.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-calculator"></i></div> Math Results
            </a>
        </div>

        <!-- <div id="sidebar-field">
            <a href="word_results.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-language"></i></div> Word Game Results
            </a>
        </div> -->

        <!-- New Color Game Results -->
        <div id="sidebar-field">
            <a href="color_game_results.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-palette"></i></div> Color Game Results
            </a>
        </div>
    <?php endif; ?>

    <!-- ================= STUDENT SIDEBAR ================= -->
    <?php if($_SESSION['login_user_type'] == 3): ?>
        <div id="sidebar-field">
            <a href="student_quiz_list.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-list"></i></div> Quiz List
            </a>
        </div>

        <!-- <div id="sidebar-field">
            <a href="color_game.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-palette"></i></div> Color Game
            </a>
        </div> -->

        <div id="sidebar-field">
            <a href="game_hub.php" class="sidebar-item text-dark">
                <div class="sidebar-icon"><i class="fa fa-gamepad"></i></div> Game Hub
            </a>
        </div>
    <?php endif; ?>

</div>

<script>
    $(document).ready(function(){
        var loc = window.location.href;
        $('#sidebar a').each(function(){
            if($(this).attr('href') == loc.substr(loc.lastIndexOf("/") + 1)){
                $(this).addClass('active');
            }
        });
    });
</script>
