let companiesTable;

$(document).ready(function() {
    // Initialize DataTable
    companiesTable = $('#companiesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ro.json'
        },
        columns: [
            { orderable: true },  // ID
            { orderable: true },  // Nume
            { orderable: true },  // CUI
            { orderable: true },  // Adresa
            { orderable: true },  // Email
            { orderable: true },  // Telefon
            { orderable: false }   // Actions - NOT SORTABLE
        ],
        order: [[0, 'asc']],
        pageLength: 25
    });
    
    // Load data
    loadStatistics();
    loadCompanies();
});

function loadStatistics() {
    fetch('../api/admin/get_companies_stats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('totalCompanies').textContent = data.stats.total;
                document.getElementById('totalManagers').textContent = data.stats.managers;
            }
        })
        .catch(error => console.error('Eroare la încărcarea statisticilor:', error));
}

function loadCompanies() {
    fetch('../api/admin/get_companies_list.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                companiesTable.clear();
                
                data.companies.forEach(company => {               
                    const actions = `
                        <button class="btn btn-sm btn-info btn-action" onclick="viewCompany(${company.companie_id})" title="Vezi detalii">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning btn-action" onclick="editCompany(${company.companie_id})" title="Editează">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-action" onclick="deleteCompany(${company.companie_id}, '${company.nume}')" title="Șterge">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    
                    companiesTable.row.add([
                        company.companie_id,
                        company.nume,
                        company.cui || '-',
                        company.adresa || '-',
                        company.email || '-',
                        company.telefon || '-',
                        actions
                    ]);
                });
                
                companiesTable.draw();
            } else {
                alert('Eroare la încărcarea companiilor: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Eroare la încărcarea companiilor:', error);
            alert('Eroare la încărcarea companiilor');
        });
}

function saveCompany() {
    const form = document.getElementById('addCompanyForm');
    const formData = new FormData(form);
    
    fetch('../api/admin/add_company.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Compania a fost adăugată cu succes!');
            $('#addCompanyModal').modal('hide');
            form.reset();
            loadStatistics();
            loadCompanies();
        } else {
            alert('Eroare: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Eroare la salvarea companiei');
    });
}

function editCompany(id) {
    fetch(`../api/admin/get_company.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('editCompanyForm');
                const company = data.company;
                
                form.querySelector('[name="companie_id"]').value = company.companie_id;
                form.querySelector('[name="nume"]').value = company.nume;
                form.querySelector('[name="cui"]').value = company.cui;
                form.querySelector('[name="adresa"]').value = company.adresa || '';
                form.querySelector('[name="email"]').value = company.email || '';
                form.querySelector('[name="telefon"]').value = company.telefon || '';
                
                $('#editCompanyModal').modal('show');
            } else {
                alert('Eroare la încărcarea datelor companiei');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Eroare la încărcarea companiei');
        });
}

function updateCompany() {
    const form = document.getElementById('editCompanyForm');
    const formData = new FormData(form);
    
    fetch('../api/admin/update_company.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Compania a fost actualizată cu succes!');
            $('#editCompanyModal').modal('hide');
            loadStatistics();
            loadCompanies();
        } else {
            alert('Eroare: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Eroare la actualizarea companiei');
    });
}

function viewCompany(id) {
    fetch(`../api/admin/get_company.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const company = data.company;
                const content = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Nume:</strong><br>${company.nume}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>CUI:</strong><br>${company.cui || '-'}
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Adresă:</strong><br>${company.adresa || '-'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email:</strong><br>${company.email || '-'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Telefon:</strong><br>${company.telefon || '-'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Data Înregistrare:</strong><br>${company.data_inregistrare || '-'}
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Număr Manageri:</strong><br>${company.managers_count || 0}
                        </div>
                    </div>
                `;
                
                document.getElementById('companyDetailsContent').innerHTML = content;
                $('#viewCompanyModal').modal('show');
            } else {
                alert('Eroare la încărcarea detaliilor companiei');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Eroare la încărcarea companiei');
        });
}

function deleteCompany(id, name) {
    if (!confirm(`Sigur doriți să ștergeți compania "${name}"?\n\nAceastă acțiune nu poate fi anulată!`)) {
        return;
    }
    
    fetch('../api/admin/delete_company.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `companie_id=${id}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Compania a fost ștearsă cu succes!');
            loadStatistics();
            loadCompanies();
        } else {
            alert('Eroare: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Eroare la ștergerea companiei');
    });
}
