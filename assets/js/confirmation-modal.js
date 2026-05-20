/**
 * Custom Confirmation Modal
 * Replaces browser confirm() with a custom card-based modal
 */

class ConfirmationModal {
    constructor() {
        this.modal = null;
        this.callback = null;
        this.escapeHandler = null;
        this.init();
    }

    init() {
        // Check if modal already exists
        if (document.getElementById('confirmationModal')) {
            this.modal = document.getElementById('confirmationModal');
            return;
        }

        // Create modal HTML with fixed positioning
        const modalHTML = `
            <div id="confirmationModal" style="
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.6);
                z-index: 99999;
                padding: 20px;
                box-sizing: border-box;
                overflow: auto;
            ">
                <div class="confirmation-modal-content" style="
                    background: #FFFFFF;
                    border: 3px solid #000000;
                    border-radius: 12px;
                    padding: 24px;
                    width: 420px;
                    max-width: calc(100vw - 40px);
                    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.2);
                    animation: modalSlideIn 0.2s ease-out;
                    box-sizing: border-box;
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    margin: 0;
                " onclick="event.stopPropagation()">
                    <div style="
                        display: flex;
                        align-items: center;
                        gap: 12px;
                        margin-bottom: 16px;
                    ">
                        <div style="
                            width: 44px;
                            height: 44px;
                            min-width: 44px;
                            min-height: 44px;
                            background: #FF5722;
                            border: 3px solid #000000;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 22px;
                            flex-shrink: 0;
                        ">
                            ⚠️
                        </div>
                        <h3 id="confirmTitle" style="
                            margin: 0;
                            font-size: 18px;
                            font-weight: 700;
                            color: #1F2937;
                            line-height: 1.3;
                        ">Konfirmasi</h3>
                    </div>

                    <p id="confirmMessage" style="
                        margin: 0 0 24px 0;
                        font-size: 14px;
                        color: #4B5563;
                        line-height: 1.6;
                        word-wrap: break-word;
                    "></p>

                    <div style="
                        display: flex;
                        gap: 12px;
                        justify-content: flex-end;
                        flex-wrap: wrap;
                    ">
                        <button id="confirmCancel" type="button" class="nb-btn nb-btn-outline" style="
                            font-size: 13px;
                            padding: 10px 20px;
                            min-width: 100px;
                            cursor: pointer;
                        ">
                            <i class="bi bi-x"></i> Batal
                        </button>
                        <button id="confirmOk" type="button" class="nb-btn nb-btn-danger" style="
                            font-size: 13px;
                            padding: 10px 20px;
                            min-width: 100px;
                            cursor: pointer;
                        ">
                            <i class="bi bi-check"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Add animation keyframes and additional styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes modalSlideIn {
                from {
                    opacity: 0;
                    transform: translate(-50%, -55%) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, -50%) scale(1);
                }
            }
            
            #confirmationModal {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            #confirmationModal * {
                box-sizing: border-box;
            }
            
            @media (max-width: 480px) {
                .confirmation-modal-content {
                    width: calc(100vw - 40px) !important;
                    max-width: calc(100vw - 40px) !important;
                    padding: 20px !important;
                }
            }
        `;
        document.head.appendChild(style);

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('confirmationModal');

        // Attach event listeners
        const cancelBtn = document.getElementById('confirmCancel');
        const okBtn = document.getElementById('confirmOk');
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.cancel());
        }
        
        if (okBtn) {
            okBtn.addEventListener('click', () => this.confirm());
        }

        // Close on Escape key
        this.escapeHandler = (e) => {
            if (e.key === 'Escape' && this.modal && this.modal.style.display === 'flex') {
                this.cancel();
            }
        };
        document.addEventListener('keydown', this.escapeHandler);

        // Close on background click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                e.preventDefault();
                e.stopPropagation();
                this.cancel();
            }
        });
    }

    show(title, message, callback, options = {}) {
        // Prevent showing if already visible
        if (this.modal && this.modal.style.display === 'block') {
            console.warn('Modal already visible, ignoring show request');
            return;
        }
        
        const titleEl = document.getElementById('confirmTitle');
        const messageEl = document.getElementById('confirmMessage');
        const cancelBtn = document.getElementById('confirmCancel');
        const okBtn = document.getElementById('confirmOk');
        
        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.textContent = message;
        
        // Customize button labels if provided
        if (cancelBtn && options.cancelLabel) {
            cancelBtn.innerHTML = `<i class="bi bi-x"></i> ${options.cancelLabel}`;
        } else if (cancelBtn) {
            cancelBtn.innerHTML = `<i class="bi bi-x"></i> Batal`;
        }
        
        if (okBtn && options.confirmLabel) {
            okBtn.innerHTML = `<i class="bi bi-check"></i> ${options.confirmLabel}`;
        } else if (okBtn) {
            okBtn.innerHTML = `<i class="bi bi-check"></i> Hapus`;
        }
        
        // Customize button style if provided
        if (okBtn && options.confirmStyle) {
            okBtn.className = `nb-btn ${options.confirmStyle}`;
            okBtn.style.cssText = 'font-size: 13px; padding: 10px 20px; min-width: 100px; cursor: pointer;';
        } else if (okBtn) {
            okBtn.className = 'nb-btn nb-btn-danger';
            okBtn.style.cssText = 'font-size: 13px; padding: 10px 20px; min-width: 100px; cursor: pointer;';
        }
        
        this.callback = callback;
        
        if (this.modal) {
            // Force reflow before showing to prevent glitch
            this.modal.offsetHeight;
            
            // Show modal
            this.modal.style.display = 'block';
            
            // Force another reflow
            this.modal.offsetHeight;
            
            // Focus on cancel button for accessibility
            setTimeout(() => {
                const cancelBtn = document.getElementById('confirmCancel');
                if (cancelBtn) cancelBtn.focus();
            }, 100);
        }
    }

    confirm() {
        if (this.modal) {
            this.modal.style.display = 'none';
        }
        if (this.callback) {
            const cb = this.callback;
            this.callback = null; // Clear callback first to prevent double execution
            cb(true);
        }
    }

    cancel() {
        if (this.modal) {
            this.modal.style.display = 'none';
        }
        if (this.callback) {
            const cb = this.callback;
            this.callback = null; // Clear callback first to prevent double execution
            cb(false);
        }
    }

    destroy() {
        if (this.escapeHandler) {
            document.removeEventListener('keydown', this.escapeHandler);
        }
        if (this.modal) {
            this.modal.remove();
        }
    }
}

// Initialize modal on page load
let confirmModal = null;

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        confirmModal = new ConfirmationModal();
    });
} else {
    // DOM already loaded
    confirmModal = new ConfirmationModal();
}

/**
 * Show confirmation dialog
 * Usage: showConfirmation('Hapus Berita?', 'Apakah Anda yakin ingin menghapus berita ini?', (confirmed) => {
 *     if (confirmed) {
 *         // Do something
 *     }
 * }, { confirmLabel: 'Hapus', cancelLabel: 'Batal', confirmStyle: 'nb-btn-danger' });
 */
function showConfirmation(title, message, callback, options = {}) {
    if (!confirmModal) {
        confirmModal = new ConfirmationModal();
    }
    confirmModal.show(title, message, callback, options);
}

/**
 * Handle delete form submission with custom confirmation
 * Usage: <form onsubmit="return handleDeleteWithConfirmation(event, 'Hapus Berita?', 'Apakah Anda yakin?')">
 */
function handleDeleteWithConfirmation(event, title, message) {
    event.preventDefault();
    const form = event.target;

    showConfirmation(title, message, (confirmed) => {
        if (confirmed) {
            form.submit();
        }
    });

    return false;
}
