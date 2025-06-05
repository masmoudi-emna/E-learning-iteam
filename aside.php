
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTeam - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #db2777;
            --accent-color: #ff6b9c;
            --text-light: #ffffff;
            --background-light: #f8f9fc;
            --sidebar-width: 280px;
        }

        body {
            background-color: var(--background-light);
            min-height: 100vh;
            margin-left: var(--sidebar-width);
            font-family: 'Segoe UI', sans-serif;
        }

        .sidebar {
            background: linear-gradient(160deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            width: var(--sidebar-width);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            color: var(--text-light);
            box-shadow: 8px 0 30px rgba(124, 58, 237, 0.15);
            display: flex;
            flex-direction: column;
            padding: 0.5rem;
        }

        .menu-item {
            padding: 0.5rem 1rem !important;
            margin: 2px 0; 
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 1.2rem;
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.95);
            text-decoration: none !important;
            position: relative;
            overflow: hidden;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: left 0.4s ease;
        }

        .menu-item:hover::before {
            left: 0;
        }

        .menu-item.active {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .menu-item i {
            width: 28px;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .menu-item:hover i {
            transform: scale(1.1);
        }

        .sidebar-header {
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            font-weight: 600;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .logo {
            width: 40px;
            margin-right: 12px;
        }

        .user-profile {
           /*  margin-top: auto; */
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.5rem;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header d-flex align-items-center">
        <i class="fas fa-university fa-2x"></i>
        <h3 class="ms-3">iTeam </h3>
    </div>

    <div class="d-flex flex-column flex-grow-1">
        <a href="gestion_admission.php" class="menu-item active">
            <i class="fas fa-user-graduate"></i>
            <span>Admission</span>
        </a>
        
        <a href="gestion_user.php" class="menu-item">
            <i class="fas fa-user-plus"></i>
            <span>Utilisateurs</span>
        </a>

        <a href="gestion_prof.php" class="menu-item">
            <i class="fas fa-users"></i>
            <span>Professeurs</span>
        </a>

        <a href="gestion_cour.php" class="menu-item">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Cours</span>
        </a>

    </div>

    <div class="user-profile">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="fas fa-user-circle fa-2x"></i>
            </div>
            <div>
                <h6 class="mb-0">Admin Name</h6>
                <small>Administrator</small>
            </div>
        </div>
    </div>

    <a href="./logout.php" class="menu-item ">
        <i class="fas fa-sign-out-alt"></i>
        <span>Déconnexion</span>
    </a>
</div>

<script>
document.querySelectorAll('.menu-item').forEach(item => {
    item.addEventListener('click', function(e) {
        if(!this.classList.contains('active')) {
            document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

// Ajouter une animation au chargement
window.addEventListener('load', () => {
    document.querySelectorAll('.menu-item').forEach((item, index) => {
        item.style.animation = `fadeInRight 0.5s ease ${index * 0.1}s forwards`;
    });
});
</script>

</body>
</html>