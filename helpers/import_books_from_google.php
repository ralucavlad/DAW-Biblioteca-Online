<?php
/**
 * Script for importing books from Google Books API
 * Adds books, authors, domains and associations between them
 */

// Database configuration - FOR CPANEL
$db_config = [
    'host' => 'localhost',
    'dbname' => 'rpirvule_test',
    'username' => 'rpirvule_test',
    'password' => 'MfFzHgSn67uFqECU92nd',
];

// Connect to database
try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✓ Conectat la baza de date\n";
} catch (PDOException $e) {
    die("✗ Eroare conexiune: " . $e->getMessage() . "\n");
}

// Categories to search in Google Books (ONLY BOOKS IN ROMANIAN)
$search_queries = [
    'fictiune' => 'Fictiune',
    'literatura romana' => 'Literatura Romana',
    'science fiction' => 'Science Fiction',
    'fantasy' => 'Fantasy',
    'mister' => 'Mister',
    'thriller' => 'Thriller',
    'istorie romania' => 'Istorie',
    'biografie' => 'Biografie',
    'dezvoltare personala' => 'Dezvoltare personala',
    'business management' => 'Business',
];

$total_books_to_import = 50;
$books_imported = 0;

echo "\n=== Începem importul cărților din Google Books API ===\n\n";

// Function to make request to Google Books API
function fetchGoogleBooks($query, $maxResults = 10) {
    $apiUrl = "https://www.googleapis.com/books/v1/volumes?q=" . urlencode($query) . 
              "&maxResults=" . $maxResults . 
              "&langRestrict=ro" .  // ONLY ROMANIAN LANGUAGE
              "&printType=books";
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        return json_decode($response, true);
    }
    
    return null;
}

// Function to find or create a domain
function getOrCreateDomeniu($pdo, $nume_domeniu) {
    $stmt = $pdo->prepare("SELECT domeniu_id FROM domeniu WHERE denumire = ?");
    $stmt->execute([$nume_domeniu]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['domeniu_id'];
    }
    
    // Create domain if it doesn't exist
    $stmt = $pdo->prepare("INSERT INTO domeniu (denumire) VALUES (?)");
    $stmt->execute([$nume_domeniu]);
    return $pdo->lastInsertId();
}

// Function to find or create an author
function getOrCreateAutor($pdo, $nume_autor) {
    if (empty($nume_autor)) {
        $nume_autor = 'Anonim';
    }
    
    $stmt = $pdo->prepare("SELECT autor_id FROM autor WHERE nume = ?");
    $stmt->execute([$nume_autor]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result['autor_id'];
    }
    
    // Create author if it doesn't exist
    $stmt = $pdo->prepare("INSERT INTO autor (nume) VALUES (?)");
    $stmt->execute([$nume_autor]);
    return $pdo->lastInsertId();
}

// Loop through each category
foreach ($search_queries as $query => $domeniu_nume) {
    if ($books_imported >= $total_books_to_import) {
        break;
    }
    
    echo "Căutare: '$query' (Domeniu: $domeniu_nume)\n";
    
    $books_per_query = min(10, $total_books_to_import - $books_imported);
    $data = fetchGoogleBooks($query, $books_per_query);
    
    if (!$data || !isset($data['items'])) {
        echo "  Nu s-au găsit cărți pentru '$query'\n\n";
        continue;
    }
    
    $domeniu_id = getOrCreateDomeniu($pdo, $domeniu_nume);
    
    foreach ($data['items'] as $item) {
        if ($books_imported >= $total_books_to_import) {
            break;
        }
        
        $volumeInfo = $item['volumeInfo'] ?? [];
        
        // Extract book information
        $title = $volumeInfo['title'] ?? 'Titlu necunoscut';
        $authors = $volumeInfo['authors'] ?? ['Anonim'];
        $description = $volumeInfo['description'] ?? null;
        $publisher = $volumeInfo['publisher'] ?? null;
        $publishedDate = $volumeInfo['publishedDate'] ?? null;
        $pageCount = $volumeInfo['pageCount'] ?? null;
        $language = $volumeInfo['language'] ?? 'ro';
        $isbn = null;
        
        // FILTER 1: Book MUST have a description
        if (empty($description)) {
            echo "  Omis (fără descriere): $title\n";
            continue;
        }
        
        // FILTER 2: Book MUST have a publisher
        if (empty($publisher)) {
            echo "  Omis (fără editură): $title\n";
            continue;
        }
        
        // Extract ISBN
        if (isset($volumeInfo['industryIdentifiers'])) {
            foreach ($volumeInfo['industryIdentifiers'] as $identifier) {
                if ($identifier['type'] === 'ISBN_13' || $identifier['type'] === 'ISBN_10') {
                    $isbn = $identifier['identifier'];
                    break;
                }
            }
        }
        
        // FILTER 3: Book MUST have an ISBN
        if (empty($isbn)) {
            echo "  Omis (fără ISBN): $title\n";
            continue;
        }
        
        // Extract cover URL (prefer large thumbnail)
        $cover_url = null;
        if (isset($volumeInfo['imageLinks'])) {
            $cover_url = $volumeInfo['imageLinks']['thumbnail'] ?? 
                        $volumeInfo['imageLinks']['smallThumbnail'] ?? null;
            
            // Replace http with https and zoom=1 for larger images
            if ($cover_url) {
                $cover_url = str_replace('http://', 'https://', $cover_url);
                $cover_url = str_replace('zoom=1', 'zoom=2', $cover_url);
            }
        }
        
        // FILTER 4: Check if image exists (not "image not available")
        if ($cover_url) {
            $ch = curl_init($cover_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true); // Only check header
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // Check if it's a valid image
            if ($httpCode !== 200 || strpos($contentType, 'image') === false) {
                echo "  Omis (imagine invalidă): $title\n";
                continue;
            }
        } else {
            echo "  Omis (fără imagine): $title\n";
            continue;
        }
        
        // Extract publication year
        $year = null;
        if ($publishedDate) {
            preg_match('/(\d{4})/', $publishedDate, $matches);
            $year = $matches[1] ?? null;
        }
        
        // Check if book already exists (based on ISBN or title)
        if ($isbn) {
            $stmt = $pdo->prepare("SELECT carte_id FROM carte WHERE isbn = ?");
            $stmt->execute([$isbn]);
            if ($stmt->fetch()) {
                echo "  Există deja: $title\n";
                continue;
            }
        }
        
        try {
            // Add first author
            $autor_id = getOrCreateAutor($pdo, $authors[0]);
            
            // Insert book
            $stmt = $pdo->prepare("
                INSERT INTO carte (
                    autor_id, denumire, isbn, descriere, editura, 
                    an_publicare, nr_pagini, limba, url_coperta,
                    nr_exemplare_totale, nr_exemplare_disponibile
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $autor_id,
                substr($title, 0, 200), // Limited to 200 characters
                $isbn,
                $description,
                substr($publisher, 0, 100),
                $year,
                $pageCount,
                $language,
                $cover_url,
                rand(1, 5), // Random total copies
                rand(0, 5)  // Random available copies
            ]);
            
            $carte_id = $pdo->lastInsertId();
            
            // Add additional authors to carte_autor
            foreach ($authors as $author_name) {
                $autor_id_extra = getOrCreateAutor($pdo, $author_name);
                
                $stmt = $pdo->prepare("
                    INSERT IGNORE INTO carte_autor (carte_id, autor_id) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$carte_id, $autor_id_extra]);
            }
            
            // Associate book with domain
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO carte_domeniu (carte_id, domeniu_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$carte_id, $domeniu_id]);
            
            $books_imported++;
            echo "  [$books_imported/$total_books_to_import] Adăugat: $title (Autor: {$authors[0]})\n";
            
            // Small pause to not overload the API
            usleep(200000); // 0.2 seconds
            
        } catch (PDOException $e) {
            echo "  ✗ Eroare la adăugare: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

echo "\n=== Import finalizat! ===\n";
echo "Total cărți importate: $books_imported\n";

// Display statistics
$stmt = $pdo->query("SELECT COUNT(*) as total FROM carte");
$total_books = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM autor");
$total_authors = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM domeniu");
$total_domains = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

echo "\nStatistici finale:\n";
echo "  - Total cărți în BD: $total_books\n";
echo "  - Total autori în BD: $total_authors\n";
echo "  - Total domenii în BD: $total_domains\n";
?>
