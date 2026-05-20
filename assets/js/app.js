// FERN - Main JavaScript

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.nb-nav');
    if (navbar) {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.querySelector('.nb-nav-toggle');
    const navMenu = document.querySelector('.nb-nav-menu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('.nb-alert');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.transition = 'opacity 0.5s';
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 500);
        }, 5000);
    });
});

// Password strength meter
function checkPasswordStrength(password) {
    let strength = 0;
    const feedback = {
        level: 0,
        text: 'Lemah',
        color: '#FF5252'
    };
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    if (password.match(/\d/)) strength++;
    if (password.match(/[^a-zA-Z\d]/)) strength++;
    
    switch(strength) {
        case 0:
        case 1:
            feedback.level = 25;
            feedback.text = 'Lemah';
            feedback.color = '#FF5252';
            break;
        case 2:
            feedback.level = 50;
            feedback.text = 'Cukup';
            feedback.color = '#FFB300';
            break;
        case 3:
            feedback.level = 75;
            feedback.text = 'Kuat';
            feedback.color = '#FFEB3B';
            break;
        case 4:
            feedback.level = 100;
            feedback.text = 'Sangat Kuat';
            feedback.color = '#00FF88';
            break;
    }
    
    return feedback;
}

// File upload preview
function handleFileUpload(input, previewId) {
    const file = input.files[0];
    const preview = document.getElementById(previewId);
    
    if (file && preview) {
        const fileName = file.name;
        const fileSize = (file.size / 1024 / 1024).toFixed(2);
        
        preview.innerHTML = `
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-file-earmark-check" style="font-size: 24px; color: #00FF88;"></i>
                <div>
                    <div style="font-weight: 700;">${fileName}</div>
                    <div style="font-size: 12px; color: #6b7280;">${fileSize} MB</div>
                </div>
            </div>
        `;
        preview.style.borderColor = '#00FF88';
        preview.style.background = '#f0fdf4';
    }
}

// Drag and drop file upload
function initDragDrop(dropZoneId, inputId) {
    const dropZone = document.getElementById(dropZoneId);
    const input = document.getElementById(inputId);
    
    if (!dropZone || !input) return;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropZone.style.borderColor = '#00E5FF';
        dropZone.style.background = '#f0f9ff';
    }
    
    function unhighlight() {
        dropZone.style.borderColor = '#000';
        dropZone.style.background = '#fff';
    }
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        input.files = files;
        handleFileUpload(input, dropZoneId);
    }
    
    dropZone.addEventListener('click', () => input.click());
}

// Character counter for textarea
function initCharCounter(textareaId, counterId, min, max) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);
    
    if (!textarea || !counter) return;
    
    textarea.addEventListener('input', function() {
        const length = this.value.length;
        counter.textContent = `${length}/${max} karakter`;
        
        if (length < min) {
            counter.style.color = '#FF5252';
        } else if (length >= max) {
            counter.style.color = '#FFB300';
        } else {
            counter.style.color = '#00FF88';
        }
    });
}

// Confirm dialog
function confirmAction(message) {
    return confirm(message);
}

// Format number with thousand separator
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Berhasil disalin ke clipboard!');
    }, function() {
        alert('Gagal menyalin ke clipboard');
    });
}

// ============================================
// PWA Support
// ============================================

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('Service Worker registered:', registration);
            })
            .catch(error => {
                console.log('Service Worker registration failed:', error);
            });
    });
}

// PWA Install Prompt
let deferredPrompt;
const installButton = document.getElementById('pwa-install-btn');

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the mini-infobar from appearing
    e.preventDefault();
    // Stash the event so it can be triggered later
    deferredPrompt = e;
    // Show install button
    if (installButton) {
        installButton.style.display = 'inline-flex';
    }
});

if (installButton) {
    installButton.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        
        // Show the install prompt
        deferredPrompt.prompt();
        
        // Wait for the user to respond to the prompt
        const { outcome } = await deferredPrompt.userChoice;
        console.log(`User response to the install prompt: ${outcome}`);
        
        // Clear the deferredPrompt
        deferredPrompt = null;
        installButton.style.display = 'none';
    });
}

// Detect if app is installed
window.addEventListener('appinstalled', () => {
    console.log('PWA was installed');
    if (installButton) {
        installButton.style.display = 'none';
    }
});

// Check if running as PWA
function isPWA() {
    return window.matchMedia('(display-mode: standalone)').matches || 
           window.navigator.standalone === true;
}

// Show different UI for PWA mode
if (isPWA()) {
    document.body.classList.add('pwa-mode');
}

// ============================================
// Tooltips
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggers = document.querySelectorAll('[data-tooltip]');
    
    tooltipTriggers.forEach(trigger => {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = trigger.getAttribute('data-tooltip');
        tooltip.style.cssText = `
            position: absolute;
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 10000;
        `;
        document.body.appendChild(tooltip);
        
        trigger.addEventListener('mouseenter', (e) => {
            const rect = trigger.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            tooltip.style.opacity = '1';
        });
        
        trigger.addEventListener('mouseleave', () => {
            tooltip.style.opacity = '0';
        });
    });
});
