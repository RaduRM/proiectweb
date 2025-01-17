<?php
session_start();
require 'includes/db_connect.php';

// se verifica autentificarea
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// intrebari folosite (? not really working tho)
if (!isset($_SESSION['used_questions'])) {
    $_SESSION['used_questions'] = [];
}

// resetarea scorului si reactivarea butonului de powerup
if (!isset($_SESSION['game_started'])) {
    $stmt_reset_score = $conn->prepare("UPDATE users SET score = 0 WHERE id = ?");
    $stmt_reset_score->bind_param("i", $_SESSION['user_id']);
    $stmt_reset_score->execute();
    $_SESSION['game_started'] = true;
    $_SESSION['powerup_50_50'] = 1;
    $_SESSION['powerup_used'] = false;
    $_SESSION['time_left'] = 30;
    $_SESSION['used_questions'] = [];
}

// preluam datele utilizatorului
$stmt = $conn->prepare("SELECT username, score, highest_score FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$username = $user_data['username'];
$score = $user_data['score'];
$highest_score = $user_data['highest_score'];


if (!isset($_SESSION['current_question']) || isset($_POST['answer']) || isset($_POST['reset_game'])) {
    $query = "SELECT * FROM questions WHERE id NOT IN (" . implode(',', (empty($_SESSION['used_questions']) ? [0] : $_SESSION['used_questions'])) . ") ORDER BY RAND() LIMIT 1";
    $result = $conn->query($query);

    if ($result->num_rows == 0) {
        $_SESSION['used_questions'] = [];
        $result = $conn->query("SELECT * FROM questions ORDER BY RAND() LIMIT 1");
    }

    $question = $result->fetch_assoc();
    $_SESSION['current_question'] = $question;
    $_SESSION['used_questions'][] = $question['id'];
} else {
    $question = $_SESSION['current_question'];
}

$options = [1, 2, 3, 4];

// 50-50, aici se aplica, eliminam doua variante gresite
if (isset($_POST['use_powerup']) && $_SESSION['powerup_50_50'] && !$_SESSION['powerup_used']) {
    $_SESSION['powerup_used'] = true;
    $correctAnswer = $_SESSION['current_question']['correct_option'];
    shuffle($options);
    foreach ($options as $option) {
        if ($option != $correctAnswer && count($options) > 2) {
            unset($options[array_search($option, $options)]);
        }
    }
}

if (isset($_POST['reset_game'])) {
    $_SESSION['powerup_used'] = false;
    $_SESSION['score'] = 0;
    $_SESSION['game_started'] = true;
    $_SESSION['time_left'] = 30;
    $_SESSION['used_questions'] = [];
    unset($_SESSION['current_question']);
    header("Location: game.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trivia Game</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #ff9a9e, #fad0c4);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .score-container {
            position: fixed;
            top: 10px;
            right: 10px;
            font-size: 1.5rem;
            padding: 15px;
            border-radius: 15px;
        }

        .leaderboard-btn, .powerup-btn, button {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.2rem;
            background: #ffcc00;
            color: black;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .leaderboard-btn:hover, .powerup-btn:hover, button:hover {
            background: #ff9900;
            transform: scale(1.1);
        }

        .powerup-btn:disabled {
            background: grey;
            cursor: not-allowed;
        }

        .question-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 70%;
            max-width: 600px;
            margin-top: 50px;
        }

        button {
            padding: 12px 25px;
            margin: 10px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
        }

        button:hover {
            background-color:rgb(255, 0, 0);
            color: white;
        }

        #timer {
            font-size: 2rem;
            color: red;
            margin-bottom: 20px;
            font-weight: bold;
        }
        h1 {
    font-size: 2.3rem;
    cursor: pointer;
    transition: color 0.3s ease, transform 0.3s ease;
}

h1:hover {
    color:rgb(255, 142, 29);
    transform: scale(1.1);
}

    </style>
    <script>
        let timeLeft = 30;

        function startTimer() {
            const timerElement = document.getElementById('timer');
            const countdown = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    alert('Timpul a expirat! Răspuns trimis automat.');
                    document.getElementById('timeoutForm').submit();
                } else {
                    timerElement.textContent = `Timp rămas: ${timeLeft} secunde`;
                    timeLeft--;
                }
            }, 1000);
        }

        window.onload = startTimer;
    </script>
</head>
<body>
    <div class="score-container">
        🏆 Scor: <?php echo $score; ?><br>
        🥇 Highest Score: <?php echo $highest_score; ?>
        <a href="leaderboard.php"><button class="leaderboard-btn">🏅 Clasament</button></a>
        <a href="logout.php"><button class="leaderboard-btn" style="background: red; color: white;">🚪 Logout</button></a>
        

        <form method="POST">
            <button type="submit" name="use_powerup" class="powerup-btn" <?php echo $_SESSION['powerup_used'] ? 'disabled' : ''; ?>>🧩 Power-Up 50-50 + Resetare Cronometru</button>
        </form>


        <form method="POST">
            <button type="submit" name="reset_game" class="leaderboard-btn" style="background: orange;">🔄 Resetează Jocul</button>
        </form>
    </div>

    <h1>🎉 Bine ai venit la Trivia, <?php echo htmlspecialchars($username); ?>!</h1>

    <div class="question-container">
        <p id="timer">Timp rămas: 30 secunde</p>
        <h2><?php echo htmlspecialchars($question['question_text']); ?></h2>
        <form method="POST" action="process_answer.php" id="timeoutForm">
            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">

            <?php foreach ($options as $option): ?>
                <button type="submit" name="answer" value="<?php echo $option; ?>"><?php echo htmlspecialchars($question["option_$option"]); ?></button>
            <?php endforeach; ?>
        </form>
    </div>
</body>
</html>
