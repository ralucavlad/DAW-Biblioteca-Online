<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['utilizator_id'])) {
    header('Location: ../login.php');
    exit;
}

// Only managers and users can access this page (not admin)
if ($_SESSION['rol'] === 'admin') {
    header('Location: ../403.php');
    exit;
}

require_once __DIR__ . '/../helpers/tracking.php';

// Track page visit
autoTrackPage();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Istoric Închirieri - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/my-rentals.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../common/navbar.php'; ?>

    <div class="hero-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0"><i class="fas fa-history me-2"></i>Istoric Închirieri</h1>
                    <p class="lead mb-0">Vezi toate cărțile pe care le-ai închiriat și statusul acestora</p>
                </div>
                <a href="../dashboard.php" class="btn-back-to-dashboard">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div> 

    <div class="container mb-5">
        <!-- Rental Limit Info -->
        <div id="rentalLimitInfo" class="alert alert-info mb-4" style="display: none;">
            <div class="d-flex align-items-center">
                <i class="fas fa-info-circle fa-2x me-3"></i>
                <div>
                    <strong>Închirieri active: <span id="activeCount">0</span>/3</strong>
                    <p class="mb-0 small" id="limitMessage">Poți închiria încă <span id="remainingSlots">3</span> cărți.</p>
                </div>
            </div>
        </div>

         <!-- No Rentals -->
        <div id="noRentals" class="text-center py-5">
            <h3>Nu ai încă închirieri</h3>
        </div>

        <!-- Rentals Container -->
        <div id="rentalsContainer">
            <!-- Rentals will be loaded here -->
        </div>
    </div>

    <?php include '../common/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/my-rentals.js?v=<?php echo time(); ?>"></script>
</body>
</html>
