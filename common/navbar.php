<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/index.php">
            <i class="fas fa-book-reader me-2"></i>Biblioteca Online
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="/index.php"><i class="fas fa-home me-1"></i>Acasă</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/architecture.php"><i class="fas fa-sitemap me-1"></i>Arhitectură</a>
                </li>
                <?php if (isset($_SESSION['utilizator_id'])): ?>
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/contact.php"><i class="fas fa-envelope me-1"></i>Contact</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard.php">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['prenume'] . ' ' . $_SESSION['nume']); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt me-1"></i>Delogare</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php"><i class="fas fa-sign-in-alt me-1"></i>Autentificare</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['utilizator_id'])): ?>
<script>
document.getElementById('logoutBtn').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm('Sigur doriți să vă delogați?')) {
        fetch('/api/auth/logout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/index.php';
            } else {
                alert('Eroare la delogare: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Eroare la delogare:', error);
            alert('Eroare la delogare. Vă rugăm încercați din nou.');
        });
    }
});
</script>
<?php endif; ?>
