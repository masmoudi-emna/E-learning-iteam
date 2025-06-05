<?php
session_start();

// Redirige vers la page de connexion si l'utilisateur n'est PAS connecté
if (!isset($_SESSION['name'])) {
    header("Location: connect.php");
    exit();
} elseif ($_SESSION['rôle'] == "student") {
    header("Location: connect.php");
    exit();
} elseif ($_SESSION['rôle'] == "teacher") {
    header("Location: connect.php");
    exit();
}

// Configuration BDD
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";
$conn = mysqli_connect($host, $user, $password, $db);

// Récupération des statistiques
$users_count = mysqli_query($conn, "SELECT COUNT(*) FROM personne")->fetch_row()[0];
$teachers_count = mysqli_query($conn, "SELECT COUNT(*) FROM personne WHERE rôle = 'teacher'")->fetch_row()[0];
$courses_count = mysqli_query($conn, "SELECT COUNT(*) FROM cours")->fetch_row()[0];

$admission_refuse = mysqli_query($conn, "SELECT COUNT(*) FROM admission WHERE statut = 'refusé'")->fetch_row()[0];
$admission_attente = mysqli_query($conn, "SELECT COUNT(*) FROM admission WHERE statut = 'en_attente'")->fetch_row()[0];
$admission_accepte = mysqli_query($conn, "SELECT COUNT(*) FROM admission WHERE statut = 'approuvé'")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTeam - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html,
        body {
            overflow: hidden;
            height: 100%;
            margin: 0;
        }

        main {
            margin-left: 280px;
            padding: 20px;
            height: 100vh;
        }

        .stats-card {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            border-radius: 15px;
            color: white;
            transition: transform 0.3s;
            height: 100px;
            width: 80%;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .users-icon {
            background: #e3f2fd;
            color: #2196f3;
        }

        .teachers-icon {
            background: #f0f4c3;
            color: #9e9d24;
        }

        .courses-icon {
            background: #ffcdd2;
            color: #f44336;
        }

        #roleChart,#admissionChart{
            max-height: 280px;
            max-height: 250px;
            margin: 0 auto;
        }

        .card-header {
            background: #ffffff !important;
            color: #2c3e50 !important;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        
    </style>
</head>

<body class="bg-light">
    <?php include 'aside.php'; ?>

    <main class="container-fluid">
        <div class="row g-4 justify-content-start">
            <!-- Carte Utilisateurs -->
            <div class="col-md-4 col-lg-4 ">
                <div class="stats-card p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-users stats-icon me-3"></i>
                        <div>
                            <h5 class="mb-0"><?= $users_count ?></h5>
                            <small>Utilisateurs</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carte Professeurs -->
            <div class="col-md-4 col-lg-4">
                <div class="stats-card p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chalkboard-teacher stats-icon me-3"></i>
                        <div>
                            <h5 class="mb-0"><?= $teachers_count ?></h5>
                            <small>Professeurs</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Carte Cours -->
            <div class="col-md-4 col-lg-4">
                <div class="stats-card p-4">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-book stats-icon me-3"></i>
                        <div>
                            <h5 class="mb-0"><?= $courses_count ?></h5>
                            <small>Cours</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-5 ms-5">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Répartition des utilisateurs
                    </div>
                    <div class="card-body p-3">
                        <canvas id="roleChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        Statut des admissions
                    </div>
                    <div class="card-body p-3">
                        <canvas id="admissionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <script>
        const ctx = document.getElementById('roleChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Étudiants', 'Professeurs', 'Admins'],
                datasets: [{
                    data: [
                        <?= mysqli_query($conn, "SELECT COUNT(*) FROM personne WHERE rôle='student'")->fetch_row()[0] ?>,
                        <?= $teachers_count ?>,
                        <?= mysqli_query($conn, "SELECT COUNT(*) FROM personne WHERE rôle='admin'")->fetch_row()[0] ?>
                    ],
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc']
                }]
            }
        });
        const admissionCtx = document.getElementById('admissionChart').getContext('2d');
        new Chart(admissionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Refusées', 'En attente', 'Approuvées'],
                datasets: [{
                    data: [
                        <?= $admission_refuse ?>,
                        <?= $admission_attente ?>,
                        <?= $admission_accepte ?>
                    ],
                    backgroundColor: ['#ff6384', '#ffcd56', '#4bc0c0'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Répartition des admissions'
                    }
                }
            }
        });
    </script>

</body>

</html>