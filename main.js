// Gestion du scroll
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    const scrollPosition = window.scrollY;
    
    if (scrollPosition > 100) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

const courses = [
    {
        title: "IA & Machine Learning",
        category: "Data Science",
        duration: "6 semaines",
        level: "Avancé",
        image: "assets/images/machine-learning.jpg"
    },
    {
        title: "Développement Web Fullstack",
        category: "Programmation",
        duration: "8 semaines",
        level: "Intermédiaire",
        image: "assets/images/web-developement.jpg"
    },
    {
        title: "Marketing Digital",
        category: "Business",
        duration: "4 semaines",
        level: "Débutant",
        image: "assets/images/marketing.jpeg"
    },
    {
        title: "Data Analytics avec Python",
        category: "Data Science",
        duration: "6 semaines",
        level: "Intermédiaire",
        image: "assets/images/data-analytics.jpg"
    },
    {
        title: "Anglais Professionnel",
        category: "Langues",
        duration: "10 semaines",
        level: "Tous niveaux",
        image: "assets/images/Anglais Professionnel.jpeg"
    },
    {
        title: "Blockchain & Crypto-monnaies",
        category: "FinTech",
        duration: "4 semaines",
        level: "Avancé", 
        image: "assets/images/Blockchain & Crypto-monnaies.jpeg"
    },
    {
        title: "Montage Vidéo avec Premiere Pro",
        category: "Multimédia",
        duration: "5 semaines",
        level: "Débutant",
        image: "assets/images/Montage Vidéo.jpeg"
    },
    {
        title: "Intelligence Artificielle Éthique",
        category: "Éthique Technologique",
        duration: "3 semaines",
        level: "Intermédiaire",
        image: "assets/images/Intelligence Artificielle Éthique.jpeg"
    },
    {
        title: "Gestion de Projet Agile",
        category: "Management",
        duration: "3 semaines",
        level: "Débutant",
        image: "assets/images/Gestion de Projet Agile.png"
    }
];
function generateCourseCards() {
    const container = document.getElementById('coursesContainer');
    let html = '';
    
    courses.forEach(course => {
        html += `
            <div class="col-md-4 mb-3">
                <div class="card course-card h-100">
                    <img src="${course.image}" class="card-img-top" alt="${course.title}">
                    <div class="card-body">
                        <span class="badge skill-badge mb-2">${course.category}</span>
                        <h5 class="card-title">${course.title}</h5>
                        <div class="d-flex justify-content-between text-muted small mb-3">
                            <span><i class="fas fa-clock"></i> ${course.duration}</span>
                            <span>${course.level}</span>
                        </div>
                        <button class="btn btn-outline-primary w-100">Voir le cours</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Dans main.js
document.addEventListener('DOMContentLoaded', function() {
    generateCourseCards();

    // Configuration personnalisée
    const scrollRevealOption = {
        distance: '40px',
        origin: 'bottom',
        duration: 1500,
        easing: 'ease-in-out',
        delay: 200,
        interval: 300,
        reset: true,
        opacity: 0.1,
        scale: 0.9
    };

    // Animation pour les cartes de cours
    ScrollReveal().reveal('.course-card', {
        duration: 1800,
        distance: '0px',
        scale: 0.8,
        easing: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
        opacity: 0,
        interval: 150,
        afterReveal: function(el) {
            el.style.transform = 'scale(1)';
        }
    });

    // Animation pour le titre de section
    ScrollReveal().reveal('#courses h2', {
        delay: 100,
        duration: 1000,
        origin: 'top',
        distance: '30px',
        easing: 'cubic-bezier(0.5, 0, 0, 1)'
    });
});
