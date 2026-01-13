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
    <title>Recenziile Mele - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/my-reviews.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../common/navbar.php'; ?>

    <div class="hero-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-star me-3"></i>Recenziile Mele</h1>
                    <p class="lead mb-0">Gestionează recenziile tale pentru cărțile citite</p>
                </div>
                <a href="../dashboard.php" class="btn-back-to-dashboard">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div> 

    <div class="container mb-5">
        <!-- No Reviews -->
        <div id="noReviews" class="text-center py-5">
            <h3>Nu ai încă recenzii</h3>
        </div>

        <!-- Reviews Container -->
        <div id="reviewsContainer">
            <!-- Reviews will be loaded here -->
        </div>
    </div>

    <?php include '../common/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/my-reviews.js?v=<?php echo time(); ?>"></script>
</body>
</html>
