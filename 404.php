<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Pagina Nu Există</title>
    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 500px;
        }
        .error-icon {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        .error-code {
            font-size: 4rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.5rem;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 500;
            color: #212529;
            margin-bottom: 0.75rem;
        }
        p {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        .btn-home {
            display: inline-block;
            background: #212529;
            color: #ffffff;
            padding: 0.75rem 2rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: background 0.2s ease;
        }
        .btn-home:hover {
            background: #343a40;
        }
        .btn-home i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="fas fa-search error-icon"></i>
        <div class="error-code">404</div>
        <h1>Pagina Nu Există</h1>
        <p>Pagina pe care o căutați nu a fost găsită.</p>
        <a href="dashboard.php" class="btn-home">
            <i class="fas fa-home"></i>Înapoi la Dashboard
        </a>
    </div>
</body>
</html>
