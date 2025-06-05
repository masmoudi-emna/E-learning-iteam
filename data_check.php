<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$db = "schoolproject"; 
$conn = mysqli_connect($host, $user, $password, $db);

if (mysqli_connect_errno()) {
    die("Erreur de connexion: " . mysqli_connect_error());
}

if (isset($_POST['soumettre'])) {
    // Nettoyage et validation des données
    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? ''); 

    // Vérification des champs obligatoires
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Le nom complet est obligatoire";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email est invalide ou manquant";
    }
    
    if (empty($phone) || strlen($phone) < 8) {
        $errors[] = "Le numéro de téléphone est invalide";
    }

    if (count($errors) === 0) {
        // Vérification de l'unicité de l'email
        $check_sql = "SELECT id FROM admission WHERE email = ?";
        $stmt_check = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $errors[] = "Cet email est déjà utilisé pour une demande en cours";
        }
        mysqli_stmt_close($stmt_check);
    }

    if (count($errors) === 0) {
        // Requête préparée
        $sql = "INSERT INTO admission (name, email, phone) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $phone);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Votre demande d'admission a été envoyée avec succès.";
            } else {
                $_SESSION['error'] = "Erreur d'enregistrement : " . mysqli_stmt_error($stmt);
            }
            
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error'] = "Erreur de préparation : " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }

    // Redirection avec gestion des messages
    header("Location: index.php");
    exit();
}

mysqli_close($conn);
?>