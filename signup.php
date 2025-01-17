<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Trivia Game</title>
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

        .signup-container {
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

        .success {
            color: green;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h1>Înregistrează-te</h1>
        <?php
        require 'includes/db_connect.php';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                echo "<p class='error'>Parolele nu se potrivesc!</p>";
            } elseif (strlen($password) < 6) {
                echo "<p class='error'>Parola trebuie să aibă minim 6 caractere!</p>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $check_username = $conn->prepare("SELECT * FROM users WHERE username = ?");
                $check_username->bind_param("s", $username);
                $check_username->execute();
                $result_username = $check_username->get_result();

                $check_email = $conn->prepare("SELECT * FROM users WHERE email = ?");
                $check_email->bind_param("s", $email);
                $check_email->execute();
                $result_email = $check_email->get_result();

                if ($result_username->num_rows > 0) {
                    echo "<p class='error'>Numele de utilizator este deja folosit!</p>";
                } elseif ($result_email->num_rows > 0) {
                    echo "<p class='error'>Adresa de email este deja folosită!</p>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $username, $email, $hashed_password);

                    if ($stmt->execute()) {
                        echo "<p class='success'>Cont creat cu succes! Redirecționare...</p>";
                        header("Refresh: 2; url=index.php");
                    } else {
                        echo "<p class='error'>Eroare la crearea contului!</p>";
                    }
                }
            }
        }
        ?>

        <form method="POST" action="">
            <input type="text" name="username" id="username" placeholder="Nume utilizator" required>
            <input type="email" name="email" id="email" placeholder="Email" required>
            <input type="password" name="password" id="password" placeholder="Parolă" required>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmă Parola" required>
            <p class="error" id="error-message"></p>
            <button type="submit">Creează cont</button>
        </form>
        <p>Ai deja cont? <a href="index.php">Autentificare</a></p>
    </div>
</body>
</html>
