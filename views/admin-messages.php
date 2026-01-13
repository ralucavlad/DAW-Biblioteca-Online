<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/tracking.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../403.php');
    exit;
}

// Track page visit
autoTrackPage();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionare Mesaje - Biblioteca Online</title>
    <link rel="icon" type="image/x-icon" href="../img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../css/global.css?v=<?php echo time(); ?>">   
    <link rel="stylesheet" href="../css/admin-messages.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../common/navbar.php'; ?>

    <div class="hero-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-envelope me-3"></i>Gestionare Mesaje Contact</h1>
                    <p class="lead mb-0">Vizualizează și răspunde la mesajele utilizatorilor</p>
                </div>
                <a href="../dashboard.php" class="btn-back-to-dashboard">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container my-5">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalMessages">0</h3>
                        <p>Total Mesaje</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-new">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="newMessages">0</h3>
                        <p>Noi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-read">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="readMessages">0</h3>
                        <p>Citite</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-answered">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="answeredMessages">0</h3>
                        <p>Răspunse</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="card">
            <div class="card-body">
                <table id="messagesTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilizator</th>
                            <th>Email</th>
                            <th>Subiect</th>
                            <th>Data</th>
                            <th>Stare</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- View/Reply Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalii Mesaj</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="message-details">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>De la:</strong>
                                <p id="modalUserName"></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong>
                                <p id="modalEmail"></p>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Subiect:</strong>
                                <p id="modalSubject"></p>
                            </div>
                            <div class="col-md-6">
                                <strong>Data trimitere:</strong>
                                <p id="modalDate"></p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Mesaj:</strong>
                            <div class="message-box" id="modalMessage"></div>
                        </div>
                        <div id="existingReplySection" style="display: none;">
                            <strong>Răspuns trimis:</strong>
                            <div class="reply-box" id="modalExistingReply"></div>
                            <p class="text-muted small" id="modalReplyDate"></p>
                        </div>
                        <div id="replySection">
                            <div class="mb-3">
                                <label for="replyText" class="form-label"><strong>Răspunsul tău:</strong></label>
                                <textarea class="form-control" id="replyText" rows="5" placeholder="Scrie răspunsul aici..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
                    <button type="button" class="btn btn-primary" id="sendReplyBtn">Trimite Răspuns
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../common/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="../js/admin-messages.js?v=<?php echo time(); ?>"></script>
</body>
</html>
