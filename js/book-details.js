// Global variables will be set by the PHP page: bookId, isLoggedIn, userId

$(document).ready(function() {
    loadBookDetails();
    loadReviews();
    
    // Check favorite and rental status on load
    if (isLoggedIn) {
        checkFavoriteStatus();
        loadUserReview();
        checkRentalStatus();
        checkRentalLimit();
    }
    
    // Event listeners
    $('#rentBtn').on('click', function() {
        if (!isLoggedIn) {
            showNotification('Trebuie să fii autentificat pentru a închiria cărți!', 'error');
            setTimeout(() => window.location.href = '../login.php', 1500);
            return;
        }
        rentBook();
    });
    
    $('#favoriteBtn').on('click', function() {
        if (!isLoggedIn) {
            showNotification('Trebuie să fii autentificat pentru a adăuga la favorite!', 'error');
            setTimeout(() => window.location.href = '../login.php', 1500);
            return;
        }
        toggleFavorite();
    });
    
    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        submitReview();
    });
});

function loadBookDetails() {
    fetch(`../api/books/get_book_details.php?id=${bookId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayBookDetails(data.data);
            } else {
                console.error('Eroare la încărcarea detaliilor cărții:', data.error);
            }
        })
        .catch(error => {
            console.error('Eroare la încărcarea detaliilor cărții:', error);
        }); 
}

function displayBookDetails(book) {
    $('#bookDetailsContainer').show();

    // Set book cover
    const coverUrl = book.url_coperta ;
    $('#bookCover').attr('src', coverUrl).attr('alt', book.denumire);

    // Set title
    $('#bookTitle').text(book.denumire);
    document.title = book.denumire + ' - Biblioteca Online';

    // Set rating
    $('#avgRating').text(book.avg_rating);
    $('#reviewsCount').text(`(${book.total_reviews} ${book.total_reviews === 1 ? 'recenzie' : 'recenzii'})`);
    displayStars(book.avg_rating, 'ratingStars');

    // Set book meta information
    const authorsText = book.autori;
    const domainsHtml = book.domenii;

    const bookMetaHtml = `
        <div class="book-meta-info">
            <p class="mb-2">
                <strong>Autor:</strong> 
                ${escapeHtml(authorsText)}
            </p>
            <p class="mb-2">
                <strong>ISBN:</strong> 
                ${escapeHtml(book.isbn || 'N/A')}
            </p>
            <p class="mb-2">
                <strong>Editura:</strong> 
                ${escapeHtml(book.editura || 'N/A')}
            </p>
            <p class="mb-2">
                <strong>An publicare:</strong> 
                ${escapeHtml(book.an_publicare || 'N/A')}
            </p>
            <p class="mb-2">
                <strong>Pagini:</strong> 
                ${escapeHtml(book.nr_pagini || 'N/A')}
            </p>
            <p class="mb-2">
                <strong>Limbă:</strong> 
                ${escapeHtml(book.limba || 'N/A')}
            </p>
            <p class="mb-2">
                <strong>Format:</strong> 
                ${escapeHtml(book.format || 'N/A')}
            </p>
            <p class="mb-0">
                <strong>Domenii:</strong> 
                ${domainsHtml}
            </p>
        </div>
    `;

    $('#bookMeta').html(bookMetaHtml);
   
    $('#bookDescription').text(book.descriere);
    

    // Set availability badge
    const available = book.nr_exemplare_disponibile > 0;
    const $availabilityBadge = $('#availabilityBadge');
    if (available) {
        $availabilityBadge.attr('class', 'badge bg-success').html('<i class="fas fa-check-circle me-1"></i>Disponibil');
    } else {
        $availabilityBadge.attr('class', 'badge bg-danger').html('<i class="fas fa-times-circle me-1"></i>Indisponibil');
        // Disable rent button if not available
        $('#rentBtn').prop('disabled', true).html('<i class="fas fa-times-circle me-2"></i>Indisponibil');
    }
}

function displayStars(rating, containerId) {
    let starsHtml = '';
    
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(rating)) {
            starsHtml += '<i class="fas fa-star"></i>';
        } else if (i - 0.5 <= rating) {
            starsHtml += '<i class="fas fa-star-half-alt"></i>';
        } else {
            starsHtml += '<i class="far fa-star"></i>';
        }
    }
    
    $(`#${containerId}`).html(starsHtml);
}

// Check if book is in favorites
function checkFavoriteStatus() {
    const formData = new FormData();
    formData.append('action', 'check');
    formData.append('book_id', bookId);

    fetch('../api/favorites/toggle_favorite.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.is_favorite) {
            updateFavoriteButton(true);
        }
    })
    .catch(error => {
        console.error('Error checking favorite status:', error);
    });
}

// Toggle favorite status
function toggleFavorite() {
    const btn = document.getElementById('favoriteBtn');
    btn.disabled = true;

    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('book_id', bookId);

    fetch('../api/favorites/toggle_favorite.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const isFavorite = data.action === 'added';
            updateFavoriteButton(isFavorite);
            
            // Show success message
            showNotification(data.message, 'success');
        } else {
            showNotification(data.error || 'Eroare la actualizarea favoritelor', 'error');
        }
    })
    .catch(error => {
        console.error('Error toggling favorite:', error);
        showNotification('Eroare la conectarea la server', 'error');
    })
    .finally(() => {
        btn.disabled = false;
    });
}

// Update favorite button appearance
function updateFavoriteButton(isFavorite) {
    const btn = document.getElementById('favoriteBtn');
    const icon = btn.querySelector('i');
    
    if (isFavorite) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        btn.classList.add('active');
        btn.innerHTML = '<i class="fas fa-heart me-2"></i>Elimină din Favorite';
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        btn.classList.remove('active');
        btn.innerHTML = '<i class="far fa-heart me-2"></i>Adaugă la Favorite';
    }
}

// Submit review
function submitReview() {
    const form = document.getElementById('reviewForm');
    const ratingInputs = form.querySelectorAll('input[name="rating"]');
    const commentTextarea = document.getElementById('reviewText');
    
    // Get selected rating
    let selectedRating = 0;
    ratingInputs.forEach(input => {
        if (input.checked) {
            selectedRating = parseInt(input.value);
        }
    });
    
    // Validate rating
    if (selectedRating === 0) {
        showNotification('Te rugăm să selectezi o evaluare (1-5 stele)', 'error');
        return;
    }
    
    const comment = commentTextarea.value.trim();
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('book_id', bookId);
    formData.append('rating', selectedRating);
    formData.append('comment', comment);
    
    fetch('../api/reviews/submit_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            // Update rating display
            $('#avgRating').text(data.avg_rating);
            $('#reviewsCount').text(`(${data.total_reviews} ${data.total_reviews === 1 ? 'recenzie' : 'recenzii'})`);
            displayStars(data.avg_rating, 'ratingStars');
            
            // Reset form
            form.reset();
            
            // Reload reviews to show the new/updated one
            loadReviews();
        } else {
            showNotification(data.error || 'Eroare la trimiterea recenziei', 'error');
        }
    })
    .catch(error => {
        console.error('Eroare la trimiterea recenziei:', error);
        showNotification('Eroare la conectarea la server', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
    });
}

// Load user's existing review
function loadUserReview() {
    fetch(`../api/reviews/get_user_review_for_book.php?book_id=${bookId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.has_review) {
            // Pre-select rating
            const ratingInput = document.getElementById(`star${data.rating}`);
            if (ratingInput) {
                ratingInput.checked = true;
            }
            
            // Pre-fill comment
            const commentTextarea = document.getElementById('reviewText');
            if (commentTextarea && data.comment) {
                commentTextarea.value = data.comment;
            }
            
            // Show info message
            const form = document.getElementById('reviewForm');
            if (form) {
                const infoDiv = document.createElement('div');
                infoDiv.className = 'alert alert-info mb-3';
                infoDiv.innerHTML = 'Ai deja o recenzie pentru această carte. Modificările tale vor actualiza recenzia existentă.';
                form.insertBefore(infoDiv, form.firstChild);
            }
        }
    })
    .catch(error => {
        console.error('Eroare la încărcarea recenziei utilizatorului:', error);
    });
}

// Load all reviews for this book
function loadReviews() {
    const reviewsList = document.getElementById('reviewsList');
    reviewsList.innerHTML = '';
    
    fetch(`../api/reviews/get_book_reviews.php?book_id=${bookId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.count > 0) {
                displayReviews(data.reviews);
            } else {
                reviewsList.innerHTML = `
                    <p class="text-muted text-center py-3">
                        Nu există încă recenzii pentru această carte.
                    </p>
                `;
            }
        } else {
            reviewsList.innerHTML = `
                <div class="alert alert-danger">
                    Eroare la încărcarea recenziilor.
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Eroare la încărcarea recenziilor:', error);
        reviewsList.innerHTML = `
            <div class="alert alert-danger">
                Eroare la încărcarea recenziilor.
            </div>
        `;
    });
}

// Display reviews
function displayReviews(reviews) {
    const reviewsList = document.getElementById('reviewsList');
    let html = '<div class="reviews-container mt-4">';
    
    reviews.forEach(review => {
        const stars = generateStarsHTML(review.evaluare);
        const date = new Date(review.data_recenzie);
        const formattedDate = date.toLocaleDateString('ro-RO', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        html += `
            <div class="review-item mb-4 pb-4 border-bottom">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong class="text-dark">${escapeHtml(review.nume)} ${escapeHtml(review.prenume)}</strong>
                        <div class="review-stars mt-1">${stars}</div>
                    </div>
                    <small class="text-muted">${formattedDate}</small>
                </div>
                ${review.comentariu ? `
                    <p class="review-comment mb-0 mt-2">${escapeHtml(review.comentariu)}</p>
                ` : ''}
            </div>
        `;
    });
    
    html += '</div>';
    reviewsList.innerHTML = html;
}

// Rent book function
function rentBook() {
    const btn = document.getElementById('rentBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '';

    const formData = new FormData();
    formData.append('book_id', bookId);

    fetch('../api/rentals/rent_book.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success modal
            $('#modalBookName').text(data.book_name);
            $('#modalDataPrimire').text(data.formatted_primire);
            $('#modalDataScadenta').text(data.formatted_scadenta);
            
            const modal = new bootstrap.Modal($('#rentalModal')[0]);
            modal.show();
            
            // Reload book details and check statuses
            loadBookDetails();
            checkRentalStatus();
            checkRentalLimit();
        } else {
            showNotification(data.error || 'Eroare la închirierea cărții', 'error');
        }
    })
    .catch(error => {
        console.error('Eroare la închirierea cărții:', error);
        showNotification('Eroare la conectarea la server', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

// Check if user has active rental for this book
function checkRentalStatus() {
    fetch(`../api/rentals/check_rental.php?book_id=${bookId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success && data.has_active_rental) {
            const rentBtn = document.getElementById('rentBtn');
            rentBtn.disabled = true;
            rentBtn.innerHTML = 'Deja Închiriată';
            rentBtn.classList.add('btn-secondary');
            rentBtn.classList.remove('btn-light');
        }
    })
    .catch(error => {
        console.error('Eroare la verificarea statusului închirierii:', error);
    });
}

// Check if user has reached rental limit
function checkRentalLimit() {
    fetch(`../api/rentals/check_rental_limit.php`)
    .then(response => response.json())
    .then(data => {
        if (data.success && !data.can_rent) {
            const rentBtn = document.getElementById('rentBtn');
            if (!rentBtn.disabled) { // Don't override "Deja Închiriată" status
                rentBtn.disabled = true;
                rentBtn.innerHTML = 'Limită Atinsă (3/3)';
                rentBtn.classList.add('btn-secondary');
                rentBtn.classList.remove('btn-light');
                rentBtn.title = 'Ai atins limita maximă de 3 cărți închiriate simultan';
            }
        }
    })
    .catch(error => {
        console.error('Eroare la verificarea limitei de închiriere:', error);
    });
}

