<?php
error_reporting(0);
session_start();

if (isset($_SESSION['message'])) {
    $message = addslashes($_SESSION['message']); // sécurise le message pour JS

    echo "<script type='text/javascript'>
        alert('$message');
    </script>";

    unset($_SESSION['message']); // On supprime le message après l’avoir affiché
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTeam LearnHub - Plateforme e-learning</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./index.css">

</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-graduation-cap me-2"></i>
                <span class="gradient-text">E-Learning</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item mx-2">
                        <a class="nav-link hover-underline" href="#courses">Cours</a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link hover-underline" href="#features">Fonctionnalités</a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link hover-underline" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-gradient" href="connect.php">Se connecter</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Hero Section -->
    <section class="hero-section ">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Apprenez sans limites</h1>
            <p class="lead mb-4">Maîtrisez les compétences de demain avec nos cours en ligne interactifs</p>
            <div class="input-group mb-3 w-75 mx-auto">
                <input type="text" class="form-control" placeholder="Que souhaitez-vous apprendre aujourd'hui ?">
                <button class="btn btn-search">Rechercher</button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="container">
            <div class="section-header text-center">
                <h2 class="title-animation">
                    <span class="title-line">Prêt·e à transformer votre avenir ?</span>
                    <span class="subtitle-line">Voici pourquoi nous choisir</span>
                </h2>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Feature Card 1 -->
                <div class="col-lg-4 col-md-6">
                    <article class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h3>Apprentissage adaptatif</h3>
                        <p class="description">Des parcours sur-mesure évoluant avec vos progrès</p>
                        <ul class="feature-list">
                            <li>Analyse de niveau initial</li>
                            <li>Recommandations intelligentes</li>
                            <li>Suivi de progression</li>
                        </ul>
                    </article>
                </div>

                <!-- Feature Card 2 -->
                <div class="col-lg-4 col-md-6">
                    <article class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-chalkboard-user"></i>
                        </div>
                        <h3>Cours en direct</h3>
                        <p class="description">Immersion interactive avec nos experts</p>
                        <ul class="feature-list">
                            <li>Sessions quotidiennes</li>
                            <li>Classes virtuelles</li>
                            <li>Enregistrements disponibles</li>
                        </ul>
                    </article>
                </div>

                <!-- Feature Card 3 -->
                <div class="col-lg-4 col-md-6">
                    <article class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3>Certifications</h3>
                        <p class="description">Reconnaissance de vos compétences</p>
                        <ul class="feature-list">
                            <li>Diplômes numériques</li>
                            <li>Partage LinkedIn</li>
                            <li>Accréditations internationales</li>
                        </ul>
                    </article>
                </div>
            </div>
        </div>
    </section>
    <!-- Popular Courses -->
    <section id="courses" class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Cours populaires</h2>
            <div class="row g-4">
                <!-- Course cards will be dynamically populated via JS -->
                <div id="coursesContainer" class="row"></div>
            </div>
        </div>
    </section>

    <section id="admission" class="py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="admission-card shadow-lg">
                        <div class="form-header text-center text-white py-4">
                            <h2 class="mb-3"><i class="fas fa-user-graduate me-2"></i>Demande d'Admission</h2>
                            <p class="mb-0">Rejoignez notre communauté d'apprenants dès aujourd'hui</p>
                        </div>
                        <form class="p-4 p-md-5" method="POST" action="data_check.php">
                            <div class="mb-5">
                                <h4 class="section-title"><i class="fas fa-user-circle"></i><span class="ms-3">Informations Personnelles</span></h4>

                                <!-- Nom Complet -->
                                <div class="row justify-content-center mb-4">
                                    <div class="col-12 col-md-8">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="fullName" name="name" required>
                                            <label for="fullName">Nom Complet</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="row justify-content-center mb-4">
                                    <div class="col-12 col-md-8">
                                        <div class="form-floating">
                                            <input type="email" class="form-control" id="email" name="email" required>
                                            <label for="email">Adresse Email</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Téléphone -->
                                <div class="row justify-content-center mb-4">
                                    <div class="col-12 col-md-8">
                                        <small class="text-muted mb-2 d-block">
                                            <i class="fas fa-info-circle me-2"></i>Format recommandé : +33 6 12 34 56 78
                                        </small>
                                        <div class="form-floating">
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                required>
                                            <label for="phone">Téléphone</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-lg submit-btn px-5 py-3" name="soumettre">
                                    <i class="fas fa-paper-plane me-2"></i>Soumettre la Demande
                                </button>
                            </div>
                        </form>


                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="container py-5">
            <div class="row g-5">
                <!-- Colonne À propos -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-about">
                        <a href="#" class="footer-brand">
                            <i class="fas fa-graduation-cap fa-2x"></i>
                            <span class="ms-2">E-Learn Pro</span>
                        </a>
                        <p class="mt-3">Transformez votre apprentissage avec nos cours en ligne interactifs et nos experts dédiés.</p>
                        <div class="social-links mt-4">
                            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Colonne Liens rapides -->
                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Navigation</h5>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Cours</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Certifications</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>À propos</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Contact</a></li>
                    </ul>
                </div>

                <!-- Colonne Newsletter -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Newsletter</h5>
                    <p class="newsletter-text">Abonnez-vous pour recevoir les dernières actualités</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Votre email">
                            <button class="btn btn-newsletter" type="button">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Colonne Contact -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Contact</h5>
                    <ul class="footer-contact">
                        <li><i class="fas fa-envelope me-2"></i>contact@elearnpro.com</li>
                        <li><i class="fas fa-phone me-2"></i>+33 1 23 45 67 89</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i>Paris, France</li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="footer-bottom mt-5 pt-4 border-top">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0">&copy; 2025 E-Learn Pro. Tous droits réservés.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <a href="#" class="footer-link">Conditions d'utilisation</a>
                        <a href="#" class="footer-link mx-3">Politique de confidentialité</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="./main.js"></script>

</body>

</html>