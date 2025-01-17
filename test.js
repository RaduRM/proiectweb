<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Chart.js</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Grafic Test Chart.js</h1>
    <canvas id="testChart"></canvas>

    <script>
        const ctx = document.getElementById('testChart').getContext('2d');
        const testChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ianuarie', 'Februarie', 'Martie'],
                datasets: [{
                    label: 'Exemplu Scoruri',
                    data: [10, 20, 30],
                    backgroundColor: ['red', 'blue', 'green']
                }]
            }
        });
    </script>
</body>
</html>
