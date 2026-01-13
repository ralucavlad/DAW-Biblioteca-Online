<?php
/**
 * Parametri:
 * - $book: array cu datele cartii (carte_id, denumire, autori, url_coperta, editura, an_publicare, nr_exemplare_disponibile)
 */

$bookId = $book['carte_id'];
$title = $book['denumire'];
$authors = $book['autori'];
$coverUrl = $book['url_coperta'];
$publisher = $book['editura'] ?? '';
$year = $book['an_publicare'] ?? '';
$available = $book['nr_exemplare_disponibile'] ?? 0;
?>

<div class="col">
    <div class="card h-100 book-card shadow-sm">
        <div class="book-cover-wrapper">
            <img src="<?php echo htmlspecialchars($coverUrl); ?>" 
                 class="card-img-top book-cover" 
                 alt="<?php echo htmlspecialchars($title); ?>">
        </div>
        <div class="card-body d-flex flex-column">
            <h5 class="card-title book-title"><?php echo htmlspecialchars($title); ?></h5>
            <p class="card-text text-muted small book-author">
                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($authors); ?>
            </p>
            
            <?php if ($publisher || $year): ?>
            <p class="card-text small text-muted mb-2">
                <?php if ($publisher): ?>
                    <i class="fas fa-building me-1"></i><?php echo htmlspecialchars($publisher); ?>
                <?php endif; ?>
                <?php if ($year): ?>
                    <span class="ms-2"><i class="fas fa-calendar me-1"></i><?php echo htmlspecialchars($year); ?></span>
                <?php endif; ?>
            </p>
            <?php endif; ?>
            
            <div class="mt-auto">
                <?php if ($available > 0): ?>
                    <span class="badge bg-success mb-2">
                        <i class="fas fa-check-circle me-1"></i>Disponibil (<?php echo $available; ?>)
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger mb-2">
                        <i class="fas fa-times-circle me-1"></i>Indisponibil
                    </span>
                <?php endif; ?>
                
                <a href="book-details.php?id=<?php echo $bookId; ?>" 
                   class="btn btn-outline-dark btn-sm w-100">
                    <i class="fas fa-info-circle me-1"></i>Detalii
                </a>
            </div>
        </div>
    </div>
</div>
