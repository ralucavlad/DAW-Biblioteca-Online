<!-- Random Books Section -->
<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-light mb-3">Cărți din Colecția Noastră</h2>
        <p class="text-muted">Descoperă câteva dintre titlurile disponibile</p>
    </div>
    
    <div class="row">
        <?php 
            if (!empty($random_books)): 
                foreach ($random_books as $book): 
        ?>
            <div class="col-md-4 mb-4">
                <div class="book-card">
                    <div class="book-card-img-wrapper">
                        <img src="<?php echo htmlspecialchars($book['url_coperta']) ?>" 
                             alt="<?php echo htmlspecialchars($book['denumire']); ?>"
                             class="book-card-img">
                    </div>
                    <div class="book-card-body">
                        <h5 class="book-card-title"><?php echo htmlspecialchars($book['denumire']); ?></h5>
                        <?php if ($book['autor_nume']): ?>
                        <p class="book-card-author">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($book['autor_nume']); ?>
                        </p>
                        <?php endif; ?>
                        <p class="book-card-description">
                            <?php 
                            $description = $book['descriere'];
                            echo htmlspecialchars(mb_substr($description, 0, 200)) . (mb_strlen($description) > 200 ? '...' : '');
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php 
                endforeach; 
                endif; 
            ?>
    </div>    
</div>
