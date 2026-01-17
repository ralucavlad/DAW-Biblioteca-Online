<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../common/db.php';

if (!isset($_SESSION['utilizator_id'])) {
    echo json_encode(['success' => false, 'message' => 'Trebuie să fii autentificat.']);
    exit;
}

if ($_SESSION['rol'] === 'admin') {
    echo json_encode(['success' => false, 'message' => 'Administratorii nu pot actualiza profilul prin această pagină']);
    exit;
}

try {
    $conn = getDatabaseConnection();
       
    $bookId = (int)$_POST['book_id'];
    $userId = (int)$_SESSION['utilizator_id'];    
        
    // Check if book is available
    $bookQuery = "SELECT carte_id, denumire, nr_exemplare_disponibile FROM carte WHERE carte_id = ?";
    $bookStmt = $conn->prepare($bookQuery);
    $bookStmt->execute([$bookId]);
    $book = $bookStmt->fetch();
        
    if ($book['nr_exemplare_disponibile'] <= 0) {
        throw new Exception('Cartea nu este disponibilă momentan');
    }
    
    // Check if user already has an active rental for this book
    $checkQuery = "SELECT inchiriere_id FROM inchiriere 
                   WHERE utilizator_id = ? AND carte_id = ? AND stare = 'activa'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute([$userId, $bookId]);
    
    if ($checkStmt->fetch()) {
        throw new Exception('Ai deja o închiriere activă pentru această carte');
    }
    
    // Check if user has reached the maximum of 3 active rentals
    $countQuery = "SELECT COUNT(*) as total FROM inchiriere 
                   WHERE utilizator_id = ? AND stare = 'activa'";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute([$userId]);
    $activeRentals = $countStmt->fetch();
    
    if ($activeRentals['total'] >= 3) {
        throw new Exception('Ai atins limita maximă de 3 cărți închiriate simultan. Te rugăm să returnezi o carte înainte de a închiria alta.');
    }
    
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Calculate dates
        $dataInchiriere = date('Y-m-d H:i:s');
        $dataPrimire = date('Y-m-d', strtotime('+4 days')); // Arrives in 4 days
        $dataScadenta = date('Y-m-d', strtotime('+34 days')); // 4 days + 30 days (1 month)
        
        // Create rental
        $insertQuery = "INSERT INTO inchiriere 
                       (utilizator_id, carte_id, data_inchiriere, data_scadenta, stare) 
                       VALUES (?, ?, ?, ?, 'activa')";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->execute([$userId, $bookId, $dataInchiriere, $dataScadenta]);
        
        // Decrease available copies
        $updateQuery = "UPDATE carte 
                       SET nr_exemplare_disponibile = nr_exemplare_disponibile - 1 
                       WHERE carte_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->execute([$bookId]);
        
        // Commit transaction
        $conn->commit();
        
        // Get the ID of the newly created rental
        $inchiriereId = $conn->lastInsertId();
        
        // Send email confirmation (non-blocking - don't fail if email fails)
        try {
            require_once __DIR__ . '/../auth/email_helpers.php';
            
            // Get user details
            $userQuery = "SELECT email, nume, prenume FROM utilizator WHERE utilizator_id = ?";
            $userStmt = $conn->prepare($userQuery);
            $userStmt->execute([$userId]);
            $user = $userStmt->fetch();
            
            $result = sendRentalConfirmation(
                $user['email'],
                $user['nume'] . ' ' . $user['prenume'],
                $book['denumire'], 
                $dataPrimire, 
                $dataScadenta
            );
                        
        } catch (Exception $emailError) {
            // Log error but don't fail the rental
            error_log("Email sending failed for rental $inchiriereId: " . $emailError->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Cartea a fost închiriată cu succes!',
            'book_name' => $book['denumire'],
            'data_primire' => $dataPrimire,
            'data_scadenta' => $dataScadenta,
            'formatted_primire' => date('d.m.Y', strtotime($dataPrimire)),
            'formatted_scadenta' => date('d.m.Y', strtotime($dataScadenta))
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
