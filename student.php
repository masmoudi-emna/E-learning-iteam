<?php
session_start();

// Redirige si non connecté ou mauvais rôle
if (!isset($_SESSION['name']) || $_SESSION['rôle'] !== "student") {
    header("Location: connect.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "schoolproject";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des cours
$sqlCourses = "SELECT id, titre, description FROM cours";
$stmtCourses = $conn->query($sqlCourses);
$courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

// Récupération des quiz
$sqlQuizzes = "SELECT id, titre, cours_id FROM quiz";
$stmtQuizzes = $conn->query($sqlQuizzes);
$quizzes = $stmtQuizzes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .navbar {
              background: linear-gradient(135deg, #6366f1 0%, #a855f7 50%, #ec4899 100%);
            color: white;
            padding: 15px 30px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 30px;
        }
        
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .brand i {
            font-size: 1.8rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-card {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .user-card i {
            font-size: 1.2rem;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }
        
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        
        .welcome-title {
            color: #4a2a80;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .section { 
            margin-bottom: 40px; 
        }
        
        .section-title {
            color: #5d3b9c;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #d0bfff;
            font-size: 1.8rem;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .card { 
            background: white;
            border-radius: 12px; 
            padding: 25px; 
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #e9e1f9;
            position: relative;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #8a63d2, #5e35b1);
        }
        
        .card h3 {
            color: #5d3b9c;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .quiz-section {
            background: #f5f2ff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .quiz-section h4 {
            color: #7e57c2;
            margin-bottom: 15px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quiz-section h4 i {
            color: #7e57c2;
        }
        
        .quiz { 
            background: white;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
            border-left: 3px solid #7e57c2;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quiz:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border-left: 3px solid #5d3b9c;
        }
        
        .quiz a {
            color: #5d3b9c;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
        }
        
        .quiz a:hover {
            color: #4527a0;
        }
        
        .quiz i {
            color: #8a63d2;
        }
        
        .no-content {
            text-align: center;
            color: #888;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            font-size: 1.1rem;
        }
        
        .footer {
            text-align: center;
            margin-top: 50px;
            color: #777;
            padding: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Barre de navigation -->
        <nav class="navbar">
            <div class="brand">
                <i class="fas fa-graduation-cap"></i>
                <span>Espace Étudiant</span>
            </div>
            <div class="user-info">
                <div class="user-card">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </div>
                <button class="logout-btn" onclick="location.href='logout.php'">
                    <i class="fas fa-sign-out-alt"></i>
                    Déconnexion
                </button>
            </div>
        </nav>
        
        <h1 class="welcome-title">Bienvenue, <?php echo htmlspecialchars($_SESSION['name']); ?> !</h1>
        
        <!-- Section des cours -->
        <div class="section">
            <h2 class="section-title"><i class="fas fa-book-open"></i> Cours Disponibles</h2>
            
            <?php if (count($courses) > 0): ?>
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="card">
                            <h3><?php echo htmlspecialchars($course['titre']); ?></h3>
                            <p><?php echo htmlspecialchars($course['description']); ?></p>
                            
                            <!-- Quiz associés à ce cours -->
                            <div class="quiz-section">
                                <h4><i class="fas fa-tasks"></i> Quiz associés</h4>
                                <?php $hasQuizzes = false; ?>
                                <?php foreach ($quizzes as $quiz): ?>
                                    <?php if ($quiz['cours_id'] == $course['id']): ?>
                                        <?php $hasQuizzes = true; ?>
                                        <div class="quiz">
                                            <a href="passer_quiz.php?quiz_id=<?php echo $quiz['id']; ?>">
                                                <i class="fas fa-question-circle"></i>
                                                <?php echo htmlspecialchars($quiz['titre']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                
                                <?php if (!$hasQuizzes): ?>
                                    <p style="color: #888; text-align: center;">Aucun quiz disponible</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-content">
                    <i class="fas fa-book" style="font-size: 3rem; margin-bottom: 15px; color: #bbb;"></i>
                    <p>Aucun cours disponible pour le moment</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>Plateforme Éducative © <?php echo date('Y'); ?> - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>