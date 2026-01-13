<!-- Rental Success Modal -->
<div class="modal fade" id="rentalModal" tabindex="-1" aria-labelledby="rentalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #333; color: white;">
                <h5 class="modal-title" id="rentalModalLabel">Închiriere confirmată</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold mb-3" id="modalBookName"></h6>
                <div style="background: #f5f5f5; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                    <div style="margin-bottom: 10px;">
                        <strong>Data primire:</strong> <span id="modalDataPrimire"></span>
                        <div style="font-size: 12px; color: #666;">Cartea va ajunge în ~4 zile</div>
                    </div>
                    <div>
                        <strong>Data returnare:</strong> <span id="modalDataScadenta"></span>
                        <div style="font-size: 12px; color: #666;">Ai 30 de zile</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Închide</button>
            </div>
        </div>
    </div>
</div>
