// Main JavaScript functions for AutoCare Admin

// Hamburger Menu Toggle
function initHamburgerMenu() {
    const hamburger = document.getElementById('hamburgerMenu');
    const navMenu = document.getElementById('navMenu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking nav links
        const navLinks = navMenu.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });
    }
}

// Live search functionality
function initLiveSearch(searchInputId, tableId, searchColumns = []) {
    const searchInput = document.getElementById(searchInputId);
    const table = document.getElementById(tableId);
    
    if (!searchInput || !table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let found = false;
            
            if (searchColumns.length === 0) {
                // Search all columns
                found = Array.from(cells).some(cell => 
                    cell.textContent.toLowerCase().includes(searchTerm)
                );
            } else {
                // Search specific columns
                found = searchColumns.some(columnIndex => {
                    if (cells[columnIndex]) {
                        return cells[columnIndex].textContent.toLowerCase().includes(searchTerm);
                    }
                    return false;
                });
            }
            
            row.style.display = found ? '' : 'none';
        });
        
        // Show/hide no results message
        showNoResults(tbody, searchTerm);
    });
}

// Filter functionality
function initFilter(filterSelectId, tableId, columnIndex) {
    const filterSelect = document.getElementById(filterSelectId);
    const table = document.getElementById(tableId);
    
    if (!filterSelect || !table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    filterSelect.addEventListener('change', function() {
        const filterValue = this.value;
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const cellValue = cells[columnIndex] ? cells[columnIndex].textContent.trim() : '';
            
            if (filterValue === '' || cellValue === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        showNoResults(tbody, filterValue);
    });
}

// Show no results message
function showNoResults(tbody, searchTerm) {
    const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(row => 
        row.style.display !== 'none'
    );
    
    // Remove existing no-results row
    const existingNoResults = tbody.querySelector('.no-results-row');
    if (existingNoResults) {
        existingNoResults.remove();
    }
    
    if (visibleRows.length === 0 && searchTerm !== '') {
        const noResultsRow = document.createElement('tr');
        noResultsRow.className = 'no-results-row';
        const colCount = tbody.querySelector('tr') ? tbody.querySelector('tr').querySelectorAll('td').length : 1;
        noResultsRow.innerHTML = `<td colspan="${colCount}" class="no-results">Tidak ada data yang ditemukan</td>`;
        tbody.appendChild(noResultsRow);
    }
}

// Auto-suggestion for input fields
function initAutoSuggestion(inputId, suggestionsData, callback) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const suggestionBox = document.createElement('div');
    suggestionBox.className = 'suggestion-box';
    suggestionBox.style.display = 'none';
    
    input.parentNode.appendChild(suggestionBox);
    
    input.addEventListener('input', function() {
        const value = this.value.toLowerCase();
        suggestionBox.innerHTML = '';
        
        if (value.length < 2) {
            suggestionBox.style.display = 'none';
            return;
        }
        
        const matches = suggestionsData.filter(item => 
            item.toLowerCase().includes(value)
        ).slice(0, 5);
        
        if (matches.length > 0) {
            matches.forEach(match => {
                const item = document.createElement('div');
                item.className = 'suggestion-item';
                item.textContent = match;
                item.addEventListener('click', function() {
                    input.value = match;
                    suggestionBox.style.display = 'none';
                    if (callback) callback(match);
                });
                suggestionBox.appendChild(item);
            });
            suggestionBox.style.display = 'block';
        } else {
            suggestionBox.style.display = 'none';
        }
    });
    
    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestionBox.contains(e.target)) {
            suggestionBox.style.display = 'none';
        }
    });
}

// Form validation
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    
    Object.keys(rules).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        const rule = rules[fieldName];
        
        if (!field) return;
        
        // Remove existing error messages
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        field.classList.remove('error');
        
        // Check required
        if (rule.required && !field.value.trim()) {
            showFieldError(field, rule.required);
            isValid = false;
            return;
        }
        
        // Check pattern
        if (rule.pattern && field.value && !rule.pattern.test(field.value)) {
            showFieldError(field, rule.patternMessage || 'Format tidak valid');
            isValid = false;
            return;
        }
        
        // Check min length
        if (rule.minLength && field.value.length < rule.minLength) {
            showFieldError(field, `Minimal ${rule.minLength} karakter`);
            isValid = false;
            return;
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    field.parentNode.appendChild(errorDiv);
}

// Auto-hide alerts
function initAutoHideAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...'); // Debug log
    
    initAutoHideAlerts();
    initHamburgerMenu();
    initDeleteModal();
    
    // Initialize based on current page
    const currentPage = window.location.pathname.split('/').pop();
    console.log('Current page:', currentPage); // Debug log
    
    switch(currentPage) {
        case 'list_service.php':
            initLiveSearch('searchInput', 'servicesTable');
            initFilter('statusFilter', 'servicesTable', 4); // Status column
            break;
            
        case 'list_customer.php':
            initLiveSearch('searchInput', 'customersTable', [0, 1]); // Name and email columns
            break;
            
        case 'list_kendaraan.php':
            initLiveSearch('searchInput', 'vehiclesTable', [0, 1]); // License plate and owner columns
            break;
            
        case 'tambah_service.php':
            // Form validation rules
            const serviceFormRules = {
                'customer_name': {
                    required: 'Nama customer harus diisi',
                    minLength: 2
                },
                'customer_email': {
                    required: 'Email harus diisi',
                    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    patternMessage: 'Format email tidak valid'
                },
                'customer_phone': {
                    required: 'Nomor HP harus diisi',
                    pattern: /^[0-9+\-\s()]+$/,
                    patternMessage: 'Nomor HP hanya boleh berisi angka'
                },
                'license_plate': {
                    required: 'Nomor polisi harus diisi'
                },
                'brand': {
                    required: 'Merek kendaraan harus diisi'
                },
                'model': {
                    required: 'Model kendaraan harus diisi'
                },
                'year': {
                    required: 'Tahun kendaraan harus diisi',
                    pattern: /^[0-9]{4}$/,
                    patternMessage: 'Tahun harus berupa 4 digit angka'
                },
                'service_type': {
                    required: 'Jenis servis harus diisi'
                },
                'entry_date': {
                    required: 'Tanggal masuk harus diisi'
                }
            };
            
            const serviceForm = document.getElementById('serviceForm');
            if (serviceForm) {
                serviceForm.addEventListener('submit', function(e) {
                    if (!validateForm('serviceForm', serviceFormRules)) {
                        e.preventDefault();
                    }
                });
            }
            break;
    }
});

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
}

// Show loading state
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = '<div class="loading show">Memuat...</div>';
    }
}

// Hide loading state
function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const loading = element.querySelector('.loading');
        if (loading) {
            loading.remove();
        }
    }
}

// Make functions globally available
window.confirmDelete = confirmDelete;
window.closeDeleteModal = closeDeleteModal;