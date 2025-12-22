// Student Management System - Main JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // ===========================================
    // MENU TOGGLE
    // ===========================================
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('hidden');
            mainContent.classList. toggle('expanded');
            
            // Save state to localStorage
            if (sidebar.classList.contains('hidden')) {
                localStorage.setItem('sidebarHidden', 'true');
            } else {
                localStorage.setItem('sidebarHidden', 'false');
            }
        });
        
        // Restore sidebar state
        if (localStorage.getItem('sidebarHidden') === 'true') {
            sidebar. classList.add('hidden');
            mainContent.classList.add('expanded');
        }
    }
    
    // ===========================================
    // ACTIVE NAVIGATION LINK
    // ===========================================
    const currentPath = window.location.pathname;
    const navLinks = document. querySelectorAll('. nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) {
            link.classList.add('active');
        }
    });
    
    // ===========================================
    // FORM VALIDATION
    // ===========================================
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalidField = null;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                    field.style.boxShadow = '0 0 0 4px rgba(239, 68, 68, 0.1)';
                    
                    if (! firstInvalidField) {
                        firstInvalidField = field;
                    }
                } else {
                    field.style.borderColor = 'var(--border-color)';
                    field.style.boxShadow = 'none';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                
                // Show error message
                showNotification('Please fill in all required fields', 'error');
                
                // Focus first invalid field
                if (firstInvalidField) {
                    firstInvalidField.focus();
                }
            }
        });
        
        // Remove error styling on input
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = 'var(--border-color)';
                this.style.boxShadow = 'none';
            });
        });
    });
    
    // ===========================================
    // IMAGE PREVIEW FOR FILE UPLOADS
    // ===========================================
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        if (input.accept && input.accept.includes('image')) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                
                if (file) {
                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        showNotification('File size must be less than 5MB', 'error');
                        this.value = '';
                        return;
                    }
                    
                    // Validate file type
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    if (!validTypes. includes(file.type)) {
                        showNotification('Please select a valid image file (JPG, JPEG, or PNG)', 'error');
                        this.value = '';
                        return;
                    }
                    
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        let preview = input.parentElement.querySelector('.image-preview');
                        
                        if (! preview) {
                            preview = document.createElement('img');
                            preview.className = 'image-preview';
                            preview.style.cssText = `
                                max-width: 200px; 
                                max-height:  200px;
                                margin-top: 15px; 
                                border-radius: 12px;
                                border: 3px solid var(--primary-color);
                                box-shadow: var(--shadow-md);
                                object-fit: cover;
                            `;
                            input.parentElement.appendChild(preview);
                        }
                        
                        preview.src = e. target.result;
                    };
                    
                    reader. readAsDataURL(file);
                }
            });
        }
    });
    
    // ===========================================
    // CONFIRM DELETE
    // ===========================================
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    
    deleteLinks.forEach(link => {
        if (! link.onclick) {
            link.addEventListener('click', function(e) {
                if (! confirm('Are you sure you want to delete this item?  This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        }
    });
    
    // ===========================================
    // AUTO-HIDE ALERTS
    // ===========================================
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.3s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
        
        // Close button
        alert.style.position = 'relative';
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = `
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.3s;
        `;
        closeBtn.addEventListener('mouseover', () => closeBtn.style.opacity = '1');
        closeBtn.addEventListener('mouseout', () => closeBtn.style.opacity = '0.7');
        closeBtn.addEventListener('click', () => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        });
        alert.appendChild(closeBtn);
    });
    
    // ===========================================
    // TABLE SEARCH/FILTER
    // ===========================================
    const tables = document.querySelectorAll('. data-table');
    
    tables.forEach(table => {
        const tableParent = table.closest('.card-body');
        
        if (tableParent && table.querySelectorAll('tbody tr').length > 5) {
            // Create search input
            const searchDiv = document.createElement('div');
            searchDiv.className = 'search-bar';
            searchDiv.style. marginBottom = '20px';
            
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput. className = 'search-input';
            searchInput. placeholder = '🔍 Search...';
            
            searchDiv.appendChild(searchInput);
            tableParent.insertBefore(searchDiv, table. parentElement);
            
            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value. toLowerCase();
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
    
    // ===========================================
    // ATTENDANCE STATUS QUICK SELECT
    // ===========================================
    const attendanceSelects = document.querySelectorAll('select[name*="attendance"]');
    
    if (attendanceSelects.length > 0) {
        const quickActionDiv = document.createElement('div');
        quickActionDiv.style.cssText = 'margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;';
        quickActionDiv.innerHTML = `
            <button type="button" class="btn btn-sm btn-success" id="markAllPresent">✅ Mark All Present</button>
            <button type="button" class="btn btn-sm btn-danger" id="markAllAbsent">❌ Mark All Absent</button>
        `;
        
        const form = attendanceSelects[0].closest('form');
        if (form) {
            form. insertBefore(quickActionDiv, form.querySelector('. table-responsive'));
            
            document.getElementById('markAllPresent')?. addEventListener('click', function() {
                attendanceSelects.forEach(select => {
                    if (select.name.includes('[status]')) {
                        select.value = 'present';
                    }
                });
                showNotification('All students marked as present', 'success');
            });
            
            document.getElementById('markAllAbsent')?.addEventListener('click', function() {
                attendanceSelects. forEach(select => {
                    if (select.name.includes('[status]')) {
                        select.value = 'absent';
                    }
                });
                showNotification('All students marked as absent', 'success');
            });
        }
    }
    
    // ===========================================
    // GRADE CALCULATION
    // ===========================================
    const gradeInputs = document.querySelectorAll('input[name*="grades"]');
    
    gradeInputs.forEach(input => {
        input.addEventListener('input', function() {
            const maxMarks = parseInt(this.max);
            const obtainedMarks = parseFloat(this.value);
            
            if (obtainedMarks > maxMarks) {
                this.value = maxMarks;
                showNotification(`Marks cannot exceed ${maxMarks}`, 'error');
            }
            
            if (obtainedMarks < 0) {
                this.value = 0;
            }
        });
    });
    
    // ===========================================
    // RESPONSIVE TABLE WRAPPER
    // ===========================================
    const dataTablesElements = document.querySelectorAll('. data-table');
    dataTablesElements.forEach(table => {
        if (! table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
    // ===========================================
    // NOTIFICATION SYSTEM
    // ===========================================
    window.showNotification = function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 90px;
            right: 20px;
            z-index:  9999;
            min-width: 300px;
            max-width: 500px;
            animation: slideIn 0.3s ease-out;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    };
    
    // ===========================================
    // PRINT FUNCTIONALITY
    // ===========================================
    window.printPage = function() {
        window.print();
    };
    
    // ===========================================
    // MOBILE RESPONSIVE MENU
    // ===========================================
    if (window.innerWidth <= 768) {
        if (sidebar) {
            sidebar. classList.add('hidden');
            mainContent?. classList.add('expanded');
        }
    }
    
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            sidebar?.classList.add('hidden');
            mainContent?.classList.add('expanded');
        } else {
            if (localStorage.getItem('sidebarHidden') !== 'true') {
                sidebar?.classList.remove('hidden');
                mainContent?.classList.remove('expanded');
            }
        }
    });
    
    // ===========================================
    // LOADING INDICATOR
    // ===========================================
    const formsWithSubmit = document.querySelectorAll('form');
    formsWithSubmit.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '⏳ Processing...';
            }
        });
    });
    
    // ===========================================
    // SMOOTH SCROLL
    // ===========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document. querySelector(href).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
    
    console.log('✅ Student Management System - Loaded Successfully!');
});

// Add CSS animation for notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);