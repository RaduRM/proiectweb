<?php
session_start();
require 'includes/db_connect.php';

// se verifica autentificarea
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_id = $_POST['question_id'];
    $selected_answer = $_POST['answer'] ?? null;

    // obtinem raspunsul corect la intrebarea curenta
    $question = $_SESSION['current_question'];
    $correct_answer = $question['correct_option'];

    // score curent si highest score
    $stmt_highest = $conn->prepare("SELECT score, highest_score FROM users WHERE id = ?");
    $stmt_highest->bind_param("i", $_SESSION['user_id']);
    $stmt_highest->execute();
    $score_data = $stmt_highest->get_result()->fetch_assoc();
    $score = $score_data['score'];
    $highest_score = $score_data['highest_score'];

    // in caz ca timpul a epirat
    if ($selected_answer === null) {
        $message = "⏰ Timpul a expirat! Răspunsul corect era: " . $question["option_{$correct_answer}"];
        $_SESSION['current_question'] = null;
    }
    // in cazul in care raspunsul e corect/gresit
    elseif ($selected_answer == $correct_answer) {
        $_SESSION['score'] += 10;
        $stmt_update_score = $conn->prepare("UPDATE users SET score = score + 10 WHERE id = ?");
        $stmt_update_score->bind_param("i", $_SESSION['user_id']);
        $stmt_update_score->execute();

        if ($_SESSION['score'] > $highest_score) {
            $stmt_update_highest = $conn->prepare("UPDATE users SET highest_score = ? WHERE id = ?");
            $stmt_update_highest->bind_param("ii", $_SESSION['score'], $_SESSION['user_id']);
            $stmt_update_highest->execute();
        }
        $_SESSION['current_question'] = null;
        $message = "✔️ Răspuns corect! Ai câștigat 10 puncte.";
    } else {
        $message = "❌ Răspuns greșit! Răspunsul corect era: " . $question["option_{$correct_answer}"];
        $_SESSION['current_question'] = null;
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezultatul Întrebării</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            background: linear-gradient(135deg, #ff9a9e, #fad0c4);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .score-container {
            position: relative;
            top: 0;
            font-size: 1.5rem;
            padding: 15px;
            border-radius: 15px;
            display: flex;
            justify-content: center;
            gap: 20px;
            align-items: center;
        }

        .message {
            font-size: 1.5rem;
            padding: 20px;
            border-radius: 15px;
            animation: fadeIn 1s ease-in-out;
        }

        .correct {
            background-color: rgba(144, 238, 144, 0.8);
            color: green;
        }

        .wrong {
            background-color: rgba(255, 99, 71, 0.8);
            color: red;
        }

        .next-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.2rem;
            background: #4CAF50;
            color: white;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .next-btn:hover {
            background: #45a049;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="score-container">
        <h1>🏆 Scor: <?php echo $_SESSION['score']; ?></h1>
        <h1>🥇 Highest Score: <?php echo $highest_score; ?></h1>
        <a href="leaderboard.php"><button class="next-btn">🏅 Clasament</button></a>
    </div>

    <h1>Rezultatul Întrebării</h1>
    <div class="message <?php echo ($selected_answer == $correct_answer) ? 'correct' : 'wrong'; ?>">
        <?php echo $message; ?>
    </div>

    <div style="margin-top: 20px;">
        <a href="game.php"><button class="next-btn">Următoarea Întrebare</button></a>
    </div>
</body>
</html>
