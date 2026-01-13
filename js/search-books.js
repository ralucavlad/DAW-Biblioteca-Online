$(document).ready(function() {
    const $searchForm = $('#searchForm');
    const $booksContainer = $('#booksContainer');
    const $noResults = $('#noResults');
    const $resultCount = $('#resultCount');
    const $searchTitle = $('#searchTitle');
    
    // Focus on title input by default
    $searchTitle.focus();
    
    // Load all books on page load
    loadBooks();
    
    // Search form submit
    $searchForm.on('submit', function(e) {
        e.preventDefault();
        loadBooks();
    });
        
    function loadBooks() {
        const formData = new FormData($searchForm[0]);
        const params = new URLSearchParams(formData);
        
        $booksContainer.empty();
        $noResults.hide();
        
        fetch('../api/books/get_books.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                
                if (data.success && data.data.length > 0) {
                    $resultCount.text(`${data.count} ${data.count === 1 ? 'carte găsită' : 'cărți găsite'}`);
                    displayBooks(data.data);
                } else {
                    $noResults.show();
                    $resultCount.text('0 cărți găsite');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Eroare la încărcarea cărților. Te rugăm să încerci din nou.', 'error');
            });
    }
    
    function displayBooks(books) {
        books.forEach(book => {
            const card = createBookCard(book);
            $booksContainer.append(card);
        });
        
        // Add click event to cards
        $('.book-card').on('click', function(e) {
            // Don't navigate if clicking the button
            if (!$(e.target).closest('.btn').length) {
                const bookId = $(this).data('book-id');
                window.location.href = `book-details.php?id=${bookId}`;
            }
        });
    }
    
    function createBookCard(book) {
        const coverUrl = book.url_coperta ;
        const authors = book.autori;
        const available = book.nr_exemplare_disponibile || 0;
        const availableBadge = available > 0 
            ? `<span class="badge bg-success mb-2"><i class="fas fa-check-circle me-1"></i>Disponibil (${available})</span>`
            : `<span class="badge bg-danger mb-2"><i class="fas fa-times-circle me-1"></i>Indisponibil</span>`;
        
        let extraInfo = '';
        if (book.editura || book.an_publicare) {
            extraInfo = '<p class="card-text small text-muted mb-2">';
            if (book.editura) {
                extraInfo += `<i class="fas fa-building me-1"></i>${escapeHtml(book.editura)}`;
            }
            if (book.an_publicare) {
                extraInfo += `<span class="ms-2"><i class="fas fa-calendar me-1"></i>${book.an_publicare}</span>`;
            }
            extraInfo += '</p>';
        }
        
        return `
            <div class="col">
                <div class="card h-100 book-card shadow-sm" data-book-id="${book.carte_id}">
                    <div class="book-cover-wrapper">
                        <img src="${escapeHtml(coverUrl)}" 
                             class="card-img-top book-cover" 
                             alt="${escapeHtml(book.denumire)}"
                             onerror="this.src='../img/no-cover.jpg'">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title book-title">${escapeHtml(book.denumire)}</h5>
                        <p class="card-text text-muted small book-author">
                            <i class="fas fa-user me-1"></i>${escapeHtml(authors)}
                        </p>
                        ${extraInfo}
                        <div class="mt-auto">
                            ${availableBadge}
                            <a href="book-details.php?id=${book.carte_id}" 
                               class="btn btn-outline-dark btn-sm w-100">
                                <i class="fas fa-info-circle me-1"></i>Detalii
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
});