<?php
session_start();
require 'includes/db_connect.php';

// Obținerea scorurilor împreună cu username din baza de date
$query = "
    SELECT users.username, scores.user_id, MAX(scores.score) AS score, scores.date
    FROM scores
    JOIN users ON users.id = scores.user_id
    GROUP BY scores.user_id, scores.date
    ORDER BY scores.user_id, scores.date ASC
";
$result = $conn->query($query);

$users = [];
$dates = [];

// Colectarea datelor pentru fiecare utilizator în parte
while ($row = $result->fetch_assoc()) {
    $username = $row['username'];
    if (!isset($users[$username])) {
        $users[$username] = [
            'scores' => [],
            'username' => $username
        ];
    }
    $users[$username]['scores'][] = $row['score'];

    // Salvăm datele doar o singură dată pentru toți utilizatorii
    if (!in_array($row['date'], $dates)) {
        $dates[] = $row['date'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafic Scoruri - Toți Utilizatorii</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            text-align: center;
            background: linear-gradient(135deg, #8e44ad, #f39c12);
            color: white;
        }

        canvas {
            width: 800px;         /* Ocupă aproape întreaga lățime a paginii */
            max-height: 400px;      /* Înălțime mai mare pentru vizualizare clară */
            margin: auto;
            border: 1px solid #333;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        h1 {
            font-size: 2.5rem;
            margin-top: 20px;
        }

        .button-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        a {
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

        /* Efect de hover pentru un look modern */
        canvas:hover {
            transform: scale(1.02);
            transition: transform 0.3s ease-in-out;
        }
    </style>
</head>
<body>
    <h1>📊 Graficul Scorurilor pentru Toți Utilizatorii 📊</h1>
    
    <!-- Container pentru grafic -->
    <canvas id="scoreChart"></canvas>

    <!-- Buton pentru a reveni la leaderboard -->
    <div class="button-container">
        <a href="leaderboard.php">🏆 Înapoi la Clasament</a>
    </div>

    <script>
        // Datele sunt preluate din PHP
        const dates = <?php echo json_encode($dates); ?>;
        const usersData = <?php echo json_encode($users); ?>;

        const datasets = [];

        // Generare date pentru fiecare utilizator
        Object.keys(usersData).forEach((username, index) => {
            datasets.push({
                label: username,
                data: usersData[username].scores,
                borderColor: `hsl(${index * 100}, 70%, 50%)`,
                borderWidth: 3,
                pointRadius: 5, 
                pointHoverRadius: 7, 
                tension: 0.4,  
                fill: true,    
                backgroundColor: `rgba(${index * 100}, 150, 200, 0.2)`
            });
        });

        // Inițializarea graficului dinamic
        const ctx = document.getElementById('scoreChart').getContext('2d');
        const scoreChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, /* Aspect flexibil pentru grafic mare */
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Zile',
                            color: 'white'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.2)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Scor',
                            color: 'white'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.2)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
