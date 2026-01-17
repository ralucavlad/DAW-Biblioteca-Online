<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autentificare - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/login.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include 'common/navbar.php'; ?>

    <div class="login-wrapper">
        <div class="container">
            <div class="login-container">
                <!-- Card Login -->
                <div class="card">
                    <div class="card-body">
                        <!-- Alert Messages -->
                        <div id="alert-container"></div>

                        <form id="step1-form">
                            <div class="mb-3">
                                <label for="email" class="form-label">Adresa Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="exemplu@companie.ro" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Parola</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Introduceti parola" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div id="recaptcha-login" class="g-recaptcha" data-sitekey="6LfWczcsAAAAAB-y0dMqg_lxBsPClgNYMlwXofRT"></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3"><i class="fas fa-arrow-right me-2"></i>Continuă</button>
                            <hr>
                            <div class="text-center">
                                <a href="#" class="btn-link" data-bs-toggle="modal" data-bs-target="#registerModal"><i class="fas fa-user-plus me-1"></i>Nu ai cont? Înregistrare</a>
                            </div>
                        </form>

                        <form id="step2-form" class="hidden">
                            <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Am trimis un cod de verificare pe email. Verificați și folderul spam.</div>
                            <div class="mb-3">
                                <label for="verification-code" class="form-label">Cod de Verificare</label>
                                <input type="text" class="form-control text-center" id="verification-code" name="verification_code" placeholder="000000" maxlength="6" required style="font-size: 1.5rem; letter-spacing: 0.5rem;">
                                <small class="text-muted">Introduceți codul din 6 cifre</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Verifică și Autentifică-te</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Înregistrare Cont Nou</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="register-alert-container" class="alert alert-danger" style="display: none;" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><span id="register-alert-message"></span>
                    </div>
                    
                    <form id="register-form">
                        <div class="mb-3">
                            <label for="reg-email" class="form-label">Adresa Email</label>
                            <input type="email" class="form-control" id="reg-email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg-nume" class="form-label">Nume</label>
                            <input type="text" class="form-control" id="reg-nume" name="nume" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg-prenume" class="form-label">Prenume</label>
                            <input type="text" class="form-control" id="reg-prenume" name="prenume" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg-password" class="form-label">Parola</label>
                            <input type="password" class="form-control" id="reg-password" name="parola" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg-password-confirm" class="form-label">Confirma Parola</label>
                            <input type="password" class="form-control" id="reg-password-confirm" name="parola_confirm" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg-companie" class="form-label">Companie</label>
                            <select class="form-control" id="reg-companie" name="companie_id" required>
                                <option value="">Selectează compania</option>
                            </select>
                        </div>
                        <div class="alert alert-info mt-3"><i class="fas fa-info-circle me-2"></i>Veți primi un email cu link de activare. Contul va fi activ după confirmare.</div>
                        <div class="mb-3">
                            <div id="recaptcha-register" class="g-recaptcha" data-sitekey="6LfWczcsAAAAAB-y0dMqg_lxBsPClgNYMlwXofRT"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Trimite Cerere Înregistrare</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'common/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var onloadCallback = function() {
            document.querySelectorAll('.g-recaptcha').forEach(function(el) {
                el.dataset.widgetId = grecaptcha.render(el, {'sitekey': el.getAttribute('data-sitekey')});
            });
        };
    </script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    <script src="js/login.js?v=<?php echo filemtime('js/login.js'); ?>"></script>
</body>
</html>
