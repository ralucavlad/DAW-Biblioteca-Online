$(document).ready(function() {
    // Character counters
    $('#subiect').on('input', function() {
        $('#subiectCount').text(this.value.length);
    });

    $('#mesaj').on('input', function() {
        $('#mesajCount').text(this.value.length);
    });

    // Form submission
    $('#contactForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true);
        submitBtn.html('');

        const formData = new FormData();
        formData.append('subiect', $('#subiect').val());
        formData.append('mesaj', $('#mesaj').val());

        fetch('api/contact/send_contact_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                $('#successMessage').show();
                
                // Reset form
                $('#subiect').val('');
                $('#mesaj').val('');
                $('#subiectCount').text('0');
                $('#mesajCount').text('0');
                                
            } else {
                alert('Eroare: ' + (data.error || 'Nu s-a putut trimite mesajul'));
            }
        })
        .catch(error => {
            console.error('Eroare:', error);
            alert('Eroare la trimiterea mesajului. Te rugăm să încerci din nou.');
        })
        .finally(() => {
            submitBtn.prop('disabled', false);
            submitBtn.html(originalText);
        });
    });
});

/**
 * Display messages
 */
function displayMessages(messages) {
    let html = '';
    messages.slice(0, 5).forEach(msg => {
        const date = new Date(msg.data_trimitere);
        const formattedDate = date.toLocaleDateString('ro-RO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        let statusBadge = '';
        if (msg.stare === 'nou') {
            statusBadge = '<span class="badge bg-warning text-dark">Nou</span>';
        } else if (msg.stare === 'citit') {
            statusBadge = '<span class="badge bg-info">Citit</span>';
        } else {
            statusBadge = '<span class="badge bg-success">Răspuns trimis</span>';
        }

        html += `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">${escapeHtml(msg.subiect)}</h6>
                        ${statusBadge}
                    </div>
                    <p class="text-muted small mb-2">
                        <i class="far fa-clock me-1"></i>${formattedDate}
                    </p>
                    <p class="mb-0">${escapeHtml(msg.mesaj.substring(0, 150))}${msg.mesaj.length > 150 ? '...' : ''}</p>
                    ${msg.raspuns ? `
                        <div class="alert alert-success mt-3 mb-0">
                            <strong>Răspuns:</strong><br>
                            ${escapeHtml(msg.raspuns)}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    });
}
