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
    <title>Caută Cărți - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/book-cards.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../common/navbar.php'; ?>

    <div class="hero-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-0"><i class="fas fa-history me-2"></i>Istoric Închirieri</h1>                    
                </div>
                <a href="../dashboard.php" class="btn-back-to-dashboard">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>     

    <div class="container mt-2 mb-5">
        <!-- Filters -->
        <div class="search-filters">
            <form id="searchForm">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="searchTitle" class="form-label filter-label">Titlu</label>
                        <input type="text" 
                                class="form-control" 
                                id="searchTitle" 
                                name="title" 
                                placeholder="Caută după titlu...">
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col">
                        <button type="submit" class="btn btn-dark me-2">
                            <i class="fas fa-search me-1"></i>Caută
                        </button>
                        <span class="ms-3 text-muted" id="resultCount"></span>
                    </div>
                </div>
            </form>
        </div>

        <!-- No Results -->
        <div id="noResults" class="text-center py-5">
            <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
            <h4>Nu s-au găsit cărți</h4>
            <p class="text-muted">Încearcă să modifici criteriile de căutare</p>
        </div>

        <!-- Books Grid -->
        <div id="booksContainer" class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
            <!-- Cards will be loaded here -->
        </div>
    </div>

    <?php include '../common/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/search-books.js?v=<?php echo time(); ?>"></script>
</body>
</html>
