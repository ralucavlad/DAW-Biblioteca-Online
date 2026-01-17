let allRentals = [];

$(document).ready(function() {
    loadRentals();
});

function loadRentals() {
    $('#noRentals').hide();
    $('#rentalsContainer').empty();

    fetch('../api/rentals/get_my_rentals.php')
    .then(response => response.json())
    .then(data => {

        if (data.success) {
            allRentals = data.rentals;
            
            // Count active rentals and update limit info
            const activeRentalsCount = data.rentals.filter(r => r.stare === 'activa').length;
            updateRentalLimitInfo(activeRentalsCount);
            
            if (data.count > 0) {
                displayRentals(allRentals);
            } else {
                $('#noRentals').show();
            }
        } else {
            showNotification(data.error || 'Eroare la încărcarea închirierilor', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Eroare la conectarea la server', 'error');
    });
}

function displayRentals(rentals) {
    const $container = $('#rentalsContainer');
    let html = '';

    rentals.forEach(rental => {
        const dateInchiriere = new Date(rental.data_inchiriere);
        const dateScadenta = new Date(rental.data_scadenta);
        
        const formattedInchiriere = dateInchiriere.toLocaleDateString('ro-RO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const formattedScadenta = dateScadenta.toLocaleDateString('ro-RO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const coverUrl = rental.url_coperta;
        
        // Status badge
        let statusBadge = '';
        
        if (rental.stare === 'activa') {            
            statusBadge = '<span class="status-badge badge bg-success"><i class="fas fa-book-reader me-1"></i>Activă</span>';            
        } else if (rental.stare === 'returnata') {
            statusBadge = '<span class="status-badge badge bg-secondary"><i class="fas fa-check-circle me-1"></i>Returnată</span>';
        }

        html += `
            <div class="rental-card">
                <div class="row">
                    <div class="col-auto">
                        <img src="${escapeHtml(coverUrl)}" 
                             alt="${escapeHtml(rental.carte_denumire)}" 
                             class="book-thumbnail"
                             onclick="viewBook(${rental.carte_id})">
                    </div>
                    <div class="col">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="book-info">
                                <h5 onclick="viewBook(${rental.carte_id})">${escapeHtml(rental.carte_denumire)}</h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-user me-1"></i>${escapeHtml(rental.autori)}
                                </p>
                                <div class="rental-status mb-2">
                                    ${statusBadge}
                                </div>
                            </div>
                            <div class="text-end">
                                <button class="btn btn-sm btn-custom" onclick="viewBook(${rental.carte_id})">
                                    <i class="fas fa-eye"></i> Vezi Carte
                                </button>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="row text-muted small">
                                <div class="col-md-4">
                                    <p class="mb-1">
                                        <i class="fas fa-calendar-plus me-1"></i>
                                        <strong>Închiriat:</strong> ${formattedInchiriere}
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-1">
                                        <i class="fas fa-calendar-times me-1"></i>
                                        <strong>Scadență:</strong> ${formattedScadenta}
                                    </p>
                                </div>                                
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

function updateRentalLimitInfo(activeCount) {
    const $limitInfo = $('#rentalLimitInfo');
    const $activeCount = $('#activeCount');
    const $remainingSlots = $('#remainingSlots');
    const $limitMessage = $('#limitMessage');
    
    $activeCount.text(activeCount);
    const remaining = 3 - activeCount;
    $remainingSlots.text(remaining);
    
    if (activeCount > 0) {
        $limitInfo.show();
        
        if (remaining === 0) {
            $limitInfo.attr('class', 'alert alert-warning mb-4');
            $limitMessage.html('Ai atins limita maximă! Returnează o carte pentru a închiria alta.');
        } else if (remaining === 1) {
            $limitInfo.attr('class', 'alert alert-info mb-4');
            $limitMessage.html(`Poți închiria încă <strong>${remaining}</strong> carte.`);
        } else {
            $limitInfo.attr('class', 'alert alert-info mb-4');
            $limitMessage.html(`Poți închiria încă <strong>${remaining}</strong> cărți.`);
        }
    } else {
        $limitInfo.hide();
    }
}