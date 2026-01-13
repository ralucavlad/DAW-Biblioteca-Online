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

$isLoggedIn = isset($_SESSION['utilizator_id']);
$userName = $isLoggedIn ? ($_SESSION['nume'] ?? '') . ' ' . ($_SESSION['prenume'] ?? '') : '';
$userId = $isLoggedIn ? ($_SESSION['utilizator_id'] ?? 0) : 0;

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookId <= 0) {
    header('Location: search-books.php');
    exit();
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
    <title>Detalii Carte - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/book-details.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../common/navbar.php'; ?>

    <div class="hero-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0"><i class="fas fa-book me-2"></i>Detalii Carte</h1>                    
                </div>
                <a href="search-books.php" class="btn-back-to-dashboard">
                    <i class="fas fa-arrow-left me-2"></i>Căutare Cărți
                </a>
            </div>
        </div>
    </div>

    <!-- Book Details Container -->
    <div id="bookDetailsContainer" class="container book-details-container mt-4" style="display: none;">
        <div class="row">
            <!-- Left Column - Book Cover -->
            <div class="col-lg-4 mb-4">
                <div class="text-center">
                    <img id="bookCover" src="" alt="Book Cover" class="book-cover-large mb-3">
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons d-grid gap-2">
                        <button class="btn btn-light border border-dark btn-lg" id="rentBtn">Închiriază Cartea</button>
                        <button class="btn btn-outline-dark" id="favoriteBtn">
                            <i class="far fa-heart me-2"></i>Adaugă la Favorite
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Book Information -->
            <div class="col-lg-8">
                <!-- Main Info Card -->
                <div class="book-info-card">
                    <h1 class="book-main-title" id="bookTitle"></h1>
                    
                    <!-- Rating -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="rating-stars me-3" id="ratingStars"></div>
                        <span class="rating-number me-2" id="avgRating">0.0</span>
                        <span class="reviews-count" id="reviewsCount">(0 recenzii)</span>
                    </div>

                    <!-- Availability -->
                    <div class="mb-4">
                        <span id="availabilityBadge" class="availability-badge badge"></span>
                    </div>

                    <!-- Book Meta Information -->
                    <div id="bookMeta"></div>
                </div>

                <!-- Description Card -->
                <div class="book-info-card">
                    <h3 class="section-title">Descriere</h3>
                    <p class="book-description" id="bookDescription"></p>
                </div>

                <!-- Reviews Section -->
                <div class="book-info-card">
                    <h3 class="section-title">Evaluare și Recenzii</h3>
                                        
                    <!-- Add Review Form -->
                    <div class="mb-4">
                        <h5>Lasă o recenzie</h5>
                        <form id="reviewForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Evaluarea ta:</label>
                                <div class="star-rating-input">
                                    <input type="radio" name="rating" id="star5" value="5">
                                    <label for="star5"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star4" value="4">
                                    <label for="star4"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star3" value="3">
                                    <label for="star3"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star2" value="2">
                                    <label for="star2"><i class="fas fa-star"></i></label>
                                    <input type="radio" name="rating" id="star1" value="1">
                                    <label for="star1"><i class="fas fa-star"></i></label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="reviewText" class="form-label fw-bold">Recenzia ta:</label>
                                <textarea class="form-control" id="reviewText" rows="4" 
                                          placeholder="Scrie recenzia ta aici..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-light border border-dark">Trimite Recenzia</button>
                        </form>
                    </div>                    

                    <!-- Reviews List -->
                    <div id="reviewsList">                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../common/rental-modal.php'; ?>

    <?php include '../common/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set global variables for the external JS file
        const bookId = <?php echo $bookId; ?>;
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        const userId = <?php echo $userId; ?>;
    </script>
    <script src="../js/book-details.js?v=<?php echo time(); ?>"></script>
</body>
</html>
