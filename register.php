<?php
session_start();
error_reporting(E_ALL); // Active les erreurs pour le débogage
ini_set('display_errors', 1);

$host = "localhost";
$user = "root"; 
$password = ""; // Vérifiez le mot de passe de votre MySQL
$db = "schoolproject"; // Nom exact de la base de données

// Établir la connexion
$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // Récupération du rôle

    // Vérification de l'email existant
    $check_query = "SELECT * FROM personne WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        $_SESSION['signup_error'] = "Cet email est déjà utilisé !";
        header("Location: connect.php");
        exit();
    }

    // Hachage du mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insertion des données
    $insert_query = "INSERT INTO personne (name, email, password, rôle) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $hashed_password, $role);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['signup_success'] = "Inscription réussie ! Vous pouvez vous connecter.";
    } else {
        $_SESSION['signup_error'] = "Erreur : " . mysqli_error($conn);
    }

    header("Location: connect.php");
    exit();
}
?>