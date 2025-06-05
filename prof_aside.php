
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTeam - Dashboard Enseignant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #a855f7;
            --secondary-color: #ec4899;
            --accent-color: #ff6b9c;
            --text-light: #ffffff;
            --background-light: #f8f9fc;
        }

        body {
            background-color: var(--background-light);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar corrigée */
.sidebar {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    width: 230px;
    min-height: 100vh;
    position: fixed;
    padding: 25px 15px;
    color: var(--text-light);
    box-shadow: 5px 0 25px rgba(168, 85, 247, 0.2);
    z-index: 1000;
    top: 0;
    left: 0;
}

.menu-item {
    padding: 12px 20px;
    margin: 8px 0;
    border-radius: 8px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none !important; /* Empêche le soulignement */
}

        .menu-item.active {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .menu-item:hover:not(.active) {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }
.main-content {
    margin-left: 230px; /* Correspond à la largeur de la sidebar */
    padding: 30px;
    min-height: 100vh;
    box-sizing: border-box;
    width: calc(100% - 230px); /* Empêche le dépassement horizontal */
}

        .welcome-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.1);
            border-left: 4px solid var(--primary-color);
        }

        .welcome-box h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin: 15px;
            box-shadow: 0 4px 12px rgba(168, 85, 247, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #f3f3f3;
            display: flex;
            flex-direction: column;
            gap: 15px;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background-color: var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(168, 85, 247, 0.15);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-shadow: 0 2px 4px rgba(168, 85, 247, 0.1);
            line-height: 1;
            text-align: center;
            transition: all 0.5s ease;
        }

        .stat-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-title {
            color: #6C757D;
            font-size: 1rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            margin: 0;
            white-space: nowrap;
        }

        .icon-box {
            border-radius: 10px;
            background: rgba(168, 85, 247, 0.1);
            color: var(--primary-color);
            transition: transform 0.3s;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-box:hover {
            transform: scale(1.05);
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eaeaea;
        }
        
        .dashboard-header h1 {
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .user-name {
            font-weight: 600;
            color: #495057;
        }
        
        .user-role {
            background: rgba(168, 85, 247, 0.1);
            color: var(--primary-color);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        a{
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .stat-card {
                margin: 10px 0;
            }
        }
        
        /* Animation pour les nombres */
        @keyframes countUp {
            from { transform: scale(1.2); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .animate-count {
            animation: countUp 0.8s ease forwards;
        }
    </style>
</head>
<body>
 <div class="sidebar">
        <div class="d-flex flex-column">
           
            <a href="#" class="menu-item active">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="courses.php" class="menu-item">
                <i class="fas fa-book-open"></i>
                <span>Mes Cours</span>
            </a>
            
            <a href="quizzes.php" class="menu-item">
                <i class="fas fa-question-circle"></i>
                <span>Quiz</span>
            </a>

            <a href="./logout.php" class="menu-item" style="margin-top: 300px
            ;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Gestion des clics sur les éléments du menu
    document.querySelectorAll('.menu-item').forEach(item => {
        item.addEventListener('click', function() {
            // Exclure l'élément de déconnexion de la gestion active
            if (!this.querySelector('a')) {
                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // Animation au survol des cartes
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Animation de comptage
    document.addEventListener('DOMContentLoaded', function() {
        // Supprimer la classe d'animation après qu'elle soit terminée
        setTimeout(() => {
            document.querySelectorAll('.animate-count').forEach(el => {
                el.classList.remove('animate-count');
            });
        }, 800);
    });
</script>
</body>
</html>