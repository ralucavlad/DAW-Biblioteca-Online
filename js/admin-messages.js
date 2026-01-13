$(document).ready(function() {
    let currentMessageId = null;

    // Initialize DataTable
    const table = $('#messagesTable').DataTable({
        ajax: {
            url: '../api/admin/get_messages.php',
            dataSrc: 'data'
        },
        columns: [
            { data: 'mesaj_id' },
            { data: 'user_name' },
            { data: 'email' },
            { data: 'subiect' },
            { 
                data: 'data_trimitere',
                render: function(data) {
                    return new Date(data).toLocaleString('ro-RO');
                }
            },
            { 
                data: 'stare',
                render: function(data) {
                    const badges = {
                        'nou': '<span class="badge bg-warning">Nou</span>',
                        'raspuns': '<span class="badge bg-success">Răspuns</span>',
                        'citit': '<span class="badge bg-secondary">Citit</span>'
                    };
                    return badges[data] || data;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return `
                        <button class="btn btn-view btn-sm" onclick="viewMessage(${data.mesaj_id})">
                            <i class="fas fa-eye me-1"></i>Vizualizează
                        </button>
                    `;
                }
            }
        ],
        order: [[4, 'desc']], // Order by date sent descending
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/ro.json'
        },
        pageLength: 25
    });

    // Load statistics
    loadStatistics();

    // View message function
    window.viewMessage = function(messageId) {
        currentMessageId = messageId;
        
        fetch(`../api/admin/get_message.php?id=${messageId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const msg = data.message;
                    
                    $('#modalUserName').text(msg.user_name);
                    $('#modalEmail').text(msg.email);
                    $('#modalSubject').text(msg.subiect);
                    $('#modalDate').text(new Date(msg.data_trimitere).toLocaleString('ro-RO'));
                    $('#modalMessage').text(msg.mesaj);
                    
                    // Show/hide reply section based on status
                    if (msg.raspuns) {
                        $('#existingReplySection').show();
                        $('#modalExistingReply').text(msg.raspuns);
                        $('#modalReplyDate').text('Răspuns trimis: ' + new Date(msg.data_raspuns).toLocaleString('ro-RO'));
                        $('#replySection').hide();
                        $('#sendReplyBtn').hide();
                    } else {
                        $('#existingReplySection').hide();
                        $('#replySection').show();
                        $('#sendReplyBtn').show();
                        $('#replyText').val('');
                    }
                                        
                    $('#messageModal').modal('show');
                } else {
                    alert('Eroare la încărcarea mesajului!');
                }
            })
            .catch(error => {
                alert('Eroare la încărcarea mesajului!');
            });
    };

    // Send reply
    $('#sendReplyBtn').click(function() {
        const reply = $('#replyText').val().trim();
        
        if (!reply) {
            alert('Te rugăm să scrii un răspuns!');
            return;
        }
        
        fetch('../api/admin/reply_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message_id: currentMessageId,
                reply: reply
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Răspuns trimis cu succes!');
                $('#messageModal').modal('hide');
                table.ajax.reload();
                loadStatistics();
            } else {
                alert('Eroare: ' + (data.error || 'Răspunsul nu a putut fi trimis'));
            }
        })
        .catch(error => {
            alert('Eroare la trimiterea răspunsului!');
        });
    });

    function loadStatistics() {
        fetch('../api/admin/get_messages_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#totalMessages').text(data.total || 0);
                    $('#newMessages').text(data.new || 0);
                    $('#readMessages').text(data.read+data.answered || 0);
                    $('#answeredMessages').text(data.answered || 0);
                }
            })
            .catch(error => {});
    }
});
