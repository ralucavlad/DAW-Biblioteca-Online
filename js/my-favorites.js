$(document).ready(function() {
    loadFavorites();
});

function loadFavorites() {
    $('#noFavorites').hide();
    $('#favoritesContainer').empty();

    fetch('../api/favorites/get_my_favorites.php')
    .then(response => response.json())
    .then(data => {

        if (data.success) {
            if (data.count > 0) {
                displayFavorites(data.favorites);
            } else {
                $('#noFavorites').show();
            }
        } else {
            showNotification(data.error || 'Eroare la încărcarea favoritelor', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Eroare la conectarea la server', 'error');
    });
}

function displayFavorites(favorites) {
    const $container = $('#favoritesContainer');
    let html = '';

    favorites.forEach(favorite => {
        const date = new Date(favorite.data_adaugare);
        const formattedDate = date.toLocaleDateString('ro-RO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        const coverUrl = favorite.url_coperta;
        const available = favorite.nr_exemplare_disponibile > 0;
        const availabilityBadge = available 
            ? `<span class="availability-badge badge bg-success"><i class="fas fa-check-circle me-1"></i>Disponibil</span>`
            : `<span class="availability-badge badge bg-danger"><i class="fas fa-times-circle me-1"></i>Indisponibil</span>`;

        html += `
            <div class="favorite-card">
                <div class="row">
                    <div class="col-auto">
                        <img src="${escapeHtml(coverUrl)}" 
                                alt="${escapeHtml(favorite.carte_denumire)}" 
                                class="book-thumbnail"
                                onclick="viewBook(${favorite.carte_id})">
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="book-info">
                                <h5 onclick="viewBook(${favorite.carte_id})">${escapeHtml(favorite.carte_denumire)}</h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-user me-1"></i>${escapeHtml(favorite.autori)}
                                </p>
                                ${availabilityBadge}
                                <div class="mt-2">
                                    <small class="favorite-date">
                                        <i class="fas fa-heart me-1"></i>Adăugat la ${formattedDate}
                                    </small>
                                </div>
                            </div>
                            <div class="favorite-actions">
                                <button class="btn btn-sm btn-custom" onclick="viewBook(${favorite.carte_id})">
                                    <i class="fas fa-eye"></i> Vezi Detalii
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeFavorite(${favorite.carte_id})">
                                    <i class="fas fa-heart-broken"></i> Elimină
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    $container.html(html);
}

function viewBook(bookId) {
    window.location.href = `book-details.php?id=${bookId}`;
}

function removeFavorite(bookId) {
    if (!confirm('Sigur dorești să elimini această carte din favorite?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'toggle');
    formData.append('book_id', bookId);

    fetch('../api/favorites/toggle_favorite.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Cartea a fost eliminată din favorite!', 'success');
            loadFavorites();
        } else {
            showNotification(data.error || 'Eroare la eliminarea din favorite', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Eroare la conectarea la server', 'error');
    });
}