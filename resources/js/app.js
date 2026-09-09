// Oweru Tech Solutions - Main JavaScript
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Scanner functionality
document.addEventListener('DOMContentLoaded', () => {
    initScanner();
    initPipelineFilters();
    initEnquiryForm();
    initCurrencyToggle();
});

/**
 * Public Scanner - Handles URL submission and result display
 */
function initScanner() {
    const scannerForm = document.getElementById('scanner-form');
    if (!scannerForm) return;

    scannerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const urlInput = document.getElementById('scanner-url');
        const scanBtn = document.getElementById('scan-btn');
        const resultsContainer = document.getElementById('scanner-results');
        const loader = document.getElementById('scanner-loader');

        if (!urlInput.value.trim()) {
            showNotification('Please enter a website address', 'error');
            return;
        }

        // Show loading state
        scanBtn.disabled = true;
        scanBtn.innerHTML = '<svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Scanning...';
        loader.classList.remove('hidden');
        resultsContainer.innerHTML = '';

        try {
            const response = await axios.post('/api/scanner/scan', {
                url: urlInput.value.trim()
            });

            if (response.data.success) {
                displayScanResults(response.data.data);
            } else {
                showNotification(response.data.message || 'Scan failed. Please try again.', 'error');
            }
        } catch (error) {
            if (error.response && error.response.data) {
                showNotification(error.response.data.message || 'Scan failed. Please try again.', 'error');
            } else {
                showNotification('Network error. Please check your connection.', 'error');
            }
        } finally {
            scanBtn.disabled = false;
            scanBtn.innerHTML = '🔍 Scan Website';
            loader.classList.add('hidden');
        }
    });
}

/**
 * Display scan results on the public scanner page
 */
function displayScanResults(data) {
    const container = document.getElementById('scanner-results');
    if (!container) return;

    const scoreClass = getScoreClass(data.score);
    const bandClass = getBandClass(data.band);

    container.innerHTML = `
        <div class="animate-fade-in-up">
            <div class="text-center mb-8">
                <div class="score-circle mx-auto border-${bandClass}" style="background: ${getScoreBgColor(data.score)}">
                    <span class="${scoreClass}">${data.score}</span>
                </div>
                <p class="text-sm text-gray-500 mt-2">out of 100</p>
                <span class="badge badge-${bandClass} mt-2 text-sm">${data.band}</span>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-800 mb-3">Key Findings</h3>
                <div class="space-y-3">
                    ${data.findings.map(f => `
                        <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50">
                            <span class="text-red-500 mt-0.5">⚠</span>
                            <div>
                                <p class="font-medium text-gray-800">${f.check_name}</p>
                                <p class="text-sm text-gray-600 mt-1">${f.finding_text}</p>
                                <p class="text-xs text-gray-400 mt-1">${f.area} • ${f.passed ? 'Passed' : 'Failed'}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <div class="text-center p-6 bg-blue-50 rounded-xl">
                <h4 class="font-bold text-gray-800 mb-2">Get Your Full Report</h4>
                <p class="text-sm text-gray-600 mb-4">Request a detailed report with all findings, business impact analysis, and recommendations.</p>
                <button onclick="showReportRequestForm()" class="btn-primary">
                    Request Full Report
                </button>
            </div>
        </div>
    `;
}

/**
 * Show the report request form (creates an enquiry)
 */
function showReportRequestForm() {
    const modal = document.getElementById('report-modal');
    if (modal) modal.classList.remove('hidden');
}

/**
 * Pipeline filters for internal dashboard
 */
function initPipelineFilters() {
    const filterBtns = document.querySelectorAll('.pipeline-filter');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const stage = btn.dataset.stage;
            filterPipeline(stage);
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        });
    });
}

function filterPipeline(stage) {
    const rows = document.querySelectorAll('.pipeline-row');
    rows.forEach(row => {
        if (stage === 'all' || row.dataset.stage === stage) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/**
 * Enquiry form validation
 */
function initEnquiryForm() {
    const form = document.getElementById('enquiry-form');
    if (!form) return;

    form.addEventListener('submit', (e) => {
        const budgetPref = document.getElementById('budget_preference');
        if (budgetPref && !budgetPref.value) {
            e.preventDefault();
            showNotification('Please select a budget option', 'error');
            return;
        }
    });
}

/**
 * Currency toggle for offer page
 */
function initCurrencyToggle() {
    const toggle = document.getElementById('currency-toggle');
    if (!toggle) return;

    toggle.addEventListener('change', (e) => {
        const currency = e.target.checked ? 'USD' : 'TZS';
        document.querySelectorAll('.price-tzs').forEach(el => {
            el.style.display = currency === 'TZS' ? '' : 'none';
        });
        document.querySelectorAll('.price-usd').forEach(el => {
            el.style.display = currency === 'USD' ? '' : 'none';
        });
    });
}

/**
 * Notification system
 */
function showNotification(message, type = 'info') {
    const container = document.getElementById('notifications') || createNotificationContainer();
    const id = 'notif-' + Date.now();
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };

    const notif = document.createElement('div');
    notif.id = id;
    notif.className = `${colors[type] || colors.info} text-white px-4 py-3 rounded-lg shadow-lg mb-2 animate-fade-in-up flex items-center gap-2`;
    notif.innerHTML = `<span>${message}</span><button onclick="this.parentElement.remove()" class="ml-auto opacity-75 hover:opacity-100">&times;</button>`;
    container.appendChild(notif);

    setTimeout(() => {
        const el = document.getElementById(id);
        if (el) el.remove();
    }, 5000);
}

function createNotificationContainer() {
    const container = document.createElement('div');
    container.id = 'notifications';
    container.className = 'fixed top-4 right-4 z-50 max-w-sm';
    document.body.appendChild(container);
    return container;
}

/**
 * Helper functions
 */
function getScoreClass(score) {
    if (score < 40) return 'score-critical';
    if (score < 60) return 'score-weak';
    if (score < 80) return 'score-adequate';
    return 'score-strong';
}

function getBandClass(band) {
    const map = { 'Critical': 'danger', 'Weak': 'warning', 'Adequate': 'info', 'Strong': 'success' };
    return map[band] || 'gray';
}

function getScoreBgColor(score) {
    if (score < 40) return '#fef2f2';
    if (score < 60) return '#fffbeb';
    if (score < 80) return '#eff6ff';
    return '#ecfdf5';
}

// Staff Internal Scan Function
function scanWebsite(websiteId) {
    if (!confirm('Run a new scan on this website? This may take 30-60 seconds.')) {
        return;
    }

    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Scanning...';
    btn.disabled = true;

    axios.post('/admin/websites/' + websiteId + '/run-scan', {
        _token: document.querySelector('meta[name="csrf-token"]')?.content
    })
        .then(response => {
            showNotification('Scan started successfully!', 'success');
            // Refresh the page after a short delay to show new results
            setTimeout(() => {
                location.reload();
            }, 1500);
        })
        .catch(error => {
            const msg = error.response?.data?.message || 'Failed to start scan. Please try again.';
            showNotification(msg, 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
}

// Batch scan: scan all (filtered) websites in the list
function batchScanWebsites() {
    const count = parseInt(document.getElementById('batch-scan-count')?.value || '0', 10);
    if (!count || confirm(`Run a new scan on ${count} website(s)? This may take several minutes (30-60s per site).`)) {
        if (!count) return;
    }

    const btn = document.getElementById('batch-scan-btn');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) {
        btn.innerHTML = '⏳ Batch scanning...';
        btn.disabled = true;
    }

    axios.post('/admin/websites/run-batch-scan', {
        _token: document.querySelector('meta[name="csrf-token"]')?.content,
        search: new URLSearchParams(window.location.search).get('search') || '',
        status: new URLSearchParams(window.location.search).get('status') || '',
    })
        .then(response => {
            const msg = response.data?.message || 'Batch scan queued!';
            showNotification(msg, 'success');
            // Scans now run in a background queue worker - no long wait needed
            setTimeout(() => { window.location.href = '/admin/scans'; }, 1200);
        })
        .catch(error => {
            const msg = error.response?.data?.message || 'Batch scan failed. Please try again.';
            showNotification(msg, 'error');
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
}

// Make functions available globally
window.showReportRequestForm = showReportRequestForm;
window.showNotification = showNotification;
window.filterPipeline = filterPipeline;
window.scanWebsite = scanWebsite;
window.batchScanWebsites = batchScanWebsites;

/**
 * Animated Counter for Stats
 */
function initAnimatedCounters() {
    const counters = document.querySelectorAll('.counter');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                animateCounter(counter, target);
                observer.unobserve(counter);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element, target) {
    let current = 0;
    const increment = target / 60;
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target + '+';
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 20);
}

// Initialize counters on page load
document.addEventListener('DOMContentLoaded', () => {
    initAnimatedCounters();
});
