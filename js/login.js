// Login.js - Logic for 2-step authentication

$(document).ready(function() {
    // DOM Elements
    const $step1Form = $('#step1-form');
    const $step2Form = $('#step2-form');
    const $registerForm = $('#register-form');
    const $alertContainer = $('#alert-container');
    let sessionToken = '';

    // Function to display alerts on main page
    function showAlert(message) {
        $alertContainer.html(`
            <div class="alert alert-danger alert-dismissible fade show">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
    }

    // Function to display alerts in registration modal
    function showRegisterAlert(message, type = 'info') {
        const $container = $('#register-alert-container');
        const $messageSpan = $('#register-alert-message');
        if (!$container.length || !$messageSpan.length) return;
        
        $messageSpan.text(message);
        $container.removeClass().addClass(`alert alert-${type}`).show();
        if (type === 'success') setTimeout(() => $container.hide(), 5000);
    }

    // STEP 1: Submit Email and Password
    $step1Form.on('submit', async function(e) {
        e.preventDefault();
        
        const email = $('#email').val().trim();
        const password = $('#password').val();
        
        // Validate email and password
        if (!document.getElementById('email').checkValidity()) {
            showAlert('Adresa de email nu este validă!');
            return;
        }
        if (!password) {
            showAlert('Parola este obligatorie!');
            return;
        }
        
        // Validate reCAPTCHA
        const recaptchaResponse = grecaptcha.getResponse(0);
        if (!recaptchaResponse) {
            showAlert('Vă rugăm să completați reCAPTCHA!');
            return;
        }

        try {
            const params = new URLSearchParams({email, password, 'g-recaptcha-response': recaptchaResponse});
            const response = await fetch('api/auth/verify_email.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: params
            });
            const data = await response.json();

            if (data.success) {
                if (data.skip_verification) {
                    window.location.href = data.redirect || 'dashboard.php';
                    return;
                }
                sessionToken = data.session_token;
                goToStep2();
            } else {
                showAlert(data.message || 'Eroare la trimiterea codului!');
                grecaptcha.reset();
            }
        } catch (error) {
            console.error('Eroare:', error);
            showAlert('Eroare de conexiune la server!');
            grecaptcha.reset();
        }
    });

    // STEP 2: Submit Verification Code
    $step2Form.on('submit', async function(e) {
        e.preventDefault();
        const code = $('#verification-code').val().trim();
        if (code.length !== 6) {
            showAlert('Codul trebuie să conțină 6 cifre!');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('code', code);
            formData.append('session_token', sessionToken);
            const response = await fetch('api/auth/verify_code.php', {method: 'POST', body: formData});
            const data = await response.json();

            if (data.success) {
                window.location.href = data.redirect_url || 'dashboard.php';
            } else {
                showAlert(data.message || 'Cod invalid!');
            }
        } catch (error) {
            console.error('Eroare:', error);
            showAlert('Eroare de conexiune la server!');
        }
    });

    // Transition to Step 2
    function goToStep2() {
        $step1Form.addClass('hidden');
        $step2Form.removeClass('hidden');
        $('#verification-code').focus();
    }

    // Registration Form
    $registerForm.on('submit', async function(e) {
        e.preventDefault();
        
        // Validate passwords match
        if ($('#reg-password').val() !== $('#reg-password-confirm').val()) {
            showRegisterAlert('Parolele nu coincid!', 'danger');
            return;
        }
        
        const formData = new FormData($registerForm[0]);
        formData.append('action', 'register');

        // Validate reCAPTCHA
        if (typeof grecaptcha === 'undefined') {
            showRegisterAlert('reCAPTCHA nu s-a încărcat! Reîncărcați pagina.', 'danger');
            return;
        }
        const recaptchaContainer = document.getElementById('recaptcha-register');
        const widgetId = recaptchaContainer ? parseInt(recaptchaContainer.dataset.widgetId || '0') : 0;
        const recaptchaResponse = grecaptcha.getResponse(widgetId);
        if (!recaptchaResponse) {
            showRegisterAlert('Vă rugăm să completați reCAPTCHA!', 'danger');
            return;
        }
        formData.append('g-recaptcha-response', recaptchaResponse);

        try {
            const response = await fetch('api/auth/register.php', {method: 'POST', body: formData});
            const data = await response.json();

            if (data.success) {
                showRegisterAlert('Cerere trimisă! Verificați emailul pentru activare.', 'success');
                $registerForm[0].reset();
                setTimeout(() => bootstrap.Modal.getInstance($('#registerModal')[0]).hide(), 2000);
            } else {
                showRegisterAlert(data.message || 'Eroare la înregistrare!', 'danger');
            }
        } catch (error) {
            console.error('Error:', error);
            showRegisterAlert('Eroare de conexiune la server!', 'danger');
        }
    });

    // Load companies for registration dropdown
    async function loadCompanies() {
        try {
            const response = await fetch('api/admin/get_companies.php');
            const data = await response.json();
            if (data.success && data.companies) {
                const $select = $('#reg-companie');
                if (!$select.length) return;
                $select.html('<option value="">Selectează compania...</option>');
                data.companies.forEach(company => {
                    $select.append(`<option value="${company.companie_id}">${company.nume}</option>`);
                });
            }
        } catch (error) {
            console.error('Error loading companies:', error);
        }
    }
    loadCompanies();
});

// Save widget IDs when reCAPTCHA loads
window.onRecaptchaLoad = function() {
    document.querySelectorAll('.g-recaptcha').forEach((container, index) => {
        container.dataset.widgetId = index;
    });
};
