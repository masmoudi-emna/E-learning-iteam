<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: connect.php");
    exit();
} elseif ($_SESSION['rôle'] == "admin" || $_SESSION['rôle'] == "student") {
    header("Location: connect.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject";
$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Traitement du formulaire d'ajout/modification de quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $date_ouverture = mysqli_real_escape_string($conn, $_POST['date_ouverture']);
    $date_fermeture = mysqli_real_escape_string($conn, $_POST['date_fermeture']);
    $duree = intval($_POST['duree']);
    $tentatives_max = intval($_POST['tentatives_max']);
    $afficher_reponses = isset($_POST['afficher_reponses']) ? 1 : 0;
    $mode_aleatoire = isset($_POST['mode_aleatoire']) ? 1 : 0;
    $points_max = intval($_POST['points_max']);
    $etat = mysqli_real_escape_string($conn, $_POST['etat']);
    $cours_id = intval($_POST['cours_id']);
    
    // Récupération de l'ID enseignant depuis la session
    $enseignant_id = $_SESSION['id'];

    // Vérification si c'est une modification ou un nouvel ajout
    if (isset($_POST['quiz_id'])) {
        $quiz_id = intval($_POST['quiz_id']);

        $sql = "UPDATE quiz SET 
        titre = ?, 
        date_ouverture = ?, 
        date_fermeture = ?, 
        duree = ?, 
        tentatives_max = ?, 
        afficher_reponses = ?, 
        mode_aleatoire = ?, 
        points_max = ?, 
        etat = ?,
        cours_id = ?
        WHERE id = ?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssiiiiiisi",
                $titre,
                $date_ouverture,
                $date_fermeture,
                $duree,
                $tentatives_max,
                $afficher_reponses,
                $mode_aleatoire,
                $points_max,
                $etat,
                $cours_id,
                $quiz_id
            );

            if (mysqli_stmt_execute($stmt)) {
                $success = "Quiz modifié avec succès!";
            } else {
                $error = "Erreur lors de la modification: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $sql = "INSERT INTO quiz (titre, date_ouverture, date_fermeture, duree, tentatives_max, 
        afficher_reponses, mode_aleatoire, points_max, etat, cours_id, enseignant_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "sssiiiiiisi",
                $titre,
                $date_ouverture,
                $date_fermeture,
                $duree,
                $tentatives_max,
                $afficher_reponses,
                $mode_aleatoire,
                $points_max,
                $etat,
                $cours_id,
                $enseignant_id
            );
            if (mysqli_stmt_execute($stmt)) {
                $success = "Quiz ajouté avec succès!";
            } else {
                $error = "Erreur lors de l'ajout: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}


// Gestion de la suppression de quiz
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // Protection contre les injections SQL

    // Supprimer le quiz
    $sql_delete_quiz = "DELETE FROM quiz WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql_delete_quiz);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Quiz supprimé avec succès!";
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

// Récupération des quiz avec les noms des cours
$quiz = mysqli_query($conn, "
    SELECT q.id, q.titre, q.date_ouverture, q.date_fermeture, q.duree, q.tentatives_max, 
           q.afficher_reponses, q.mode_aleatoire, q.points_max, q.etat,
           c.titre AS cours_titre
    FROM quiz q 
    LEFT JOIN cours c ON q.cours_id = c.id
");

// Récupération des cours pour le formulaire
$cours = mysqli_query($conn, "SELECT id, titre FROM cours");
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Gestion des Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #6366f1;
            --accent-color: #8b5cf6;
            --text-light: #ffffff;
            --background-light: #f8f9fc;
            --card-bg: #ffffff;
            --card-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        body {
            background-color: var(--background-light);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            overflow-x: hidden;
        }

        /* Contenu principal */
        .main-content {
            flex: 1;
            margin-left: 230px;
            padding: 30px;
            min-height: 100vh;
            box-sizing: border-box;
            width: calc(100% - 230px);
        }

        /* En-tête */
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
            font-size: 1.8rem;
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
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary-color);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Boutons */
        .gradient-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border: none;
            color: white !important;
            padding: 10px 25px;
            border-radius: 8px;
            transition: transform 0.2s;
            font-weight: 500;
        }

        .gradient-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

        /* Cartes de quiz */
        .quiz-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .quiz-card {
            transition: all 0.3s ease;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            height: 100%;
            display: flex;
            flex-direction: column;
            background: var(--card-bg);
            position: relative;
        }

        .quiz-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.15);
        }

        .quiz-header {
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
        }

        .quiz-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: white;
        }

        .quiz-body {
            padding: 15px;
            flex-grow: 1;
        }

        .quiz-info {
            margin-bottom: 10px;
            font-size: 0.9rem;
            color: #4b5563;
        }

        .quiz-info strong {
            color: #1e293b;
            margin-right: 5px;
        }

        .quiz-footer {
            padding: 15px;
            background-color: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .quiz-status {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .status-planned {
            background-color: rgba(249, 115, 22, 0.1);
            color: #f97316;
        }

        .status-active {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .status-closed {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
            background-color: #f1f5f9;
            color: #64748b;
        }

        .action-btn:hover {
            transform: scale(1.1);
            background-color: #e2e8f0;
        }

        /* Modals */
        .modal-content {
            border-radius: 16px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 15px 20px;
        }

        .modal-title {
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
            color: #374151;
        }

        .form-check-label {
            color: #4b5563;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            .quiz-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .quiz-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>

<body>

    <?php include 'prof_aside.php'; ?>

    <!-- Contenu principal -->
    <div class="main-content">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-question-circle me-2"></i>Gestion des Quiz</h1>
            </div>

            <div class="user-info">
                <div class="user-avatar"><?= substr($_SESSION['name'], 0, 1) ?></div>
                <div class="d-flex flex-column">
                    <span class="user-name"><?= $_SESSION['name'] ?></span>
                    <span class="user-role">Enseignant</span>
                </div>
            </div>
        </div>

        <!-- Contrôles -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <button class="btn gradient-btn" data-bs-toggle="modal" data-bs-target="#addQuizModal">
                    <i class="fas fa-plus-circle me-2"></i>Nouveau Quiz
                </button>
            </div>

            <div class="d-flex align-items-center">
                <div class="input-group" style="width: 250px;">
                    <input type="text" class="form-control" placeholder="Rechercher un quiz...">
                    <button class="btn btn-primary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Messages d'alerte -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Grille des quiz -->
        <div class="quiz-grid">
            <?php while ($q = mysqli_fetch_assoc($quiz)):
                // Déterminer le statut du quiz
                $now = time();
                $open = strtotime($q['date_ouverture']);
                $close = strtotime($q['date_fermeture']);

                if ($now < $open) {
                    $status = 'planned';
                    $status_text = 'Planifié';
                    $status_class = 'status-planned';
                } elseif ($now >= $open && $now <= $close) {
                    $status = 'active';
                    $status_text = 'Actif';
                    $status_class = 'status-active';
                } else {
                    $status = 'closed';
                    $status_text = 'Clôturé';
                    $status_class = 'status-closed';
                }
            ?>
                <div class="quiz-card">
                    <div class="quiz-header">
                        <h3 class="quiz-title"><?= htmlspecialchars($q['titre']) ?></h3>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark">
                                <i class="fas fa-book me-1"></i>
                                <?= htmlspecialchars($q['cours_titre'] ?? 'Sans cours') ?>
                            </span>
                            <span class="<?= $status_class ?> quiz-status"><?= $status_text ?></span>
                        </div>
                    </div>
                    <div class="quiz-body">
                        <div class="quiz-info">
                            <strong><i class="fas fa-calendar-alt me-1"></i>Ouverture:</strong>
                            <?= date('d/m/Y H:i', strtotime($q['date_ouverture'])) ?>
                        </div>
                        <div class="quiz-info">
                            <strong><i class="fas fa-calendar-times me-1"></i>Fermeture:</strong>
                            <?= date('d/m/Y H:i', strtotime($q['date_fermeture'])) ?>
                        </div>
                        <div class="quiz-info">
                            <strong><i class="fas fa-clock me-1"></i>Durée:</strong>
                            <?= $q['duree'] ?> minutes
                        </div>
                        <div class="quiz-info">
                            <strong><i class="fas fa-redo me-1"></i>Tentatives:</strong>
                            <?= $q['tentatives_max'] ?>
                        </div>
                        <div class="quiz-info">
                            <strong><i class="fas fa-star me-1"></i>Points max:</strong>
                            <?= $q['points_max'] ?>
                        </div>
                    </div>
                    <div class="quiz-footer">
                        <button class="btn btn-sm btn-outline-info"
                            data-bs-toggle="modal"
                            data-bs-target="#viewQuizModal"
                            data-id="<?= $q['id'] ?>"
                            data-titre="<?= htmlspecialchars($q['titre']) ?>"
                            data-date_ouverture="<?= $q['date_ouverture'] ?>"
                            data-date_fermeture="<?= $q['date_fermeture'] ?>"
                            data-duree="<?= $q['duree'] ?>"
                            data-tentatives_max="<?= $q['tentatives_max'] ?>"
                            data-afficher_reponses="<?= $q['afficher_reponses'] ?>"
                            data-mode_aleatoire="<?= $q['mode_aleatoire'] ?>"
                            data-points_max="<?= $q['points_max'] ?>"
                            data-etat="<?= $q['etat'] ?>"
                            data-cours="<?= $q['cours_titre'] ?? '' ?>">
                            <i class="fas fa-eye me-1"></i> Détails
                        </button>
                        <div>
                            <button class="btn btn-sm btn-outline-primary action-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#editQuizModal"
                                data-id="<?= $q['id'] ?>"
                                data-titre="<?= htmlspecialchars($q['titre']) ?>"
                                data-date_ouverture="<?= $q['date_ouverture'] ?>"
                                data-date_fermeture="<?= $q['date_fermeture'] ?>"
                                data-duree="<?= $q['duree'] ?>"
                                data-tentatives_max="<?= $q['tentatives_max'] ?>"
                                data-afficher_reponses="<?= $q['afficher_reponses'] ?>"
                                data-mode_aleatoire="<?= $q['mode_aleatoire'] ?>"
                                data-points_max="<?= $q['points_max'] ?>"
                                data-etat="<?= $q['etat'] ?>"
                                data-cours_id="<?= $q['cours_id'] ?? '' ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirm_delete(<?= $q['id'] ?>)"
                                class="btn btn-sm btn-outline-danger action-btn ms-2">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal Ajout Quiz -->
    <div class="modal fade" id="addQuizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un nouveau quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Titre du quiz</label>
                                    <input type="text" name="titre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date d'ouverture</label>
                                    <input type="datetime-local" name="date_ouverture" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date de fermeture</label>
                                    <input type="datetime-local" name="date_fermeture" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Durée (minutes)</label>
                                    <input type="number" name="duree" class="form-control" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tentatives maximales</label>
                                    <input type="number" name="tentatives_max" class="form-control" min="1" value="1" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Points maximaux</label>
                                    <input type="number" name="points_max" class="form-control" min="1" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cours associé</label>
                                    <select name="cours_id" class="form-select">
                                        <option value="">Sélectionner un cours</option>
                                        <?php
                                        mysqli_data_seek($cours, 0);
                                        while ($c = mysqli_fetch_assoc($cours)): ?>
                                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['titre']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">État</label>
                                    <select name="etat" class="form-select" required>
                                        <option value="brouillon">Brouillon</option>
                                        <option value="publié">Publié</option>
                                        <option value="archivé">Archivé</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="afficher_reponses" id="afficherReponses">
                                    <label class="form-check-label" for="afficherReponses">Afficher les réponses après soumission</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="mode_aleatoire" id="modeAleatoire">
                                    <label class="form-check-label" for="modeAleatoire">Ordre aléatoire des questions</label>
                                </div>
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

    <!-- Modal Modification Quiz -->
    <div class="modal fade" id="editQuizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="quiz_id" id="editQuizId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Titre du quiz</label>
                                    <input type="text" name="titre" id="editTitre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date d'ouverture</label>
                                    <input type="datetime-local" name="date_ouverture" id="editDateOuverture" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Date de fermeture</label>
                                    <input type="datetime-local" name="date_fermeture" id="editDateFermeture" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Durée (minutes)</label>
                                    <input type="number" name="duree" id="editDuree" class="form-control" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tentatives maximales</label>
                                    <input type="number" name="tentatives_max" id="editTentativesMax" class="form-control" min="1" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Points maximaux</label>
                                    <input type="number" name="points_max" id="editPointsMax" class="form-control" min="1" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cours associé</label>
                                    <select name="cours_id" id="editCoursId" class="form-select">
                                        <option value="">Sélectionner un cours</option>
                                        <?php
                                        mysqli_data_seek($cours, 0);
                                        while ($c = mysqli_fetch_assoc($cours)): ?>
                                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['titre']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">État</label>
                                    <select name="etat" id="editEtat" class="form-select" required>
                                        <option value="brouillon">Brouillon</option>
                                        <option value="publié">Publié</option>
                                        <option value="archivé">Archivé</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="afficher_reponses" id="editAfficherReponses">
                                    <label class="form-check-label" for="editAfficherReponses">Afficher les réponses après soumission</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" name="mode_aleatoire" id="editModeAleatoire">
                                    <label class="form-check-label" for="editModeAleatoire">Ordre aléatoire des questions</label>
                                </div>
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

    <!-- Modal Vue Quiz -->
    <div class="modal fade" id="viewQuizModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTitre"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6><i class="fas fa-calendar-alt me-2"></i>Date d'ouverture</h6>
                                <p id="viewDateOuverture"></p>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-calendar-times me-2"></i>Date de fermeture</h6>
                                <p id="viewDateFermeture"></p>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-clock me-2"></i>Durée</h6>
                                <p id="viewDuree"></p>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-redo me-2"></i>Tentatives maximales</h6>
                                <p id="viewTentativesMax"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <h6><i class="fas fa-book me-2"></i>Cours associé</h6>
                                <p id="viewCours"></p>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-star me-2"></i>Points maximaux</h6>
                                <p id="viewPointsMax"></p>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-toggle-on me-2"></i>Paramètres</h6>
                                <p id="viewAfficherReponses"></p>
                                <p id="viewModeAleatoire"></p>
                            </div>
                            <div class="mb-3">
                                <h6><i class="fas fa-info-circle me-2"></i>État</h6>
                                <p id="viewEtat"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmation de suppression
        function confirm_delete(id) {
            if (confirm("Voulez-vous vraiment supprimer ce quiz ? Toutes les données associées seront également supprimées.")) {
                window.location.href = '?action=delete&id=' + id;
            }
        }

        // Initialisation du modal d'édition
        const editQuizModal = document.getElementById('editQuizModal');
        editQuizModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            document.getElementById('editQuizId').value = button.getAttribute('data-id');
            document.getElementById('editTitre').value = button.getAttribute('data-titre');
            document.getElementById('editDateOuverture').value = formatDateTime(button.getAttribute('data-date_ouverture'));
            document.getElementById('editDateFermeture').value = formatDateTime(button.getAttribute('data-date_fermeture'));
            document.getElementById('editDuree').value = button.getAttribute('data-duree');
            document.getElementById('editTentativesMax').value = button.getAttribute('data-tentatives_max');
            document.getElementById('editPointsMax').value = button.getAttribute('data-points_max');
            document.getElementById('editEtat').value = button.getAttribute('data-etat');
            document.getElementById('editCoursId').value = button.getAttribute('data-cours_id');

            // Checkboxes
            document.getElementById('editAfficherReponses').checked = button.getAttribute('data-afficher_reponses') === '1';
            document.getElementById('editModeAleatoire').checked = button.getAttribute('data-mode_aleatoire') === '1';
        });

        // Initialisation du modal de vue
        const viewQuizModal = document.getElementById('viewQuizModal');
        viewQuizModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            document.getElementById('viewTitre').textContent = button.getAttribute('data-titre');
            document.getElementById('viewDateOuverture').textContent = formatDate(button.getAttribute('data-date_ouverture'));
            document.getElementById('viewDateFermeture').textContent = formatDate(button.getAttribute('data-date_fermeture'));
            document.getElementById('viewDuree').textContent = button.getAttribute('data-duree') + ' minutes';
            document.getElementById('viewTentativesMax').textContent = button.getAttribute('data-tentatives_max');
            document.getElementById('viewPointsMax').textContent = button.getAttribute('data-points_max');
            document.getElementById('viewCours').textContent = button.getAttribute('data-cours') || 'Aucun cours associé';
            document.getElementById('viewEtat').textContent = button.getAttribute('data-etat');

            // Paramètres
            document.getElementById('viewAfficherReponses').textContent =
                'Afficher les réponses: ' + (button.getAttribute('data-afficher_reponses') === '1' ? 'Oui' : 'Non');

            document.getElementById('viewModeAleatoire').textContent =
                'Ordre aléatoire: ' + (button.getAttribute('data-mode_aleatoire') === '1' ? 'Oui' : 'Non');
        });

        // Formater la date pour l'affichage
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Formater la date pour l'input datetime-local
        function formatDateTime(dateString) {
            const date = new Date(dateString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    </script>
</body>

</html>