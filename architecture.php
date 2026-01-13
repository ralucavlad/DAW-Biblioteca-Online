<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/tracking.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$userName = $isLoggedIn ? ($_SESSION['nume'] ?? '') . ' ' . ($_SESSION['prenume'] ?? '') : '';
$userEmail = $isLoggedIn ? ($_SESSION['email'] ?? '') : '';

// Track page visit
autoTrackPage();
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arhitectura Aplicației - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
    
    <!-- Architecture page CSS -->
    <link rel="stylesheet" href="css/architecture.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'common/navbar.php'; ?>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Hero Section -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-light mb-3">Arhitectura Aplicației</h1>
            <p class="lead text-muted">Biblioteca Online pentru Angajați Companii</p>
        </div>

        <!-- Descriere Generală -->
        <div class="section-header">
            <h2>Descriere Generală</h2>
        </div>
        <p class="mb-4">
            Biblioteca Online este o aplicație web destinată companiilor pentru gestionarea unei biblioteci digitale 
            accesibile angajaților. Sistemul permite închirierea de cărți (fizice, ebook, audiobook), gestionarea 
            utilizatorilor pe roluri, tracking-ul activității și generarea de rapoarte detaliate.
        </p>

        <!-- Roluri -->
        <div class="section-header">
            <h2>Roluri și Permisiuni</h2>
        </div>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card role-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h5 class="card-title">Administrator</h5>
                        <ul class="list-unstyled text-start feature-list">
                            <li><i class="fas fa-check"></i>Gestionare companii</li>
                            <li><i class="fas fa-check"></i>Răspuns la mesaje contact</li>
                            <li><i class="fas fa-check"></i>Nu poate trimite mesaje contact</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card role-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h5 class="card-title">Manager Companie</h5>
                        <ul class="list-unstyled text-start feature-list">
                            <li><i class="fas fa-check"></i>Vizualizare activitate angajați companie</li>
                            <li><i class="fas fa-check"></i>Statistici și rapoarte companie</li>
                            <li><i class="fas fa-check"></i>Închiriere cărți (ca angajat)</li>
                            <li><i class="fas fa-check"></i>Recenzii și favorite</li>
                            <li><i class="fas fa-check"></i>Trimite mesaje contact</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card role-card">
                    <div class="card-body">
                        <div class="icon-box">
                            <i class="fas fa-user"></i>
                        </div>
                        <h5 class="card-title">Angajat (User)</h5>
                        <ul class="list-unstyled text-start feature-list">
                            <li><i class="fas fa-check"></i>Căutare și filtrare cărți</li>
                            <li><i class="fas fa-check"></i>Închiriere și returnare cărți</li>
                            <li><i class="fas fa-check"></i>Scrie recenzii cu rating</li>
                            <li><i class="fas fa-check"></i>Gestionare listă favorite</li>
                            <li><i class="fas fa-check"></i>Istoric închirieri personale</li>
                            <li><i class="fas fa-check"></i>Trimite mesaje contact</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Entități Principale -->
        <div class="section-header">
            <h2>Entități Principale</h2>
        </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-users me-2"></i>Entități Utilizatori
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>tip_utilizator</strong> - Roluri (admin, manager, angajat)
                            </li>
                            <li class="list-group-item">
                                <strong>companie</strong> - Date companii
                            </li>
                            <li class="list-group-item">
                                <strong>utilizator</strong> - Conturi utilizatori cu autentificare
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-book me-2"></i>Entități Bibliotecă
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>carte</strong> - Informații cărți (ISBN, descriere, stoc)
                            </li>
                            <li class="list-group-item">
                                <strong>autor</strong> - Autori cărți
                            </li>
                            <li class="list-group-item">
                                <strong>domeniu</strong> - Categorii/genuri cărți
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-exchange-alt me-2"></i>Entități Operaționale
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>inchiriere</strong> - Închirieri cărți
                            </li>
                            <li class="list-group-item">
                                <strong>recenzie</strong> - Recenzii și rating-uri
                            </li>
                            <li class="list-group-item">
                                <strong>favorite</strong> - Cărți favorite utilizatori
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-line me-2"></i>Entități Analytics & Contact
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>sesiune</strong> - Tracking sesiuni utilizatori
                            </li>
                            <li class="list-group-item">
                                <strong>vizitare_pagina</strong> - Tracking pagini vizitate
                            </li>
                            <li class="list-group-item">
                                <strong>contact_mesaj</strong> - Mesaje formular contact
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Procese Principale -->
        <div class="section-header">
            <h2>Procese Principale</h2>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-3">1. Înregistrare și Autentificare</h5>
                <div class="list-group">
                    <div class="list-group-item">Înregistrare cont nou</div>
                    <div class="list-group-item">Verificare reCAPTCHA</div>
                    <div class="list-group-item">Email cu link activare</div>
                    <div class="list-group-item">Activare cont (stare: activ)</div>
                    <div class="list-group-item">Login: Email + Parolă + reCAPTCHA</div>
                    <div class="list-group-item">Cod 2FA trimis pe email</div>
                    <div class="list-group-item">Verificare cod → Sesiune PHP</div>
                    <div class="list-group-item">Redirect dashboard (după rol)</div>
                </div>
            </div>

            <div class="col-md-6">
                <h5 class="mb-3">2. Închiriere Carte</h5>
                <div class="list-group">
                    <div class="list-group-item">User caută/filtrează carte</div>
                    <div class="list-group-item">Vizualizare detalii carte</div>
                    <div class="list-group-item">Verificare disponibilitate stoc</div>
                    <div class="list-group-item">Creare înregistrare închiriere</div>
                    <div class="list-group-item">Update stoc disponibil (-1)</div>
                    <div class="list-group-item">Email confirmare cu date primire</div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-3">3. Generare Raport (Manager)</h5>
                <div class="list-group">
                    <div class="list-group-item">Manager selectează interval date</div>
                    <div class="list-group-item">Aplică filtre</div>
                    <div class="list-group-item">Query date activitate angajați</div>
                    <div class="list-group-item">Generare PDF cu TCPDF</div>
                    <div class="list-group-item">Download raport</div>
                </div>
            </div>
        </div>

        <!-- Arhitectura Bazei de Date -->
        <div class="section-header">
            <h2>Structura Bazei de Date</h2>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                Schema Relațională Simplificată
            </div>
            <div class="card-body">
                <p class="mb-3">Baza de date conține <strong>14 tabele</strong> organizate în 4 categorii:</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-users me-2"></i>Gestionare Utilizatori</h6>
                        <ul class="feature-list">
                            <li><code>tip_utilizator</code> - 3 roluri (admin, manager, angajat)</li>
                            <li><code>companie</code> - Date companii (CUI, adresă, contact)</li>
                            <li><code>utilizator</code> - Conturi cu autentificare</li>
                        </ul>

                        <h6 class="mt-4"><i class="fas fa-book me-2"></i>Gestionare Bibliotecă</h6>
                        <ul class="feature-list">
                            <li><code>autor</code> - Autori cu biografie</li>
                            <li><code>domeniu</code> - Categorii/genuri cărți</li>
                            <li><code>carte</code> - Cărți cu ISBN, stoc, format</li>
                            <li><code>carte_autor</code> - Many-to-many cărți-autori</li>
                            <li><code>carte_domeniu</code> - Many-to-many cărți-domenii</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-exchange-alt me-2"></i>Operațiuni</h6>
                        <ul class="feature-list">
                            <li><code>inchiriere</code> - Închirieri cu status și date</li>
                            <li><code>recenzie</code> - Recenzii cu rating 1-5</li>
                            <li><code>favorite</code> - Wishlist utilizatori</li>
                        </ul>

                        <h6 class="mt-4"><i class="fas fa-chart-line me-2"></i>Analytics & Contact</h6>
                        <ul class="feature-list">
                            <li><code>sesiune</code> - Tracking sesiuni (IP, user agent)</li>
                            <li><code>vizitare_pagina</code> - Tracking pagini vizitate</li>
                            <li><code>contact_mesaj</code> - Mesaje contact cu răspunsuri</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
 
        <!-- Stack Tehnologic -->
        <div class="section-header">
            <h2>Stack Tehnologic</h2>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-3">
                        <div class="icon-box mx-auto">
                            <i class="fab fa-html5"></i>
                        </div>
                        <p class="mb-0">HTML5</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="icon-box mx-auto">
                            <i class="fab fa-css3-alt"></i>
                        </div>
                        <p class="mb-0">CSS3</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="icon-box mx-auto">
                            <i class="fab fa-bootstrap"></i>
                        </div>
                        <p class="mb-0">Bootstrap 5</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="icon-box mx-auto">
                            <i class="fab fa-php"></i>
                        </div>
                        <p class="mb-0">PHP 8.x</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="icon-box mx-auto">
                            <i class="fas fa-database"></i>
                        </div>
                        <p class="mb-0">MySQL 8.x</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="icon-box mx-auto">
                            <i class="fab fa-font-awesome"></i>
                        </div>
                        <p class="mb-0">FontAwesome</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="icon-box mx-auto">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <p class="mb-0">PHPMailer</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <div class="icon-box mx-auto">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <p class="mb-0">TCPDF</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'common/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
