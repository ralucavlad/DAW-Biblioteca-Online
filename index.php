<?php
session_start();
require_once 'config/config.php';
require_once __DIR__ . '/api/common/db.php';
require_once __DIR__ . '/helpers/tracking.php';

// Check if user is authenticated
$is_logged_in = isset($_SESSION['utilizator_id']);

// Get database connection
$conn = getDatabaseConnection();

// Track page visit only for logged in users
if ($is_logged_in) {
    autoTrackPage();
}

// If user is NOT authenticated, get 3 random books
$random_books = [];

if (!$is_logged_in) {
    try {
        if ($conn) {            
            $stmt = $conn->query("
                SELECT c.carte_id, c.denumire, c.descriere, c.url_coperta, 
                       a.nume as autor_nume
                FROM carte c
                LEFT JOIN autor a ON c.autor_id = a.autor_id
                WHERE c.nr_exemplare_disponibile > 0
                ORDER BY RAND()
                LIMIT 3
            ");
            $random_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } 
    } catch (PDOException $e) {
        error_log("Eroare: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca Online - Acasă</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
    
    <?php if (!$is_logged_in): ?>
        <!-- CSS for visitors -->
        <link rel="stylesheet" href="css/book-cards.css?v=<?php echo time(); ?>">
    <?php else: ?>
        <!-- CSS for authenticated users -->
        <link rel="stylesheet" href="css/authenticated-content.css?v=<?php echo time(); ?>">
    <?php endif; ?>
</head>
<body>
    <?php include 'common/navbar.php'; ?>

    <?php if (!$is_logged_in): ?>
        <!-- Content for unauthenticated visitors -->

        <!-- Hero Section for visitors -->
        <div class="hero-section">
            <div class="container">
                <h1>Descoperă Lumea Cărților</h1>
                <p class="lead">Biblioteca ta digitală cu mii de titluri la un click distanță</p>
                <a href="login.php" class="btn btn-outline-dark btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Creează cont sau autentifică-te
                </a>
            </div>
        </div>

        <?php include 'views/featured-books.php'; ?>
    <?php else: ?>
        <!-- Redirect authenticated users to dashboard -->
        <?php header('Location: dashboard.php'); exit; ?>
    <?php endif; ?>

    <?php include 'common/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
