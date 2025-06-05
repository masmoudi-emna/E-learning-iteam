<?php
session_start();

// Vérification des permissions admin
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
    die("Erreur de connexion : " . mysqli_connect_error());
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['admission_id'])) {
        $admission_id = intval($_POST['admission_id']);

        if ($_POST['action'] === 'accept') {
    // Récupération des données
    $sql = "SELECT * FROM admission WHERE id = $admission_id";
    $result = mysqli_query($conn, $sql);
    $admission = mysqli_fetch_assoc($result);

    // Générer un mot de passe temporaire (ex: "temp123") et le hacher
    $temp_password = password_hash('temp123', PASSWORD_DEFAULT);

    // Requête corrigée avec toutes les colonnes obligatoires
    $sql = "INSERT INTO personne (name, email, password, rôle)
    VALUES (
        '" . mysqli_real_escape_string($conn, $admission['name']) . "',
        '" . mysqli_real_escape_string($conn, $admission['email']) . "',
        '" . $temp_password . "',
        'student' -- Rôle par défaut
    )";

    if (mysqli_query($conn, $sql)) {
        mysqli_query($conn, "UPDATE admission SET statut = 'approuvé' WHERE id = $admission_id");
        $_SESSION['message'] = "Admission approuvée avec succès !";
    } else {
        $_SESSION['error'] = "Erreur : " . mysqli_error($conn);
    }
        } elseif ($_POST['action'] === 'reject') {
            $sql = "UPDATE admission SET statut = 'refusé' WHERE id = $admission_id";
            if (mysqli_query($conn, $sql)) {
                $_SESSION['message'] = "Admission refusée avec succès !";
            } else {
                $_SESSION['error'] = "Erreur : " . mysqli_error($conn);
            }
        }

        header("Location: gestion_admission.php");
        exit();
    }
}

// Récupération des admissions en attente
$sql = "SELECT * FROM admission WHERE statut = 'en_attente'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Erreur : " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Admissions - iTeam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7c3aed;
            --header-color: #7c3aed;
            /* Couleur unie pour le header */
            --success-color: #10b981;
            --danger-color: #ef4444;
            --background-gradient: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);

        }


        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background-gradient);
            min-height: 100vh;
        }

        .data-table {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }


        th {
            background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
            color: white !important;
            font-weight: 600;
            padding: 10px !important;
            font-size: 0.95rem;
            border: none !important;
            border-bottom: 0 !important;


        }

        tr {
            transition: all 0.2s ease;
            border: none !important;
        }

        td {
            border: none !important;
            vertical-align: middle !important;
        }

        .btn-action {
            border-radius: 0.75rem;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .btn-accept {
            background-color: var(--success-color) !important;
            color: white !important;
        }

        .btn-reject {
            background-color: var(--danger-color) !important;
            color: white !important;
        }

        .btn-action:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }

        .header-title {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.75rem;
        }

        .badge-count {
            background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);
            padding: 0.5rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
        }

        .table-borderless> :not(:first-child) {
            border-top: 0;
        }
    </style>
</head>

<body class="bg-light">
    <?php include 'aside.php'; ?>
    <main class="container mt-3 py-4">
        <!-- Messages d'alerte -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="header-title">
                <i class="fas fa-user-graduate me-2" style="color: var(--primary-color);"></i>
                Gestion des Admissions
            </h1>
            <span class="badge-count text-white">
                <i class="fas fa-inbox me-2"></i>
                <?= mysqli_num_rows($result) ?> Demandes
            </span>
        </div>

        <div class="data-table">
            <table class="table align-middle table-borderless">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="position-relative">
                            <td class="fw-medium text-gray-800"><?= htmlspecialchars($row['name']) ?></td>
                            <td class="fw-medium text-gray-800"><?= htmlspecialchars($row['email']) ?></td>
                            <td class="fw-medium text-gray-800"><?= htmlspecialchars($row['phone']) ?></td>
                            <td>
                                <!-- Formulaire pour l'acceptation -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="admission_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-action btn-accept">
                                        <i class="fas fa-check-circle me-1"></i>Accepter
                                    </button>
                                </form>

                                <!-- Formulaire pour le rejet -->
                                <form method="POST" class="d-inline ms-2">
                                    <input type="hidden" name="admission_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-action btn-reject">
                                        <i class="fas fa-times-circle me-1"></i>Rejeter
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>