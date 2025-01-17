<?php
require 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // Verificăm dacă emailul există în baza de date
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generăm un token unic
        $token = bin2hex(random_bytes(50));

        // Salvăm tokenul în baza de date
        $stmt = $conn->prepare("UPDATE users SET verification_code = ? WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Trimitem emailul cu link-ul de resetare
        $reset_link = "http://localhost/trivia-game/reset_password.php?token=$token";
        $subject = "Resetează-ți parola";
        $message = "Click pe acest link pentru a-ți reseta parola: $reset_link";
        $headers = "From: no-reply@trivia-game.com";

        // Trimiterea emailului
        mail($email, $subject, $message, $headers);

        echo "<p style='color: green;'>Email de resetare trimis. Verifică-ți emailul!</p>";
    } else {
        echo "<p style='color: red;'>Adresa de email nu există în sistem.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetare Parolă</title>
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

        p a {
            color: #ff416c;
            text-decoration: none;
        }

        p a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="login-container">
        <h1>Ai uitat parola?</h1>
        <form method="POST">
            <input type="email" name="email" placeholder="Introdu adresa de email" required>
            <button type="submit">Trimite link de resetare</button>
        </form>
        <p><a href="index.php">Înapoi la Login</a></p>
    </div>
</body>
</html>
