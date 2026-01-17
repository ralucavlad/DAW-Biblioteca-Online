<?php
/**
 * Account Activation Page
 * Activates user account using the token from email
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/api/common/db.php';

$message = '';
$success = false;

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = 'Token de activare invalid sau lipsa.';
} else {
    try {
        $pdo = getDatabaseConnection();
        
        // Find user by activation token
        $stmt = $pdo->prepare("
            SELECT utilizator_id, nume, prenume, email, stare 
            FROM utilizator 
            WHERE token_activare_cont = ?
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $message = 'Token de activare invalid sau expirat.';
        } elseif ($user['stare'] === 'activ') {
            $message = 'Acest cont este deja activ. Puteti sa va autentificati.';
            $success = true;
        } else {
            // Activate account
            $updateStmt = $pdo->prepare("
                UPDATE utilizator 
                SET stare = 'activ', 
                    data_activare_cont = NOW(), 
                    token_activare_cont = NULL 
                WHERE utilizator_id = ?
            ");
            $updateStmt->execute([$user['utilizator_id']]);
            
            $message = 'Felicitari, ' . htmlspecialchars($user['nume']) . '! Contul dumneavoastra a fost activat cu succes. Acum va puteti autentifica.';
            $success = true;
        }
        
    } catch (PDOException $e) {
        error_log("Activation Error: " . $e->getMessage());
        $message = 'A aparut o eroare la activarea contului. Va rugam incercati din nou sau contactati administratorul.';
    }
}

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activare Cont - Biblioteca Online</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 3rem 2rem;
            max-width: 500px;
            text-align: center;
        }
        .icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #212529;
        }
        p {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: #212529;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #343a40;
        }
        .btn i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($success): ?>
            <i class="fas fa-check-circle icon success"></i>
            <h2>Cont Activat!</h2>
        <?php else: ?>
            <i class="fas fa-times-circle icon error"></i>
            <h2>Activare Eșuată</h2>
        <?php endif; ?>
        
        <p><?php echo htmlspecialchars($message); ?></p>
        
        <?php if ($success): ?>
            <a href="login.php" class="btn">
                <i class="fas fa-sign-in-alt"></i>Autentificare
            </a>
        <?php else: ?>
            <a href="index.php" class="btn">
                <i class="fas fa-home"></i>Acasă
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
