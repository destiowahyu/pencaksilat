<?php
// Add this JavaScript to the existing perlombaan.php file, in the <script> section

// Add these functions to handle the Detail and Register buttons
?>

<script>
// Add these functions to the existing JavaScript in perlombaan.php

// Competition detail modal functions
function viewCompetitionDetails(competitionId) {
    // Create modal backdrop
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop';
    backdrop.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    `;
    
    // Load competition details
    fetch(`competition-detail-modal.php?id=${competitionId}`)
        .then(response => response.text())
        .then(html => {
            backdrop.innerHTML = html;
            document.body.appendChild(backdrop);
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error loading competition details:', error);
            alert('Gagal memuat detail perlombaan');
            document.body.removeChild(backdrop);
        });
    
    // Close modal when clicking backdrop
    backdrop.addEventListener('click', function(e) {
        if (e.target === backdrop) {
            closeCompetitionDetailModal();
        }
    });
}

function closeCompetitionDetailModal() {
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        document.body.removeChild(backdrop);
        document.body.style.overflow = '';
    }
}

function registerNow(competitionId) {
    // Close detail modal first
    closeCompetitionDetailModal();
    
    // Redirect to registration page
    window.location.href = `daftar-perlombaan.php?id=${competitionId}`;
}

// Update the existing competition card buttons in the available competitions section
// Replace the existing button onclick handlers with these:

// For Detail button:
// onclick="viewCompetitionDetails(<?php echo $competition['id']; ?>)"

// For Register button:
// onclick="registerNow(<?php echo $competition['id']; ?>)"

// Add CSS for modal backdrop
const modalStyles = `
<style>
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-backdrop .competition-detail-modal {
    max-height: 90vh;
    overflow-y: auto;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Success message styles */
.success-message {
    background: #dcfce7;
    border: 2px solid #22c55e;
    color: #166534;
    padding: 15px 20px;
    border-radius: 8px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.whatsapp-group-link {
    background: #22c55e;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
}

.whatsapp-group-link:hover {
    background: #16a34a;
}
</style>
`;

// Add styles to head
document.head.insertAdjacentHTML('beforeend', modalStyles);

// Handle success message and WhatsApp group link
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const tab = urlParams.get('tab');
    
    if (success === '1') {
        // Show success message
        const successMessage = document.createElement('div');
        successMessage.className = 'success-message';
        successMessage.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <div>
                <strong>Pendaftaran Berhasil!</strong>
                <p>Atlet telah berhasil didaftarkan. Silakan lakukan pembayaran dan upload bukti pembayaran.</p>
                ${window.sessionStorage.getItem('whatsapp_group') ? `
                    <a href="${window.sessionStorage.getItem('whatsapp_group')}" target="_blank" class="whatsapp-group-link">
                        <i class="fab fa-whatsapp"></i> Join Grup WhatsApp
                    </a>
                ` : ''}
            </div>
        `;
        
        // Insert after page header
        const pageHeader = document.querySelector('.page-header');
        pageHeader.insertAdjacentElement('afterend', successMessage);
        
        // Clear session storage
        window.sessionStorage.removeItem('whatsapp_group');
        
        // Auto-hide success message after 10 seconds
        setTimeout(() => {
            successMessage.style.opacity = '0';
            setTimeout(() => {
                if (successMessage.parentNode) {
                    successMessage.parentNode.removeChild(successMessage);
                }
            }, 300);
        }, 10000);
    }
    
    // Switch to correct tab if specified
    if (tab === 'registered-athletes') {
        const registeredTab = document.querySelector('a[href="#registered-athletes"]');
        if (registeredTab) {
            registeredTab.click();
        }
    }
});

// Handle ESC key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCompetitionDetailModal();
    }
});
</script>
