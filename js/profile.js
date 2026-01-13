document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('updateProfileForm');
    
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            formData.append('action', 'update_profile');
            
            try {
                const response = await fetch('/api/users/update_profile.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message);
                }
            } catch (error) {
                showAlert('danger', 'A aparut o eroare la actualizarea profilului. Va rugam incercati din nou.');
            }
        });
    }
});

function showAlert(type, message) {
    const alertContainer = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.appendChild(alert);
    
    // Auto dismiss success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            alert.remove();
        }, 5000);
    }
}
