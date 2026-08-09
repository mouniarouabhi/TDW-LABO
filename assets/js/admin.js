// ========================================
// ADMIN.JS - Complete Administration Interface Script
// Consolidated and optimized version
// ========================================

/* ========================================
   GLOBAL STATE MANAGEMENT
   ======================================== */

const AdminState = {
    sidebarOpen: false,
    currentFilters: {},
    
    init() {
        // Load saved sidebar state
        const savedState = localStorage.getItem('sidebarState');
        this.sidebarOpen = savedState !== 'closed';
    },
    
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        localStorage.setItem('sidebarState', this.sidebarOpen ? 'open' : 'closed');
    }
};

/* ========================================
   SIDEBAR FUNCTIONALITY
   ======================================== */

const Sidebar = {
    init() {
        this.setupToggle();
        this.setupCategories();
        this.setupMobileHandling();
        this.restoreState();
    },
    
    setupToggle() {
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        const mainContent = document.querySelector('.admin-main');
        
        if (!toggleBtn || !sidebar) return;
        
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            document.body.classList.toggle('sidebar-closed');
            sidebar.classList.toggle('sidebar-closed');
            if (mainContent) mainContent.classList.toggle('sidebar-closed');
            
            AdminState.toggleSidebar();
        });
    },
    
    setupCategories() {
        const categoryHeaders = document.querySelectorAll('.category-header');
        
        categoryHeaders.forEach(header => {
            header.addEventListener('click', () => {
                this.toggleCategory(header);
            });
        });
        
        // Auto-open active category
        const activeItem = document.querySelector('.sidebar-nav li.active');
        if (activeItem) {
            const categoryItems = activeItem.closest('.category-items');
            if (categoryItems) {
                categoryItems.classList.remove('collapsed');
                const icon = categoryItems.previousElementSibling?.querySelector('.toggle-icon');
                if (icon) icon.classList.remove('rotated');
            }
        }
    },
    
    toggleCategory(header) {
        const items = header.nextElementSibling;
        const icon = header.querySelector('.toggle-icon');
        
        if (!items || !icon) return;
        
        items.classList.toggle('collapsed');
        icon.classList.toggle('rotated');
        
        // Save state
        const categoryId = this.getCategoryId(header);
        if (categoryId) {
            if (items.classList.contains('collapsed')) {
                localStorage.setItem(`sidebar-${categoryId}`, 'collapsed');
            } else {
                localStorage.removeItem(`sidebar-${categoryId}`);
            }
        }
    },
    
    getCategoryId(header) {
        const items = header.nextElementSibling;
        const firstLink = items?.querySelector('li a');
        if (firstLink) {
            const href = firstLink.getAttribute('href');
            const parts = href.split('/');
            return parts[1] || 'category';
        }
        return null;
    },
    
    setupMobileHandling() {
        const sidebar = document.getElementById('adminSidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        
        if (!sidebar) return;
        
        // Close sidebar when clicking outside (mobile only)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !toggleBtn?.contains(e.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        });
        
        // Close sidebar after clicking any link (mobile)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-open');
                }
            });
        });
    },
    
    restoreState() {
        // Restore saved category states
        const categories = document.querySelectorAll('.category-items');
        categories.forEach(items => {
            const firstLink = items.querySelector('li a');
            if (firstLink) {
                const href = firstLink.getAttribute('href');
                const categoryId = href.split('/')[1];
                const isCollapsed = localStorage.getItem(`sidebar-${categoryId}`) === 'collapsed';
                
                // Don't close if it contains active item
                const hasActive = items.closest('.sidebar-category')?.querySelector('li.active');
                if (isCollapsed && !hasActive) {
                    items.classList.add('collapsed');
                    const icon = items.previousElementSibling?.querySelector('.toggle-icon');
                    if (icon) icon.classList.add('rotated');
                }
            }
        });
        
        // Restore sidebar open/close state
        if (!AdminState.sidebarOpen) {
            document.body.classList.add('sidebar-closed');
            const sidebar = document.getElementById('adminSidebar');
            const mainContent = document.querySelector('.admin-main');
            if (sidebar) sidebar.classList.add('sidebar-closed');
            if (mainContent) mainContent.classList.add('sidebar-closed');
        }
    }
};

// Make toggleCategory globally available for onclick attributes
window.toggleCategory = function(header) {
    Sidebar.toggleCategory(header);
};

/* ========================================
   USER MENU DROPDOWN
   ======================================== */

const UserMenu = {
    init() {
        const menuToggle = document.getElementById('userMenuToggle');
        const dropdown = document.getElementById('userDropdown');
        
        if (!menuToggle || !dropdown) return;
        
        // Toggle on click
        menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        
        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!menuToggle.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
        
        // Close when clicking a link
        dropdown.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                dropdown.style.display = 'none';
            });
        });
    }
};

/* ========================================
   COLOR PICKER FUNCTIONALITY
   ======================================== */

const ColorPicker = {
    init() {
        this.setupColorPickers();
    },
    
    setupColorPickers() {
        // Find all color picker sets
        const colorSets = [
            { picker: 'color_picker_primaire', input: 'theme_couleur_primaire', preview: 'preview_theme_couleur_primaire', live: 'preview_primary' },
            { picker: 'color_picker_secondaire', input: 'theme_couleur_secondaire', preview: 'preview_theme_couleur_secondaire', live: 'preview_secondary' },
            { picker: 'color_picker_nav', input: 'theme_couleur_nav', preview: 'preview_theme_couleur_nav', live: 'preview_nav' },
            { picker: 'color_picker_footer', input: 'theme_couleur_footer', preview: 'preview_theme_couleur_footer', live: 'preview_footer' }
        ];
        
        colorSets.forEach(set => {
            this.syncColorPicker(set.picker, set.input, set.preview, set.live);
        });
    },
    
    syncColorPicker(pickerId, inputId, previewId, livePreviewId) {
        const picker = document.getElementById(pickerId);
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const livePreview = document.getElementById(livePreviewId);
        
        if (!picker || !input) return;
        
        // When color picker changes
        picker.addEventListener('input', () => {
            const value = picker.value;
            input.value = value;
            if (preview) {
                preview.style.backgroundColor = value;
                preview.style.transition = 'background-color 0.3s ease';
            }
            if (livePreview) {
                livePreview.style.backgroundColor = value;
                livePreview.style.transition = 'background-color 0.3s ease';
            }
        });
        
        // When text input changes
        input.addEventListener('input', () => {
            let value = input.value.trim();
            
            // Add # if missing
            if (value && !value.startsWith('#')) {
                value = '#' + value;
            }
            
            // Validate hex color
            if (/^#[0-9A-F]{6}$/i.test(value)) {
                picker.value = value;
                if (preview) {
                    preview.style.backgroundColor = value;
                    preview.style.transition = 'background-color 0.3s ease';
                }
                if (livePreview) {
                    livePreview.style.backgroundColor = value;
                    livePreview.style.transition = 'background-color 0.3s ease';
                }
            }
        });
    }
};

/* ========================================
   IMAGE PREVIEW FUNCTIONALITY
   ======================================== */

const ImagePreview = {
    init() {
        const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => this.previewImage(e.target));
        });
    },
    
    previewImage(input) {
        if (!input.files || !input.files[0]) return;
        
        const file = input.files[0];
        const reader = new FileReader();
        const previewBox = input.closest('.image-upload-container')?.querySelector('.image-preview-box');
        
        if (!previewBox) return;
        
        reader.onload = (e) => {
            previewBox.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="preview-image">
                <div class="image-info">
                    <span class="image-filename">${file.name}</span>
                </div>
            `;
        };
        
        reader.readAsDataURL(file);
    }
};

// Make previewImage globally available for inline onchange attributes
window.previewImage = function(input) {
    ImagePreview.previewImage(input);
};

/* ========================================
   TABLE SEARCH FUNCTIONALITY
   ======================================== */

const TableSearch = {
    init() {
        const searchInputs = document.querySelectorAll('.table-search, input[id$="-search"]');
        
        searchInputs.forEach(input => {
            const tableId = input.id.replace('-search', '');
            const table = document.getElementById(tableId);
            
            if (!table) return;
            
            input.addEventListener('keyup', () => {
                this.filterTable(table, input.value);
            });
        });
    },
    
    filterTable(table, searchTerm) {
        const filter = searchTerm.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            // Skip if it's an empty state row
            if (row.classList.contains('empty-state-row')) return;
            
            const text = row.textContent.toLowerCase();
            const isVisible = text.includes(filter);
            
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });
        
        this.updateEmptyState(table, visibleCount);
    },
    
    updateEmptyState(table, visibleCount) {
        let emptyRow = table.querySelector('.empty-state-row');
        
        if (visibleCount === 0) {
            if (!emptyRow) {
                const tbody = table.querySelector('tbody');
                const colspan = table.querySelectorAll('thead th').length;
                
                emptyRow = document.createElement('tr');
                emptyRow.className = 'empty-state-row';
                emptyRow.innerHTML = `<td colspan="${colspan}" class="empty-state">Aucun résultat trouvé</td>`;
                tbody.appendChild(emptyRow);
            }
        } else {
            if (emptyRow) {
                emptyRow.remove();
            }
        }
    }
};

/* ========================================
   FILTER FUNCTIONALITY (for Contact page, etc.)
   ======================================== */

const Filters = {
    init() {
        this.setupContactFilters();
        this.setupGenericFilters();
    },
    
    setupContactFilters() {
        const filterStatut = document.getElementById('filter-statut');
        const filterSujet = document.getElementById('filter-sujet');
        const resetBtn = document.getElementById('reset-filters');
        const table = document.getElementById('contact-table');
        
        if (!table || !filterStatut || !filterSujet) return;
        
        const applyFilters = () => {
            const rows = table.querySelectorAll('tbody tr');
            const statutValue = filterStatut.value.toLowerCase();
            const sujetValue = filterSujet.value.toLowerCase();
            
            rows.forEach(row => {
                if (row.classList.contains('empty-state-row')) return;
                
                const statut = row.cells[4]?.textContent.toLowerCase() || '';
                const sujet = row.cells[2]?.textContent.toLowerCase() || '';
                
                const matchStatut = !statutValue || statut.includes(statutValue.replace('_', ' '));
                const matchSujet = !sujetValue || sujet.includes(sujetValue);
                
                row.style.display = (matchStatut && matchSujet) ? '' : 'none';
            });
        };
        
        filterStatut.addEventListener('change', applyFilters);
        filterSujet.addEventListener('change', applyFilters);
        
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                filterStatut.value = '';
                filterSujet.value = '';
                applyFilters();
            });
        }
    },
    
    setupGenericFilters() {
        // Setup any other filter dropdowns with class 'filter-select'
        const filterSelects = document.querySelectorAll('.filter-select:not(#filter-statut):not(#filter-sujet)');
        
        filterSelects.forEach(select => {
            select.addEventListener('change', () => {
                const tableId = select.getAttribute('data-table');
                if (tableId) {
                    const table = document.getElementById(tableId);
                    if (table) {
                        // Generic filtering logic
                        this.applyGenericFilter(table, select);
                    }
                }
            });
        });
    },
    
    applyGenericFilter(table, filterSelect) {
        const filterValue = filterSelect.value.toLowerCase();
        const filterColumn = parseInt(filterSelect.getAttribute('data-column')) || 0;
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            if (row.classList.contains('empty-state-row')) return;
            
            const cell = row.cells[filterColumn];
            if (!cell) return;
            
            const cellText = cell.textContent.toLowerCase();
            row.style.display = !filterValue || cellText.includes(filterValue) ? '' : 'none';
        });
    }
};

/* ========================================
   ALERTS MANAGEMENT
   ======================================== */

const Alerts = {
    init() {
        const alerts = document.querySelectorAll('.alert');
        
        alerts.forEach(alert => {
            this.setupAlert(alert);
        });
    },
    
    setupAlert(alert) {
        // Add close button if not present
        if (!alert.querySelector('.alert-close')) {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = () => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            };
            alert.appendChild(closeBtn);
        }
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    },
    
    show(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert ${type}`;
        
        const icon = this.getIcon(type);
        alert.innerHTML = `
            <i class="${icon}"></i>
            ${message}
        `;
        
        const container = document.querySelector('.admin-main') || document.body;
        container.insertBefore(alert, container.firstChild);
        
        this.setupAlert(alert);
    },
    
    getIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }
};

/* ========================================
   FORM VALIDATION
   ======================================== */

const FormValidation = {
    init() {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    },
    
    validateForm(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#dc3545';
                isValid = false;
                
                // Remove error styling after user starts typing
                field.addEventListener('input', () => {
                    field.style.borderColor = '';
                }, { once: true });
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            Alerts.show('Veuillez remplir tous les champs obligatoires', 'error');
        }
        
        return isValid;
    }
};

/* ========================================
   DELETE CONFIRMATIONS
   ======================================== */

const DeleteConfirmation = {
    init() {
        // For links/buttons with confirm attribute or class
        const deleteElements = document.querySelectorAll('.btn-delete, .btn-action.delete, [data-confirm]');
        
        deleteElements.forEach(element => {
            element.addEventListener('click', (e) => {
                const message = element.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
                
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    }
};

/* ========================================
   TOOLTIPS
   ======================================== */

const Tooltips = {
    init() {
        const tooltipElements = document.querySelectorAll('[data-tooltip], [title]');
        
        tooltipElements.forEach(element => {
            const tooltipText = element.getAttribute('data-tooltip') || element.getAttribute('title');
            if (!tooltipText) return;
            
            // Remove default title to prevent browser tooltip
            if (element.hasAttribute('title')) {
                element.setAttribute('data-tooltip', tooltipText);
                element.removeAttribute('title');
            }
            
            element.addEventListener('mouseenter', () => this.show(element, tooltipText));
            element.addEventListener('mouseleave', () => this.hide(element));
        });
    },
    
    show(element, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.textContent = text;
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.position = 'absolute';
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
        tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
        
        element._tooltip = tooltip;
    },
    
    hide(element) {
        if (element._tooltip) {
            element._tooltip.remove();
            element._tooltip = null;
        }
    }
};

/* ========================================
   STATS COUNTER ANIMATION
   ======================================== */

const StatsCounter = {
    init() {
        const statNumbers = document.querySelectorAll('.stat-content h3, .stat-value');
        
        statNumbers.forEach(stat => {
            const targetValue = parseInt(stat.textContent);
            if (!isNaN(targetValue) && targetValue > 0) {
                this.animateCounter(stat, 0, targetValue, 1000);
            }
        });
    },
    
    animateCounter(element, start, end, duration) {
        const range = end - start;
        const increment = end > start ? 1 : -1;
        const stepTime = Math.abs(Math.floor(duration / range));
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            element.textContent = current;
            
            if (current === end) {
                clearInterval(timer);
            }
        }, stepTime);
    }
};

/* ========================================
   PAGINATION
   ======================================== */

const Pagination = {
    init() {
        const paginationBtns = document.querySelectorAll('.pagination-number');
        
        paginationBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                paginationBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    }
};

/* ========================================
   UTILITY FUNCTIONS
   ======================================== */

const Utils = {
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '15px 20px',
            borderRadius: '8px',
            color: '#fff',
            zIndex: '9999',
            animation: 'slideInRight 0.3s ease'
        });
        
        const colors = {
            success: '#2eb85c',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#1e4fa3'
        };
        toast.style.background = colors[type] || colors.info;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },
    
    copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                this.showToast('Copié dans le presse-papiers', 'success');
            });
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            textarea.remove();
            this.showToast('Copié dans le presse-papiers', 'success');
        }
    },
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

// Make utility functions globally available
window.showToast = (message, type) => Utils.showToast(message, type);
window.copyToClipboard = (text) => Utils.copyToClipboard(text);

/* ========================================
   MAIN INITIALIZATION
   ======================================== */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize state
    AdminState.init();
    
    // Initialize all modules
    Sidebar.init();
    UserMenu.init();
    ColorPicker.init();
    ImagePreview.init();
    TableSearch.init();
    Filters.init();
    Alerts.init();
    FormValidation.init();
    DeleteConfirmation.init();
    Tooltips.init();
    StatsCounter.init();
    Pagination.init();
    
    console.log('✅ Admin interface initialized successfully');
});

/* ========================================
   EXPORT FOR EXTERNAL USE
   ======================================== */

window.Admin = {
    Sidebar,
    UserMenu,
    ColorPicker,
    ImagePreview,
    TableSearch,
    Filters,
    Alerts,
    FormValidation,
    Utils,
    StatsCounter
};