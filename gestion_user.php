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
    // Validation des données
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = in_array($_POST['role'], ['student', 'teacher']) ? $_POST['role'] : 'student';

    // Requête préparée sécurisée
    $sql = "INSERT INTO personne (name, email, password, rôle) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $password, $role);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Utilisateur ajouté avec succès!";
        } else {
            $error = "Erreur: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "Erreur de préparation de la requête";
    }
}

// Récupération des utilisateurs
$users = mysqli_query($conn, "SELECT * FROM personne");
// Gestion de la suppression
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // Protection contre les injections SQL

    $sql = "DELETE FROM personne WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Utilisateur supprimé avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "Erreur de préparation de la requête";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Gestion Utilisateurs</title>
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

        .table thead {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .table td,
        .table th {
            vertical-align: middle;
            border: none;
        }

        .role-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .student-badge {
            background: #e9f5ff;
            color: #1d4ed8;
        }

        .teacher-badge {
            background: #f0fdf4;
            color: #047857;
        }
    </style>
</head>
</head>

<body class="bg-light">
    <?php include 'aside.php'; ?>

     <main class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold text-dark">
                <i class="fas fa-users-cog me-2"></i>
                Gestion des Utilisateurs
            </h2>
            <button class="btn gradient-btn" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus-circle me-2"></i>Nouvel Utilisateur
            </button>
        </div>

        <div class="user-table">
            <table class="table table-hover table-borderless">
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
                    $users = mysqli_query($conn, "SELECT name, email, rôle, id FROM personne");
                    while ($user = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="role-badge <?= $user['rôle'] === 'student' ? 'student-badge' : 'teacher-badge' ?>">
                                    <?= ucfirst($user['rôle']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="confirm_delete(<?= $user['id'] ?>)" class="btn btn-sm btn-outline-danger ms-2">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Ajout Utilisateur -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvel Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Nom complet</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Mot de passe</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Rôle</label>
                            <select name="role" class="form-select" required>
                                <option value="student">student</option>
                                <option value="teacher">teacher</option>
                            </select>
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
            if (confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) {
                window.location.href = '?action=delete&id=' + id;
            }
        }
    </script>

</body>

</html>