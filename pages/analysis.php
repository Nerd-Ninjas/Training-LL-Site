<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Dashboard Graphs</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card h3 {
            margin-bottom: 15px;
            color: #333;
        }

        canvas {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <h1>LMS Dashboard Graphs</h1>
    <div class="container">
        <div class="card">
            <h3>Course Completion Rate</h3>
            <canvas id="completionRate"></canvas>
        </div>
        <div class="card">
            <h3>User Engagement</h3>
            <canvas id="userEngagement"></canvas>
        </div>
        <div class="card">
            <h3>Quiz Performance</h3>
            <canvas id="quizPerformance"></canvas>
        </div>
        <div class="card">
            <h3>Monthly Signups</h3>
            <canvas id="monthlySignups"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Course Completion Rate (Pie Chart)
        new Chart(document.getElementById('completionRate'), {
            type: 'pie',
            data: {
                labels: ['Completed', 'In Progress', 'Not Started'],
                datasets: [{
                    data: [40, 35, 25],
                    backgroundColor: ['#4caf50', '#ffeb3b', '#f44336']
                }]
            }
        });

        // User Engagement (Bar Chart)
        new Chart(document.getElementById('userEngagement'), {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [{
                    label: 'Hours Spent',
                    data: [12, 19, 3, 17],
                    backgroundColor: '#2196f3'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Quiz Performance (Line Chart)
        new Chart(document.getElementById('quizPerformance'), {
            type: 'line',
            data: {
                labels: ['Quiz 1', 'Quiz 2', 'Quiz 3', 'Quiz 4'],
                datasets: [{
                    label: 'Average Score',
                    data: [85, 78, 92, 88],
                    borderColor: '#673ab7',
                    fill: false
                }]
            },
            options: {
                responsive: true
            }
        });

        // Monthly Signups (Doughnut Chart)
        new Chart(document.getElementById('monthlySignups'), {
            type: 'doughnut',
            data: {
                labels: ['January', 'February', 'March', 'April'],
                datasets: [{
                    data: [200, 150, 300, 250],
                    backgroundColor: ['#ff5722', '#03a9f4', '#8bc34a', '#ffc107']
                }]
            }
        });
    </script>
</body>
</html>
