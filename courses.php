<?php
session_start();
if (!isset($_SESSION['name'])) { 
    header("Location: connect.php");
    exit();
}
elseif($_SESSION['rôle'] == "admin" || $_SESSION['rôle'] == "student"){
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

// Traitement du formulaire d'ajout/modification de cours
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = mysqli_real_escape_string($conn, $_POST['titre']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $enseignant_id = intval($_POST['enseignant_id']);
    
    // Gestion de l'upload d'image
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/cours/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image = $target_file;
        }
    }
    
    // Vérification si c'est une modification ou un nouvel ajout
    if (isset($_POST['cours_id'])) {
        $cours_id = intval($_POST['cours_id']);
        
        // Si nouvelle image fournie, on met à jour, sinon on garde l'ancienne
        if ($image) {
            $sql = "UPDATE cours SET titre=?, description=?, enseignant_id=?, image=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssisi", $titre, $description, $enseignant_id, $image, $cours_id);
            }
        } else {
            $sql = "UPDATE cours SET titre=?, description=?, enseignant_id=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssii", $titre, $description, $enseignant_id, $cours_id);
            }
        }
        
        if ($stmt) {
            if (mysqli_stmt_execute($stmt)) {
                $success = "Cours modifié avec succès!";
            } else {
                $error = "Erreur lors de la modification: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $sql = "INSERT INTO cours (titre, description, enseignant_id, image) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssis", $titre, $description, $enseignant_id, $image);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Cours ajouté avec succès!";
            } else {
                $error = "Erreur lors de l'ajout: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Gestion de la suppression de cours
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']); // Protection contre les injections SQL

    // Supprimer d'abord les inscriptions liées au cours
    $sql_delete_inscriptions = "DELETE FROM inscription_cours WHERE cours_id = ?";
    $stmt = mysqli_prepare($conn, $sql_delete_inscriptions);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Puis supprimer le cours
    $sql_delete_cours = "DELETE FROM cours WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql_delete_cours);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Cours supprimé avec succès!";
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

// Récupération des cours avec les noms des enseignants
$cours = mysqli_query($conn, "
    SELECT c.id, c.titre, c.description, c.image, p.name AS enseignant, c.enseignant_id
    FROM cours c 
    LEFT JOIN personne p ON c.enseignant_id = p.id
");

// Récupération des enseignants pour le formulaire
$enseignants = mysqli_query($conn, "SELECT id, name FROM personne WHERE rôle = 'teacher'");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Gestion des Cours</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #a855f7;
            --secondary-color: #ec4899;
            --accent-color: #ff6b9c;
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
            background: rgba(168, 85, 247, 0.1);
            color: var(--primary-color);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Boutons */
        .gradient-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white !important;
            padding: 10px 25px;
            border-radius: 8px;
            transition: transform 0.2s;
            font-weight: 500;
        }

        .gradient-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(124, 58, 237, 0.3);
        }

        /* Cartes de cours */
        .cours-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .cours-card {
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

        .cours-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(124, 58, 237, 0.15);
        }

        .cours-image {
            height: 180px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .cours-header {
            padding: 15px;
            background: white;
        }

        .cours-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #1e293b;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 3rem;
        }

        .cours-body {
            padding: 0 15px 15px;
            flex-grow: 1;
        }

        .cours-description {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 15px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 70px;
        }

        .cours-footer {
            padding: 15px;
            background-color: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .badge-enseignant {
            display: inline-flex;
            align-items: center;
            padding: 5px 12px;
            border-radius: 20px;
            background-color: rgba(124, 58, 237, 0.1);
            color: var(--primary-color);
            font-size: 0.85rem;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

        .cours-image-placeholder {
            background: linear-gradient(135deg, #a78bfa, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            height: 100%;
        }

        /* Modals */
        .modal-content {
            border-radius: 16px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 20px;
        }

        .modal-title {
            font-weight: 600;
        }

        .image-preview {
            width: 100%;
            height: 200px;
            border-radius: 12px;
            background-size: cover;
            background-position: center;
            margin-bottom: 20px;
            border: 2px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 0.9rem;
            overflow: hidden;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 200px;
            object-fit: cover;
        }

        .no-image {
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 260px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
            
            .cours-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            }
            
            .mobile-menu-btn {
                display: block !important;
            }
        }

        @media (max-width: 768px) {
            .cours-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
        
        /* Bouton menu mobile */
        .mobile-menu-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1100;
            background: var(--primary-color);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
        }
        
        /* Overlay pour menu mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1050;
            display: none;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
    </style>
</head>
<body>
    
    <?php include 'prof_aside.php'; ?>

    <!-- Contenu principal -->
    <div class="main-content">
        <div class="dashboard-header">
            <div>
                <h1><i class="fas fa-book me-2"></i>Gestion des Cours</h1>
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
                <button class="btn gradient-btn" data-bs-toggle="modal" data-bs-target="#addCoursModal">
                    <i class="fas fa-plus-circle me-2"></i>Nouveau Cours
                </button>
            </div>
            
            <div class="d-flex align-items-center">
                <div class="input-group" style="width: 250px;">
                    <input type="text" class="form-control" placeholder="Rechercher un cours...">
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

        <!-- Grille des cours -->
        <div class="cours-grid">
            <?php while ($cour = mysqli_fetch_assoc($cours)): ?>
                <div class="cours-card">
                    <div class="cours-image" 
                         style="background-image: url('<?= $cour['image'] ? $cour['image'] : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>' ?>')">
                        <?php if (!$cour['image']): ?>
                            <div class="cours-image-placeholder w-100 h-100">
                                <i class="fas fa-book"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="cours-header">
                        <h3 class="cours-title"><?= htmlspecialchars($cour['titre']) ?></h3>
                    </div>
                    <div class="cours-body">
                        <p class="cours-description"><?= htmlspecialchars($cour['description']) ?></p>
                        <div class="d-flex align-items-center">
                            <span class="badge-enseignant">
                                <i class="fas fa-chalkboard-teacher me-1"></i>
                                <?= htmlspecialchars($cour['enseignant'] ?? 'Non assigné') ?>
                            </span>
                        </div>
                    </div>
                    <div class="cours-footer">
                        <button class="btn btn-sm btn-outline-info" 
                                data-bs-toggle="modal" 
                                data-bs-target="#viewCoursModal"
                                data-id="<?= $cour['id'] ?>"
                                data-titre="<?= htmlspecialchars($cour['titre']) ?>"
                                data-description="<?= htmlspecialchars($cour['description']) ?>"
                                data-enseignant="<?= $cour['enseignant'] ?? '' ?>"
                                data-image="<?= $cour['image'] ?>">
                            <i class="fas fa-eye me-1"></i> Détails
                        </button>
                        <div>
                            <button class="btn btn-sm btn-outline-primary action-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editCoursModal"
                                    data-id="<?= $cour['id'] ?>"
                                    data-titre="<?= htmlspecialchars($cour['titre']) ?>"
                                    data-description="<?= htmlspecialchars($cour['description']) ?>"
                                    data-enseignant="<?= $cour['enseignant_id'] ?? '' ?>"
                                    data-image="<?= $cour['image'] ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirm_delete(<?= $cour['id'] ?>)" 
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
    <!-- Modal Ajout Cours -->
    <div class="modal fade" id="addCoursModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un nouveau cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Titre du cours</label>
                                    <input type="text" name="titre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Enseignant</label>
                                    <select name="enseignant_id" class="form-select" required>
                                        <option value="">Sélectionner un enseignant</option>
                                        <?php 
                                        mysqli_data_seek($enseignants, 0);
                                        while ($enseignant = mysqli_fetch_assoc($enseignants)): ?>
                                            <option value="<?= $enseignant['id'] ?>"><?= htmlspecialchars($enseignant['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Image du cours</label>
                                    <div class="image-preview no-image" id="addImagePreview">
                                        <span>Aucune image sélectionnée</span>
                                    </div>
                                    <input type="file" name="image" class="form-control" id="addImageInput" accept="image/*">
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    L'image doit être au format JPG, PNG ou GIF. Taille maximale : 2MB.
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

    <!-- Modal Modification Cours -->
    <div class="modal fade" id="editCoursModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifier le cours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="cours_id" id="editCoursId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Titre du cours</label>
                                    <input type="text" name="titre" id="editTitre" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" id="editDescription" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Enseignant</label>
                                    <select name="enseignant_id" id="editEnseignant" class="form-select" required>
                                        <option value="">Sélectionner un enseignant</option>
                                        <?php 
                                        mysqli_data_seek($enseignants, 0);
                                        while ($enseignant = mysqli_fetch_assoc($enseignants)): ?>
                                            <option value="<?= $enseignant['id'] ?>"><?= htmlspecialchars($enseignant['name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Image du cours</label>
                                    <div class="image-preview" id="editImagePreview">
                                        <span>Aucune image sélectionnée</span>
                                    </div>
                                    <input type="file" name="image" class="form-control" id="editImageInput" accept="image/*">
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="removeImageCheckbox" name="remove_image">
                                    <label class="form-check-label" for="removeImageCheckbox">
                                        Supprimer l'image actuelle
                                    </label>
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

    <!-- Modal Vue Cours -->
    <div class="modal fade" id="viewCoursModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTitre"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="image-preview" id="viewImagePreview">
                                <span>Aucune image</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6>Description</h6>
                                <p id="viewDescription"></p>
                            </div>
                            <div>
                                <h6>Enseignant</h6>
                                <p id="viewEnseignant" class="badge-enseignant d-inline-block"></p>
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
        // Gestion du menu mobile
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
        
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });

        // Confirmation de suppression
        function confirm_delete(id) {
            if (confirm("Voulez-vous vraiment supprimer ce cours ? Toutes les inscriptions associées seront également supprimées.")) {
                window.location.href = '?action=delete&id=' + id;
            }
        }
        
        // Initialisation du modal d'édition
        const editCoursModal = document.getElementById('editCoursModal');
        editCoursModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const titre = button.getAttribute('data-titre');
            const description = button.getAttribute('data-description');
            const enseignant = button.getAttribute('data-enseignant');
            const image = button.getAttribute('data-image');
            
            document.getElementById('editCoursId').value = id;
            document.getElementById('editTitre').value = titre;
            document.getElementById('editDescription').value = description;
            document.getElementById('editEnseignant').value = enseignant;
            
            const preview = document.getElementById('editImagePreview');
            if (image) {
                preview.innerHTML = `<img src="${image}" alt="Image du cours">`;
                preview.classList.remove('no-image');
            } else {
                preview.innerHTML = '<span>Aucune image</span>';
                preview.classList.add('no-image');
            }
            
            // Réinitialiser la case à cocher
            document.getElementById('removeImageCheckbox').checked = false;
        });
        
        // Initialisation du modal de vue
        const viewCoursModal = document.getElementById('viewCoursModal');
        viewCoursModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const titre = button.getAttribute('data-titre');
            const description = button.getAttribute('data-description');
            const enseignant = button.getAttribute('data-enseignant');
            const image = button.getAttribute('data-image');
            
            document.getElementById('viewTitre').textContent = titre;
            document.getElementById('viewDescription').textContent = description;
            document.getElementById('viewEnseignant').textContent = enseignant || 'Non assigné';
            
            const preview = document.getElementById('viewImagePreview');
            if (image) {
                preview.innerHTML = `<img src="${image}" alt="Image du cours">`;
                preview.classList.remove('no-image');
            } else {
                preview.innerHTML = '<span>Aucune image</span>';
                preview.classList.add('no-image');
            }
        });
        
        // Gestion de l'aperçu d'image pour l'ajout
        const addImageInput = document.getElementById('addImageInput');
        const addImagePreview = document.getElementById('addImagePreview');
        
        addImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    addImagePreview.innerHTML = `<img src="${e.target.result}" alt="Prévisualisation">`;
                    addImagePreview.classList.remove('no-image');
                }
                reader.readAsDataURL(file);
            } else {
                addImagePreview.innerHTML = '<span>Aucune image sélectionnée</span>';
                addImagePreview.classList.add('no-image');
            }
        });
        
        // Gestion de l'aperçu d'image pour l'édition
        const editImageInput = document.getElementById('editImageInput');
        const editImagePreview = document.getElementById('editImagePreview');
        
        editImageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    editImagePreview.innerHTML = `<img src="${e.target.result}" alt="Prévisualisation">`;
                    editImagePreview.classList.remove('no-image');
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Gestion de la suppression d'image
        const removeImageCheckbox = document.getElementById('removeImageCheckbox');
        removeImageCheckbox.addEventListener('change', function() {
            if (this.checked) {
                editImagePreview.innerHTML = '<span>Image sera supprimée</span>';
                editImagePreview.classList.add('no-image');
            } else {
                const image = document.querySelector('#editCoursModal .btn').getAttribute('data-image');
                if (image) {
                    editImagePreview.innerHTML = `<img src="${image}" alt="Image du cours">`;
                    editImagePreview.classList.remove('no-image');
                } else {
                    editImagePreview.innerHTML = '<span>Aucune image</span>';
                    editImagePreview.classList.add('no-image');
                }
            }
        });
    </script>
</body>
</html>