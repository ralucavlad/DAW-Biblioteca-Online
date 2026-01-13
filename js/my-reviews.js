$(document).ready(function() {
    loadReviews();
});

function loadReviews() {
    $('#noReviews').hide();
    $('#reviewsContainer').empty();

    fetch('../api/reviews/get_all_my_reviews.php')
    .then(response => response.json())
    .then(data => {

        if (data.success) {
            if (data.count > 0) {
                displayReviews(data.reviews);
            } else {
                $('#noReviews').show();
            }
        } else {
            showNotification(data.error || 'Eroare la încărcarea recenziilor', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Eroare la conectarea la server', 'error');
    });
}

function displayReviews(reviews) {
    const $container = $('#reviewsContainer');
    let html = '';

    reviews.forEach(review => {
        const stars = generateStarsHTML(review.evaluare);
        const date = new Date(review.data_recenzie);
        const formattedDate = date.toLocaleDateString('ro-RO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        const coverUrl = review.url_coperta;

        html += `
            <div class="review-card">
                <div class="row">
                    <div class="col-auto">
                        <img src="${escapeHtml(coverUrl)}" 
                             alt="${escapeHtml(review.carte_denumire)}" 
                             class="book-thumbnail">
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="book-info">
                                <h5>${escapeHtml(review.carte_denumire)}</h5>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-user me-1"></i>${escapeHtml(review.autori || 'Autor necunoscut')}
                                </p>
                                <div class="review-stars">${stars}</div>
                                <small class="review-date">
                                    <i class="far fa-clock me-1"></i>${formattedDate}
                                </small>
                            </div>
                            <div class="review-actions">
                                <button class="btn btn-sm btn-custom" onclick="editReview(${review.carte_id})">
                                    <i class="fas fa-edit"></i> Editează
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(${review.recenzie_id})">
                                    <i class="fas fa-trash"></i> Șterge
                                </button>
                            </div>
                        </div>
                        ${review.comentariu ? `
                            <div class="review-comment mt-3">
                                <strong>Comentariul tău:</strong><br>
                                ${escapeHtml(review.comentariu)}
                            </div>
                        ` : '<p class="text-muted fst-italic mt-2">Fără comentariu</p>'}
                    </div>
                </div>
            </div>
        `;
    });

    $container.html(html);
}

function editReview(bookId) {
    window.location.href = `book-details.php?id=${bookId}`;
}

function deleteReview(reviewId) {
    if (!confirm('Sigur dorești să ștergi această recenzie?')) {
        return;
    }

    const formData = new FormData();
    formData.append('review_id', reviewId);

    fetch('../api/reviews/delete_review.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadReviews();
        } else {
            showNotification(data.error || 'Eroare la ștergerea recenziei', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Eroare la conectarea la server', 'error');
    });
}