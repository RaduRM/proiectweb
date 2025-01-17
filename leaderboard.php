<?php
session_start();
require 'includes/db_connect.php';

// Verificare autentificare
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Obținerea clasamentului
$stmt = $conn->prepare("SELECT username, highest_score FROM users ORDER BY highest_score DESC LIMIT 10");
$stmt->execute();
$result = $stmt->get_result();
$leaderboard = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clasament Trivia</title>
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
            margin: 0;
        }

        .leaderboard-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
            animation: fadeIn 1s ease-in-out;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f2f2;
        }

        tr:hover {
            background: #ddd;
        }

        a {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        a:hover {
            background: #45a049;
        }

        .button-container {
            margin-top: 20px;
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <h1>🏆 Clasamentul Trivia</h1>
    <div class="leaderboard-container">
        <table>
            <tr>
                <th>Loc</th>
                <th>Utilizator</th>
                <th>Cel mai mare scor</th>
            </tr>
            <?php $rank = 1; ?>
            <?php foreach ($leaderboard as $user): ?>
            <tr>
                <td><?php echo $rank++; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo $user['highest_score']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <!-- Butoane pentru a reveni la joc și pentru a merge la grafice -->
    <div class="button-container">
        <a href="game.php">🔙 Înapoi la joc</a>
        <a href="grafice.php">📊 Vezi Graficul</a>
    </div>
</body>
</html>
