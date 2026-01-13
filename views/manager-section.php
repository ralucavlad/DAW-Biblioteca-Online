<!-- Manager Section - Employee Management -->
<?php 
if ($userRole === 'manager'): 
    // Get company name for manager
    try {
        $conn = getDatabaseConnection();
        
        $stmt = $conn->prepare("
            SELECT c.nume as companie_nume 
            FROM utilizator u 
            JOIN companie c ON u.companie_id = c.companie_id 
            WHERE u.utilizator_id = ?
        ");
        $stmt->execute([$_SESSION['utilizator_id']]);
        $companie = $stmt->fetch(PDO::FETCH_ASSOC);
        $companieNume = $companie ? $companie['companie_nume'] : 'Compania Ta';
    } catch (Exception $e) {
        $companieNume = 'Compania Ta';
    }
?>
<div class="container mt-5">
    <div class="manager-section">
        <div class="section-header">
            <h2><i class="fas fa-users-cog me-3"></i>Management Angajați</h2>
            <p class="text-muted">Statistici și rapoarte despre activitatea angajaților din <strong><?php echo htmlspecialchars($companieNume); ?></strong></p>
        </div>

        <!-- Statistics Cards for Managers -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-gradient-primary">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalEmployees">-</h3>
                        <p>Total Angajați</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-gradient-success">
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="activeEmployees">-</h3>
                        <p>Activi Astăzi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-gradient-warning">
                    <div class="stat-icon">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalBooksRented">-</h3>
                        <p>Cărți Închiriate</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card bg-gradient-info">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalReviews">-</h3>
                        <p>Recenzii Scrise</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Generation Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header" style="background: #6c757d; color: white;">
                        <h5 class="mb-0">Generare Rapoarte</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-outline-secondary w-100" onclick="generateReport()">Raport PDF</button>
                            </div>
                        </div>
                        
                        <!-- Date Range Filter -->
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label>Data Început:</label>
                                <input type="date" id="reportStartDate" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label>Data Sfârșit:</label>
                                <input type="date" id="reportEndDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label>&nbsp;</label>
                                <button class="btn btn-secondary w-100" onclick="filterEmployees()">
                                    <i class="fas fa-filter me-2"></i>Filtrează
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Activity Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header" style="background: #6c757d; color: white;">
                        <h5 class="mb-0">Activitate Angajați</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="employeesTable">
                                <thead>
                                    <tr>
                                        <th>Nume</th>
                                        <th>Email</th>
                                        <th>Ultima Autentificare</th>
                                        <th>Cărți Închiriate</th>
                                        <th>Recenzii</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="employeesTableBody">
                                    <!-- Dynamic Content Loaded via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS and JS for Manager -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<link rel="stylesheet" href="css/manager-dashboard.css?v=<?php echo time(); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/manager-dashboard.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>
