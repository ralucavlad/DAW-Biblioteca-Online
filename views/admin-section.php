<?php
/**
 * Admin Dashboard Section
 * Only shown for Administrator role
 */

if (!isset($userRole) || $userRole !== 'admin') {
    return; // Don't show for non-admin users
}
?>

<!-- Admin Section -->
<div class="admin-section mb-5">
    <div class="section-header mb-4">
        <h2><i class="fas fa-shield-alt text-danger me-2"></i>Administrare Sistem</h2>
        <p class="text-muted">Gestionare completă a platformei de bibliotecă</p>
    </div>

    <!-- Admin Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="admin-stat-card companies-card">
                <div class="stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-info">
                    <h3 id="companiesCount">-</h3>
                    <p>Companii</p>
                </div>
            </div>
        </div> 
        
        <div class="col-md-6 col-lg-4">
            <div class="admin-stat-card messages-card">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="stat-info">
                    <h3 id="messagesCount">-</h3>
                    <p>Mesaje Noi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Action Cards -->
    <div class="row g-4">
        <!-- Companies Management -->
        <div class="col-md-6 col-lg-4">
            <a href="views/admin-companies.php" class="admin-action-card">
                <div class="card-icon companies">
                    <i class="fas fa-building"></i>
                </div>
                <h5>Gestionare Companii</h5>
                <p>Adaugă, modifică sau șterge companii din sistem</p>
                <div class="card-footer-link">
                    <span>Administrează <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
        </div>

        <!-- Messages Management -->
        <div class="col-md-6 col-lg-4">
            <a href="views/admin-messages.php" class="admin-action-card">
                <div class="card-icon messages">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h5>Mesaje Contact</h5>
                <p>Vizualizează și răspunde la mesajele utilizatorilor</p>
                <div class="card-footer-link">
                    <span>Deschide <i class="fas fa-arrow-right"></i></span>
                </div>
            </a>
        </div>       
    </div>
</div>

<script>
// Load admin statistics
document.addEventListener('DOMContentLoaded', function() {
    // Load companies count
    fetch('api/admin/get_companies_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('companiesCount').textContent = data.count;
            }
        })
        .catch(error => console.error('Eroare la încărcarea numărului de companii:', error));

    // Load new messages count
    fetch('api/admin/get_new_messages_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('messagesCount').textContent = data.count;
            }
        })
        .catch(error => console.error('Eroare la încărcarea numărului de mesaje noi:', error));
});
</script>
