<?php
session_start();

// Vérification de sécurité
if (!isset($_SESSION['name']) || $_SESSION['rôle'] !== 'admin') {
    header("Location: connect.php");
    exit();
}

// Configuration BDD
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";
$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'teacher';

    $sql = "INSERT INTO personne (name, email, password, rôle) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $password, $role);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Professeur ajouté avec succès !";
            header("Location: gestion_prof.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Erreur de préparation de la requête";
    }
}

// Récupération des professeurs
$teachers = mysqli_query($conn, "SELECT id, name, email, rôle FROM personne WHERE rôle = 'teacher'");

// Gestion suppression
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Vérifier si le professeur est référencé dans la table cours
    $check_cours = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM cours WHERE enseignant_id = ?");
    mysqli_stmt_bind_param($check_cours, "i", $id);
    mysqli_stmt_execute($check_cours);
    $result = mysqli_stmt_get_result($check_cours);
    $row = mysqli_fetch_assoc($result);
    
    if ($row['count'] > 0) {
        // Si le prof est référencé, mettre à jour les cours
        $update_cours = mysqli_prepare($conn, "UPDATE cours SET enseignant_id = NULL WHERE enseignant_id = ?");
        mysqli_stmt_bind_param($update_cours, "i", $id);
        mysqli_stmt_execute($update_cours);
    }
    
    // Maintenant supprimer le professeur
    $sql = "DELETE FROM personne WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Professeur supprimé avec succès !";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression: " . mysqli_error($conn);
        }
    }
    
    header("Location: gestion_prof.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion Professeurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #7c3aed;
            --secondary-color: #6d28d9;
        }

        .gradient-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white !important;
            padding: 10px 25px;
            border-radius: 8px;
            transition: transform 0.2s;
        }

        .gradient-btn:hover {
            transform: translateY(-2px);
        }

        .user-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table th,
        .table td {
            padding: 0.75rem 1.5rem;
            border: none !important;
        }

        .table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .table thead tr th:first-child {
            border-top-left-radius: 10px;
        }

        .table thead tr th:last-child {
            border-top-right-radius: 10px;
        }

        .table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 10px;
        }

        .table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 10px;
        }

        .teacher-badge {
            background: #f0fdf4;
            color: #047857;
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 12px;
        }

        .teacher-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }

        .teacher-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.15);
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'aside.php'; ?>

    <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold text-dark">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Gestion des Professeurs
            </h2>
            <button class="btn gradient-btn" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                <i class="fas fa-plus-circle me-2"></i>Nouveau Professeur
            </button>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>


        <!-- Affichage sous forme de tableau -->
        <div class="user-table mb-4">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($teachers, 0); // Réinitialiser le pointeur
                    while ($teacher = mysqli_fetch_assoc($teachers)): 
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($teacher['name']) ?></td>
                        <td><?= htmlspecialchars($teacher['email']) ?></td>
                        <td>
                            <span class="teacher-badge">
                                <?= ucfirst($teacher['rôle']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_prof.php?id=<?= $teacher['id'] ?>" class="btn btn-sm btn-outline-primary action-btn">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirm_delete(<?= $teacher['id'] ?>)" 
                                    class="btn btn-sm btn-outline-danger action-btn ms-2">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Ajout -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau Professeur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordInput" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn gradient-btn">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirm_delete(id) {
            if (confirm("Voulez-vous vraiment supprimer ce professeur ? Cette action est irréversible.")) {
                window.location.href = '?action=delete&id=' + id;
            }
        }
        
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('passwordInput');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            this.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        });
    </script>
</body>
</html>