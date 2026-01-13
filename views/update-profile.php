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

require_once '../helpers/tracking.php';
require_once '../api/common/db.php';

// Track page visit
autoTrackPage();

// Get user's current information
$conn = getDatabaseConnection();
$stmt = $conn->prepare("SELECT telefon, adresa, departament, data_nasterii FROM utilizator WHERE utilizator_id = ?");
$stmt->execute([$_SESSION['utilizator_id']]);
$userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizează Profil - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/profile.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../common/navbar.php'; ?>

    <div class="hero-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-building me-3"></i>Actualizează Profil</h1>
                    <p class="lead mb-0">Completează sau modifică informațiile tale personale</p>
                </div>
                <a href="../dashboard.php" class="btn-back-to-dashboard">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>    

    <!-- Main Content -->
    <div class="container mt-2 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Alert Container -->
                <div id="alert-container"></div>

                <!-- Profile Form Card -->
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informații Personale</h4>
                    </div>
                    <div class="card-body">
                        <form id="updateProfileForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telefon" class="form-label">Telefon</label>
                                    <input type="tel" class="form-control" id="telefon" name="telefon" 
                                           value="<?php echo htmlspecialchars($userInfo['telefon'] ?? ''); ?>"
                                           placeholder="07xxxxxxxx">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="data_nasterii" class="form-label">Data Nașterii</label>
                                    <input type="date" class="form-control" id="data_nasterii" name="data_nasterii"
                                           value="<?php echo htmlspecialchars($userInfo['data_nasterii'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="adresa" class="form-label">Adresa</label>
                                <input type="text" class="form-control" id="adresa" name="adresa"
                                       value="<?php echo htmlspecialchars($userInfo['adresa'] ?? ''); ?>"
                                       placeholder="Stradă, număr, oraș">
                            </div>

                            <div class="mb-3">
                                <label for="departament" class="form-label">Departament</label>
                                <input type="text" class="form-control" id="departament" name="departament"
                                       value="<?php echo htmlspecialchars($userInfo['departament'] ?? ''); ?>"
                                       placeholder="Departament / Echipă">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Salvează Modificările
                                </button>
                            </div>
                        </form>
                    </div>
                </div>                
            </div>
        </div>
    </div>

    <?php include '../common/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/profile.js?v=<?php echo time(); ?>"></script>
</body>
</html>
