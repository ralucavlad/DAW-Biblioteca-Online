<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if user is admin - they shouldn't access contact page
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    header('Location: 403.php');
    exit();
}

// Include dependencies
require_once __DIR__ . '/helpers/tracking.php';
require_once __DIR__ . '/api/common/db.php';

// Track page visit
autoTrackPage();


$isLoggedIn = true;
$userName = ($_SESSION['nume'] ?? '') . ' ' . ($_SESSION['prenume'] ?? '');
$userEmail = $_SESSION['email'] ?? $_SESSION['user_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/contact.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'common/navbar.php'; ?>

    <div class="hero-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0"><i class="fas fa-envelope me-2"></i>Contactează-ne</h1>
                    <p class="lead mb-0">Suntem aici să te ajutăm! Trimite-ne un mesaj.</p>
                </div>
                <a href="../dashboard.php" class="btn-back-to-dashboard">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div> 

    <div class="container mb-5">
        <div class="contact-container">   
            <!-- Success Message -->
            <div id="successMessage" class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Mesaj trimis cu succes!</strong> Vom răspunde în cel mai scurt timp.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- Contact Form -->
            <div class="contact-card">
                <form id="contactForm">
                    <div class="mb-4">
                        <label for="nume" class="form-label">
                            Nume complet <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="nume" 
                               value="<?php echo htmlspecialchars($userName); ?>" 
                               readonly>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">
                            Email <span class="required">*</span>
                        </label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               value="<?php echo htmlspecialchars($userEmail); ?>" 
                               readonly>
                    </div>

                    <div class="mb-4">
                        <label for="subiect" class="form-label">
                            Subiect <span class="required">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="subiect" 
                               placeholder="Ex: Problema cu inchirierea, Intrebare despre carte..."
                               maxlength="255"
                               required>
                        <div class="char-count">
                            <span id="subiectCount">0</span>/255 caractere
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="mesaj" class="form-label">
                            Mesaj <span class="required">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="mesaj" 
                                  rows="8" 
                                  placeholder="Descrie in detaliu problema sau intrebarea ta..."
                                  maxlength="2000"
                                  required></textarea>
                        <div class="char-count">
                            <span id="mesajCount">0</span>/2000 caractere
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-custom" id="submitBtn">Trimite Mesaj</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'common/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/common.js?v=<?php echo time(); ?>"></script>
    <script src="js/contact.js?v=<?php echo time(); ?>"></script>
</body>
</html>
