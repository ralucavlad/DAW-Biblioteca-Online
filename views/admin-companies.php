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

$userName = $_SESSION['nume'] . ' ' . $_SESSION['prenume'];
$userRole = $_SESSION['rol'];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionare Companii - Admin</title>    
    <link rel="icon" type="image/x-icon" href="../img/favicon.svg">    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">    
    <link rel="stylesheet" href="../css/global.css?v=<?php echo time(); ?>">    
    <link rel="stylesheet" href="../css/admin-companies.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../common/navbar.php'; ?>
    
    <div class="hero-banner">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-building me-3"></i>Gestionare Companii</h1>
                    <p class="lead mb-0">Administrare completă a companiilor din sistem</p>
                </div>
                <a href="../dashboard.php" class="btn-back-to-dashboard">
                    <i class="fas fa-arrow-left me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <div class="container mb-5">        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalCompanies">-</h3>
                        <p>Total Companii</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-managers">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalManagers">-</h3>
                        <p>Manageri Total</p>
                    </div>
                </div>
            </div>
        </div>        
        
        <!-- Actions Bar -->
        <div class="mb-3">
            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
                <i class="fas fa-plus me-2"></i>Adaugă Companie
            </button>
        </div>
        
        <!-- Companies Table -->
        <div class="card">
            <div class="card-body">
                <table id="companiesTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nume</th>
                            <th>CUI</th>
                            <th>Adresă</th>
                            <th>Email</th>
                            <th>Telefon</th>
                            <th>Acțiuni</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Company Modal -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Adaugă Companie Nouă</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addCompanyForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nume Companie *</label>
                                <input type="text" class="form-control" name="nume" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CUI *</label>
                                <input type="text" class="form-control" name="cui" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Adresă</label>
                            <textarea class="form-control" name="adresa" rows="2"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefon</label>
                                <input type="tel" class="form-control" name="telefon">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button>
                    <button type="button" class="btn btn-primary" onclick="saveCompany()">Salvează
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Company Modal -->
    <div class="modal fade" id="editCompanyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editează Companie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editCompanyForm">
                        <input type="hidden" name="companie_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nume Companie *</label>
                                <input type="text" class="form-control" name="nume" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CUI (nu se poate modifica)</label>
                                <input type="text" class="form-control" name="cui" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Adresă</label>
                            <textarea class="form-control" name="adresa" rows="2"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefon</label>
                                <input type="tel" class="form-control" name="telefon">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulează</button>
                    <button type="button" class="btn btn-primary" onclick="updateCompany()">Actualizează
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Company Modal -->
    <div class="modal fade" id="viewCompanyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalii Companie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="companyDetailsContent">
                    <!-- Populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../common/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Admin Companies JS -->
    <script src="../js/admin-companies.js?v=<?php echo time(); ?>"></script>
</body>
</html>
