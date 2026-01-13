<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['utilizator_id'])) {
    header('Location: login.php');
    exit;
}

// Include dependencies
require_once __DIR__ . '/helpers/tracking.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/api/common/db.php';

// Track this page visit
autoTrackPage();

// Set variables for navbar
$isLoggedIn = true;
$userName = $_SESSION['nume'] . ' ' . $_SESSION['prenume'];
$userRole = $_SESSION['rol'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/dashboard.css?v=<?php echo time(); ?>">
    
    <?php if ($userRole === 'admin'): ?>
    <link rel="stylesheet" href="css/admin-dashboard.css?v=<?php echo time(); ?>">
    <?php endif; ?>
</head>
<body>
    <?php include 'common/navbar.php'; ?>

    <!-- Dashboard Hero -->
    <div class="hero-banner">
        <div class="container">
            <h1><i class="fas fa-tachometer-alt me-3"></i>Dashboard</h1>
            <p class="lead">Bine ai revenit! Gestionează-ți activitatea în bibliotecă.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5">
        
        <?php if ($userRole === 'admin'): ?>
            <!-- Admin Section -->
            <?php include 'views/admin-section.php'; ?>
        <?php else: ?>
            <!-- Regular User Content -->
            
            <!-- Quick Stats -->
            <div class="quick-stats mb-5">
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-item">
                            <span class="stat-number">
                                <i class="fas fa-book-open text-primary"></i> <span id="rentalsCount">0</span>
                            </span>
                            <span class="stat-label">Cărți închiriate</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <span class="stat-number">
                                <i class="fas fa-star text-warning"></i> <span id="reviewsCount">0</span>
                            </span>
                            <span class="stat-label">Recenzii scrise</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-item">
                            <span class="stat-number">
                                <i class="fas fa-heart text-danger"></i> <span id="favoritesCount">0</span>
                            </span>
                            <span class="stat-label">Cărți favorite</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="row">
                <!-- Rental History -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="views/my-rentals.php" class="dashboard-card">
                        <div class="dashboard-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h4>Istoric Închirieri</h4>
                        <p>Vezi toate cărțile pe care le-ai închiriat și statusul acestora</p>
                    </a>
                </div>

                <!-- Search Books -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="views/search-books.php" class="dashboard-card">
                        <div class="dashboard-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4>Caută Cărți</h4>
                        <p>Explorează colecția noastră și găsește cartea potrivită pentru tine</p>
                    </a>
                </div>

                <!-- My Reviews -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="views/my-reviews.php" class="dashboard-card">
                        <div class="dashboard-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h4>Recenziile Mele</h4>
                        <p>Vizualizează și editează recenziile tale pentru cărțile citite</p>
                    </a>
                </div>

                <!-- Favorites -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="views/my-favorites.php" class="dashboard-card">
                        <div class="dashboard-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h4>Lista Favorite</h4>
                        <p>Accesează rapid cărțile tale preferate salvate pentru mai târziu</p>
                    </a>
                </div>

                <?php if ($userRole !== 'admin'): ?>
                <!-- Update Profile (only for managers and users) -->
                <div class="col-md-6 col-lg-3 mb-4">
                    <a href="views/update-profile.php" class="dashboard-card">
                        <div class="dashboard-icon">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <h4>Actualizează Profil</h4>
                        <p>Modifică informațiile tale personale</p>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Manager Section -->
            <?php include 'views/manager-section.php'; ?>
        
        <?php endif; // End regular user content ?>

    </div>

    <?php include 'common/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStats();
        });

        function loadDashboardStats() {
            // Check if stats elements exist (only for non-admin users)
            if (!document.getElementById('reviewsCount')) return;
            
            fetch('api/users/get_dashboard_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('reviewsCount').textContent = data.reviews;
                    document.getElementById('favoritesCount').textContent = data.favorites;
                    document.getElementById('rentalsCount').textContent = data.rentals; 
                }
            })
            .catch(error => {
                console.error('Error loading dashboard stats:', error);
            });
        }
    </script>
</body>
</html>
