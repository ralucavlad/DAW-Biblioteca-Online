/**
 * Common JavaScript utilities
 * Funcții globale reutilizabile în întreaga aplicație
 */

/**
 * Afișează o notificare temporară în partea de sus a paginii
 * @param {string} message - Mesajul de afișat
 * @param {string} type - Tipul notificării: 'success' sau 'error'
 */
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'check-circle' : 'exclamation-circle';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
             style="z-index: 9999; min-width: 300px;" role="alert">
            <i class="fas fa-${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    setTimeout(function() {
        $('.alert').first().remove();
    }, 3000);
}

/**
 * Escape-uiește caractere HTML pentru a preveni XSS
 * @param {string} text - Textul de escape-uit
 * @returns {string} - Text cu caractere HTML escape-uite
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Generează HTML pentru afișarea stelelor de rating
 * @param {number} rating - Rating-ul de la 1 la 5
 * @returns {string} - HTML cu icoane de stele (pline/goale)
 */
function generateStarsHTML(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star text-warning"></i>';
        } else {
            stars += '<i class="far fa-star text-warning"></i>';
        }
    }
    return stars;
}