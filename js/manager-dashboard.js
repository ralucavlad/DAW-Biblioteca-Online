// Manager Dashboard - Employee Management
let activityChart = null;

document.addEventListener('DOMContentLoaded', function() {
    loadManagerStats();
    loadEmployeeActivity();
});

/**
 * Load manager statistics
 */
function loadManagerStats() {
    fetch('../api/manager/get_company_stats.php', {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totalEmployees').textContent = data.stats.total_employees || 0;
            document.getElementById('activeEmployees').textContent = data.stats.active_today || 0;
            document.getElementById('totalBooksRented').textContent = data.stats.total_books_rented || 0;
            document.getElementById('totalReviews').textContent = data.stats.total_reviews || 0;
        } else {
            console.error('Eroare:', data.error);
        }
    })
    .catch(error => {
        console.error('Eroare:', error);
    });
}

/**
 * Load employee activity table
 */
function loadEmployeeActivity() {
    const startDate = document.getElementById('reportStartDate')?.value || '';
    const endDate = document.getElementById('reportEndDate')?.value || '';
    
    const params = new URLSearchParams();
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    
    fetch('../api/manager/get_employee_activity.php?' + params, {
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayEmployeeActivity(data.employees);
        } else {
            showNotification(data.error || 'Eroare la încărcarea datelor', 'error');
        }
    })
    .catch(error => {
        console.error('Eroare:', error);
        showNotification('Eroare de conexiune la server', 'error');
    });
}

/**
 * Display employee activity in table
 */
function displayEmployeeActivity(employees) {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#employeesTable')) {
        $('#employeesTable').DataTable().destroy();
    }
    
    const tbody = document.getElementById('employeesTableBody');
    
    if (employees.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>Nu există angajați înregistrați</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = employees.map(emp => {
        // Format date
        let formattedDate = '<span class="text-muted">Niciodată</span>';
        if (emp.ultima_autentificare && emp.ultima_autentificare !== '0000-00-00 00:00:00') {
            const date = new Date(emp.ultima_autentificare);
            formattedDate = date.toLocaleDateString('ro-RO', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        return `
        <tr>
            <td><strong>${escapeHtml(emp.nume)} ${escapeHtml(emp.prenume)}</strong></td>
            <td>${escapeHtml(emp.email)}</td>
            <td>${formattedDate}</td>
            <td>${emp.total_inchirieri || 0}</td>
            <td>${emp.total_recenzii || 0}</td>
            <td>
                ${emp.active_today ? 
                    '<span class="badge bg-success">Activ</span>' : 
                    '<span class="badge bg-secondary">Inactiv</span>'}
            </td>
        </tr>
        `;
    }).join('');
    
    // Initialize DataTable
    $('#employeesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ro.json'
        },
        pageLength: 10,
        order: [[2, 'desc']], // Sort by ultima_autentificare
        columnDefs: [
            { orderable: true, targets: '_all' }
        ]
    });
}

/**
 * Filter employees by date range
 */
function filterEmployees() {
    const startDate = document.getElementById('reportStartDate').value;
    const endDate = document.getElementById('reportEndDate').value;
    
    if (!startDate || !endDate) {
        showNotification('Selectează intervalul de date', 'warning');
        return;
    }
    
    if (new Date(startDate) > new Date(endDate)) {
        showNotification('Data de început trebuie să fie înainte de data de sfârșit', 'error');
        return;
    }
    
    loadEmployeeActivity();
}

/**
 * Generate report
 */
function generateReport() {
    const startDate = document.getElementById('reportStartDate').value;
    const endDate = document.getElementById('reportEndDate').value;
    
    if (!startDate || !endDate) {
        showNotification('Selectează intervalul de date pentru raport', 'warning');
        return;
    }
          
    // Create download link
    const params = new URLSearchParams({
        type: 'pdf',
        start_date: startDate,
        end_date: endDate
    });
    
    const url = `../api/manager/generate_report.php?${params}`;
    
    window.open(url, '_blank');   
}
