<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Trivia Game</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
            font-family: 'Poppins', sans-serif;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 350px;
            text-align: center;
        }

        h1 {
            font-size: 2rem;
            color: #333;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 10px;
            outline: none;
            font-size: 1rem;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #ff4b2b;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #ff416c;
        }

        .error {
            color: red;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Autentificare</h1>
        <?php
        require 'includes/db_connect.php';
        session_start();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    echo "<p class='success'>Autentificare reușită! Redirecționare...</p>";
                    header("Refresh: 2; url=game.php");
                } else {
                    echo "<p class='error'>Parola incorectă!</p>";
                }
            } else {
                echo "<p class='error'>Utilizator inexistent!</p>";
            }
        }
        ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Nume utilizator" required>
            <input type="password" name="password" placeholder="Parola" required>
            <button type="submit">Autentificare</button>
        </form>
        <p>Nu ai cont? <a href="signup.php">Înregistrează-te</a></p>
        <p><a href="forgot_password.php">Ai uitat parola?</a></p>
    </div>
</body>
</html>
