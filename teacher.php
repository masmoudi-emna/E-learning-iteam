<?php
session_start();
if (!isset($_SESSION['name'])) { 
    header("Location: connect.php");
    exit();
}
elseif($_SESSION['rôle']=="admin"){
    header("Location: connect.php");
    exit();
}
elseif($_SESSION['rôle']=="student"){
    header("Location: connect.php");
    exit();
}

$host = 'localhost';
$dbname = 'schoolproject';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connexion échouée: " . $e->getMessage());
}
$query = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM cours) AS total_cours,
        (SELECT COUNT(*) FROM inscription_cours) AS total_inscriptions,
        (SELECT COUNT(*) FROM personne WHERE rôle = 'student') AS total_etudiants,
        (SELECT COUNT(*) FROM quiz) AS total_quiz,
        (SELECT COUNT(*) FROM quiz WHERE etat = 'publié') AS quiz_publies,
        (SELECT COUNT(*) FROM quiz WHERE etat = 'brouillon') AS quiz_brouillon,
        (SELECT COUNT(*) FROM quiz WHERE etat = 'archivé') AS quiz_archives
");
$query->execute();
$stats = $query->fetch(PDO::FETCH_ASSOC);

// Récupérer les quiz récents
$query = $pdo->prepare("
    SELECT q.id, q.titre, q.etat, c.titre AS cours_titre, q.date_creation 
    FROM quiz q
    JOIN cours c ON q.cours_id = c.id
    ORDER BY q.date_creation DESC
    LIMIT 5
");
$query->execute();
$recentQuiz = $query->fetchAll(PDO::FETCH_ASSOC);

// Probabilité de quiz par cours
$query = $pdo->prepare("
    SELECT c.titre, 
           COUNT(q.id) AS total_quiz,
           ROUND(COUNT(q.id) * 100.0 / (SELECT COUNT(*) FROM quiz), 1) AS probabilite
    FROM cours c
    LEFT JOIN quiz q ON c.id = q.cours_id
    GROUP BY c.id
    HAVING total_quiz > 0
    ORDER BY probabilite DESC
");
$query->execute();
$quizProbabilities = $query->fetchAll(PDO::FETCH_ASSOC);

// Préparation des données pour les graphiques
$quizStatusLabels = ['Publiés', 'Brouillons', 'Archivés'];
$quizStatusData = [
    $stats['quiz_publies'], 
    $stats['quiz_brouillon'], 
    $stats['quiz_archives']
];

$quizProbLabels = array_map(function($item) { return $item['titre']; }, $quizProbabilities);
$quizProbData = array_map(function($item) { return $item['probabilite']; }, $quizProbabilities);

// Données pour le graphique d'activité (simulées)
$mois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
$connexionsData = [12, 19, 15, 22, 18, 25, 30, 28, 32, 40, 38, 45];
$quizData = [2, 5, 3, 7, 6, 8, 10, 9, 12, 15, 10, 8];
$coursData = [1, 3, 2, 4, 3, 5, 4, 6, 5, 7, 6, 8];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Enseignant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --primary-light: #a78bfa;
            --secondary: #10b981;
            --warning: #f59e0b;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #64748b;
            --card-radius: 16px;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            background-color: #f5f7ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark);
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            width: 250px;
            min-height: 100vh;
            position: fixed;
            padding: 20px 0;
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .sidebar-header {
            text-align: center;
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            font-weight: 600;
            margin: 0;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .menu-item {
            padding: 12px 20px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.15);
            transform: translateX(5px);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
        }
        
        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .dashboard-header h1 {
            color: var(--primary);
            font-weight: 800;
            font-size: 2.2rem;
            position: relative;
            display: inline-block;
        }
        
        .dashboard-header h1:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: white;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: var(--card-radius);
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s;
            height: 100%;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stat-title {
            color: var(--gray);
            font-size: 1rem;
            font-weight: 500;
        }
        
        .chart-container {
            background: white;
            border-radius: var(--card-radius);
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
            height: 100%;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title {
            color: var(--primary);
            font-weight: 700;
            margin: 0;
            font-size: 1.3rem;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        
        /* Welcome box */
        .welcome-box {
            background: linear-gradient(135deg, #f0f4ff, #f9f5ff);
            border-radius: var(--card-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            border-left: 4px solid var(--primary);
        }
        
        .welcome-box h3 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .welcome-box p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #555;
            max-width: 800px;
        }
        
        /* Recent items */
        .recent-box {
            background: white;
            border-radius: var(--card-radius);
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: var(--card-shadow);
            height: 100%;
        }
        
        .recent-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .recent-title {
            color: var(--primary);
            font-weight: 700;
            margin: 0;
            font-size: 1.3rem;
        }
        
        .recent-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
            transition: background 0.2s;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-item:hover {
            background-color: #f8fafc;
            border-radius: 10px;
        }
        
        .item-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .quiz-icon {
            background: var(--primary);
        }
        
        .course-icon {
            background: var(--secondary);
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-title {
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        .item-meta {
            font-size: 0.9rem;
            color: var(--gray);
            display: flex;
            gap: 15px;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-published {
            background: rgba(16, 185, 129, 0.15);
            color: var(--secondary);
        }
        
        .status-draft {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }
        
        .status-archived {
            background: rgba(107, 114, 128, 0.15);
            color: var(--gray);
        }
        
        /* Progress bars */
        .probability-item {
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .probability-item:last-child {
            border-bottom: none;
        }
        
        .progress {
            height: 12px;
            border-radius: 6px;
            margin-top: 8px;
            background-color: #e2e8f0;
        }
        
        .progress-bar {
            background-color: var(--primary);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-chalkboard-teacher me-2"></i>Tableau de Bord</h3>
        </div>
        <div class="d-flex flex-column">
            <a href="#" class="menu-item active">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>
            <a href="courses.php" class="menu-item">
                <i class="fas fa-book-open"></i>
                <span>Mes Cours</span>
            </a>
            <a href="quizzes.php" class="menu-item">
                <i class="fas fa-question-circle"></i>
                <span>Quiz</span>
            </a>
            <a href="students.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Étudiants</span>
            </a>
            <a href="analytics.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Analytiques</span>
            </a>
            <a href="logout.php" class="menu-item mt-4">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="dashboard-header">
            <h1>Tableau de Bord Enseignant</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo substr($_SESSION['name'], 0, 1); ?>
                </div>
                <div>
                    <div class="fw-bold"><?php echo $_SESSION['name']; ?></div>
                    <div class="small text-muted">Enseignant</div>
                </div>
            </div>
        </div>
        
        <div class="welcome-box">
            <h3>Bienvenue, <?php echo $_SESSION['name']; ?>!</h3>
            <p>Vous pouvez gérer vos cours, créer des quiz et suivre les progrès de vos étudiants depuis ce tableau de bord. Consultez les statistiques récentes pour optimiser votre enseignement.</p>
        </div>
        
        <div class="row">
            <!-- Statistiques principales -->
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <i class="fas fa-book"></i>
                        <?php echo $stats['total_cours']; ?>
                    </div>
                    <div class="stat-title">Cours Actifs</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <i class="fas fa-users"></i>
                        <?php echo $stats['total_etudiants']; ?>
                    </div>
                    <div class="stat-title">Étudiants Inscrits</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <i class="fas fa-question-circle"></i>
                        <?php echo $stats['total_quiz']; ?>
                    </div>
                    <div class="stat-title">Quiz Créés</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number">
                        <i class="fas fa-clipboard-list"></i>
                        <?php echo $stats['total_inscriptions']; ?>
                    </div>
                    <div class="stat-title">Inscriptions</div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <!-- Statuts des quiz -->
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        <h4 class="chart-title">Statut des Quiz</h4>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="quizStatusChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Probabilité de quiz par cours -->
            <div class="col-md-6">
                <div class="chart-container">
                    <div class="chart-header">
                        <h4 class="chart-title">Probabilité de Quiz par Cours</h4>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="quizProbabilityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <!-- Quiz récents -->
            <div class="col-md-6">
                <div class="recent-box">
                    <div class="recent-header">
                        <h4 class="recent-title">Quiz Récents</h4>
                        <a href="quizzes.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
                    </div>
                    <?php foreach ($recentQuiz as $quiz): ?>
                    <div class="recent-item">
                        <div class="item-icon quiz-icon">
                            <i class="fas fa-question"></i>
                        </div>
                        <div class="item-info">
                            <div class="item-title"><?php echo $quiz['titre']; ?></div>
                            <div class="item-meta">
                                <span>Cours: <?php echo $quiz['cours_titre']; ?></span>
                                <span>Créé: <?php echo date('d/m/Y', strtotime($quiz['date_creation'])); ?></span>
                                <span class="status-badge 
                                    <?php 
                                    if ($quiz['etat'] == 'publié') echo 'status-published';
                                    elseif ($quiz['etat'] == 'brouillon') echo 'status-draft';
                                    else echo 'status-archived';
                                    ?>">
                                    <?php echo $quiz['etat']; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Probabilités détaillées -->
            <div class="col-md-6">
                <div class="recent-box">
                    <div class="recent-header">
                        <h4 class="recent-title">Probabilités par Cours</h4>
                    </div>
                    <?php foreach ($quizProbabilities as $prob): ?>
                    <div class="probability-item">
                        <div class="d-flex justify-content-between">
                            <strong><?php echo $prob['titre']; ?></strong>
                            <span><?php echo $prob['probabilite']; ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 style="width: <?php echo $prob['probabilite']; ?>%; background-color: <?php 
                                 $hue = (100 - $prob['probabilite']) * 2.4; 
                                 echo "hsl($hue, 70%, 45%)";
                                 ?>;" 
                                 aria-valuenow="<?php echo $prob['probabilite']; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <!-- Activité récente -->
            <div class="col-md-12">
                <div class="chart-container">
                    <div class="chart-header">
                        <h4 class="chart-title">Activité Récente</h4>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Graphique Statuts des quiz
        const quizStatusCtx = document.getElementById('quizStatusChart').getContext('2d');
        const quizStatusChart = new Chart(quizStatusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($quizStatusLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($quizStatusData); ?>,
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#64748b'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
        
        // Graphique des probabilités
        const quizProbCtx = document.getElementById('quizProbabilityChart').getContext('2d');
        const quizProbChart = new Chart(quizProbCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($quizProbLabels); ?>,
                datasets: [{
                    label: 'Probabilité (%)',
                    data: <?php echo json_encode($quizProbData); ?>,
                    backgroundColor: '#7c3aed',
                    borderColor: '#6d28d9',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
        
        // Graphique d'activité
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($mois); ?>,
                datasets: [
                    {
                        label: 'Connexions',
                        data: <?php echo json_encode($connexionsData); ?>,
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Quiz créés',
                        data: <?php echo json_encode($quizData); ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Cours créés',
                        data: <?php echo json_encode($coursData); ?>,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>