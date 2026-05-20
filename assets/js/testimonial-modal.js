/**
 * Testimonial Modal
 * Display testimonial details in a modal popup
 */

class TestimonialModal {
    constructor() {
        this.modal = null;
        this.init();
    }

    init() {
        // Check if modal already exists
        if (document.getElementById('testimonialModal')) {
            this.modal = document.getElementById('testimonialModal');
            return;
        }

        // Create modal HTML
        const modalHTML = `
            <div id="testimonialModal" style="
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.75);
                z-index: 9000;
                padding: 20px;
                box-sizing: border-box;
                overflow: auto;
            ">
                <div class="testimonial-modal-content" style="
                    background: #FFFFFF;
                    border: 3px solid #000000;
                    border-radius: 16px;
                    padding: 40px 32px 32px 32px;
                    max-width: 550px;
                    width: 100%;
                    box-shadow: 8px 8px 0 rgba(0, 0, 0, 0.2);
                    box-sizing: border-box;
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    margin: 0;
                " onclick="event.stopPropagation()">
                    <!-- Close Button -->
                    <button id="testimonialModalClose" type="button" style="
                        position: absolute;
                        top: 12px;
                        right: 12px;
                        background: white;
                        color: black;
                        border: 3px solid black;
                        border-radius: 50%;
                        width: 36px;
                        height: 36px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        font-weight: 800;
                        font-size: 20px;
                        transition: all 0.2s;
                    ">×</button>
                    
                    <!-- Avatar & Info -->
                    <div style="text-align: center; margin-bottom: 28px;">
                        <div id="testimonialAvatar" style="
                            width: 90px;
                            height: 90px;
                            border-radius: 50%;
                            border: 4px solid #000;
                            margin: 0 auto 16px;
                        "></div>
                        
                        <h4 id="testimonialName" style="
                            margin: 0 0 6px 0;
                            font-size: 22px;
                            font-weight: 800;
                            color: #000;
                        "></h4>
                        
                        <p id="testimonialUniversity" style="
                            color: #374151;
                            margin: 0 0 4px 0;
                            font-size: 15px;
                            font-weight: 600;
                        "></p>
                        
                        <p id="testimonialMajor" style="
                            color: #6B7280;
                            font-size: 14px;
                            margin: 0;
                        "></p>
                    </div>
                    
                    <!-- Testimonial Content Box -->
                    <div style="
                        background: #FFFFFF;
                        border: 3px solid #000;
                        border-radius: 12px;
                        padding: 24px;
                        position: relative;
                    ">
                        <div style="
                            color: var(--primary);
                            font-size: 36px;
                            line-height: 1;
                            font-weight: 900;
                            margin-bottom: 8px;
                        ">"</div>
                        <div id="testimonialContent" style="
                            font-size: 15px;
                            line-height: 1.7;
                            color: #1F2937;
                        "></div>
                        <div style="
                            color: var(--primary);
                            font-size: 36px;
                            line-height: 1;
                            font-weight: 900;
                            text-align: right;
                            margin-top: 8px;
                        ">"</div>
                    </div>
                </div>
            </div>
        `;

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('testimonialModal');

        // Attach event listeners
        const closeBtn = document.getElementById('testimonialModalClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        // Close on background click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal && this.modal.style.display === 'block') {
                this.close();
            }
        });
    }

    show(data) {
        if (!this.modal) return;

        // Set avatar
        const avatarEl = document.getElementById('testimonialAvatar');
        if (avatarEl) {
            if (data.image) {
                // If has image, show as background image
                avatarEl.innerHTML = '';
                avatarEl.style.backgroundImage = `url('${data.image}')`;
                avatarEl.style.backgroundSize = 'cover';
                avatarEl.style.backgroundPosition = 'center';
                avatarEl.style.backgroundColor = 'var(--primary)';
            } else {
                // If no image, show initials
                avatarEl.style.backgroundImage = 'none';
                avatarEl.style.backgroundColor = 'var(--primary)';
                avatarEl.style.display = 'flex';
                avatarEl.style.alignItems = 'center';
                avatarEl.style.justifyContent = 'center';
                avatarEl.style.fontWeight = '900';
                avatarEl.style.fontSize = '32px';
                avatarEl.style.color = '#000';
                avatarEl.innerHTML = data.initials || '';
            }
        }

        // Set name
        const nameEl = document.getElementById('testimonialName');
        if (nameEl) nameEl.textContent = data.name || '';

        // Set university
        const universityEl = document.getElementById('testimonialUniversity');
        if (universityEl) {
            universityEl.textContent = data.university || '';
            universityEl.style.display = data.university ? 'block' : 'none';
        }

        // Set major
        const majorEl = document.getElementById('testimonialMajor');
        if (majorEl) {
            majorEl.textContent = data.major || '';
            majorEl.style.display = data.major ? 'block' : 'none';
        }

        // Set content
        const contentEl = document.getElementById('testimonialContent');
        if (contentEl) {
            contentEl.innerHTML = data.content || '';
        }

        // Show modal
        this.modal.style.display = 'block';
    }

    close() {
        if (this.modal) {
            this.modal.style.display = 'none';
        }
    }
}

// Initialize modal on page load
let testimonialModal = null;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        testimonialModal = new TestimonialModal();
    });
} else {
    testimonialModal = new TestimonialModal();
}

/**
 * Show testimonial modal
 * Usage: showTestimonial({ name, university, major, image, initials, content })
 */
function showTestimonial(data) {
    console.log('showTestimonial called with data:', data);
    
    if (!testimonialModal) {
        console.log('Creating new TestimonialModal instance');
        testimonialModal = new TestimonialModal();
    }
    
    testimonialModal.show(data);
}

// Make function globally available
window.showTestimonial = showTestimonial;
