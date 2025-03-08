/**
 * PIXELBYTE Admin Panel JavaScript
 * Handles sidebar toggling, dropdowns, and other interactive elements
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const topHeader = document.querySelector('.top-header');
    const body = document.body;

    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('expanded');
            topHeader.classList.toggle('sidebar-expanded');
            body.classList.toggle('sidebar-expanded');
        });
    }

    // User dropdown functionality
    const userDropdown = document.querySelector('.user-info');
    const dropdownContent = document.querySelector('.user-dropdown-content');

    if (userDropdown && dropdownContent) {
        userDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownContent.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            if (dropdownContent.classList.contains('show')) {
                dropdownContent.classList.remove('show');
            }
        });
    }

    // Form validation
    const forms = document.querySelectorAll('form.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    });

    // DataTable initialization (if available)
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            }
        });
    }

    // Initialize TinyMCE if exists on page
    if (typeof tinymce !== 'undefined' && document.querySelector('.tinymce-editor')) {
        tinymce.init({
            selector: '.tinymce-editor',
            height: 400,
            menubar: false,
            plugins: [
                'advlist autolink lists link image charmap print preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });
    }

    // Alert auto-close functionality
    const alerts = document.querySelectorAll('.alert-dismissible');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade-out');
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Image preview functionality
    const imageInputs = document.querySelectorAll('.image-upload-input');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewContainer = this.parentElement.querySelector('.image-preview');
            if (previewContainer) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        previewContainer.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-fluid">`;
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    });

    // Toggle for collapsible sections
    const toggleButtons = document.querySelectorAll('.collapse-toggle');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const target = document.querySelector(targetId);
            
            if (target) {
                target.classList.toggle('show');
                this.classList.toggle('active');
                
                // Change icon if present
                const icon = this.querySelector('i.toggle-icon');
                if (icon) {
                    if (target.classList.contains('show')) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    } else {
                        icon.classList.remove('fa-chevron-up');
                        icon.classList.add('fa-chevron-down');
                    }
                }
            }
        });
    });

    // Tooltip initialization
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            
            const tip = document.createElement('div');
            tip.classList.add('tooltip');
            tip.textContent = text;
            
            document.body.appendChild(tip);
            
            const rect = this.getBoundingClientRect();
            const tipRect = tip.getBoundingClientRect();
            
            tip.style.top = `${rect.top - tipRect.height - 10}px`;
            tip.style.left = `${rect.left + (rect.width / 2) - (tipRect.width / 2)}px`;
            
            this.addEventListener('mouseleave', function() {
                document.body.removeChild(tip);
            }, { once: true });
        });
    });

    // Active link highlighting based on current URL
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.sidebar-nav-link');

// Highlight the active link in the sidebar
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        
        // Check if the current path starts with the link path
        // This handles both exact matches and active parent sections
        if (linkPath && (
            currentPath === linkPath || 
            (linkPath !== '/' && currentPath.startsWith(linkPath)) ||
            (currentPath.includes(linkPath) && linkPath !== '/')
        )) {
            link.classList.add('active');
            
            // If in a nested menu, also highlight parent
            const parentSection = link.closest('.collapse');
            if (parentSection) {
                const trigger = document.querySelector(`[data-target="#${parentSection.id}"]`);
                if (trigger) {
                    parentSection.classList.add('show');
                    trigger.classList.add('active');
                    
                    const icon = trigger.querySelector('i.toggle-icon');
                    if (icon) {
                        icon.classList.remove('fa-chevron-down');
                        icon.classList.add('fa-chevron-up');
                    }
                }
            }
        }
    });

    // Slug generator for title fields
    const titleInputs = document.querySelectorAll('input[data-slug-source]');
    
    titleInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const targetId = this.getAttribute('data-slug-source');
            const slugInput = document.getElementById(targetId);
            
            if (slugInput && !slugInput.value) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove special chars
                    .replace(/\s+/g, '-')     // Replace spaces with hyphens
                    .replace(/-+/g, '-');     // Replace multiple hyphens with single hyphen
                
                slugInput.value = slug;
            }
        });
    });

    // File upload with custom styling
    const fileInputs = document.querySelectorAll('.custom-file-input');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const fileLabel = this.nextElementSibling;
            
            if (fileLabel && this.files && this.files.length > 0) {
                fileLabel.textContent = this.files[0].name;
            }
        });
    });

    // Confirmation dialogs for delete actions
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-save drafts for long forms
    const autosaveForms = document.querySelectorAll('form[data-autosave]');
    
    autosaveForms.forEach(form => {
        const autosaveInterval = parseInt(form.getAttribute('data-autosave')) || 30000; // Default to 30 seconds
        const autosaveUrl = form.getAttribute('data-autosave-url');
        
        if (autosaveUrl) {
            // Set up interval for auto-saving
            const intervalId = setInterval(() => {
                const formData = new FormData(form);
                formData.append('autosave', 'true');
                
                fetch(autosaveUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const autosaveStatus = form.querySelector('.autosave-status');
                        if (autosaveStatus) {
                            autosaveStatus.textContent = 'Draft saved at ' + new Date().toLocaleTimeString();
                            autosaveStatus.classList.add('show');
                            
                            setTimeout(() => {
                                autosaveStatus.classList.remove('show');
                            }, 3000);
                        }
                    }
                })
                .catch(error => console.error('Autosave error:', error));
            }, autosaveInterval);
            
            // Clean up interval when user submits the form
            form.addEventListener('submit', () => {
                clearInterval(intervalId);
            });
        }
    });

    // Handle bulk actions in tables
    const bulkActionForm = document.querySelector('.bulk-action-form');
    
    if (bulkActionForm) {
        const actionSelect = bulkActionForm.querySelector('.bulk-action-select');
        const checkboxes = document.querySelectorAll('input[name="bulk_items[]"]');
        const selectAll = document.querySelector('.select-all-checkbox');
        
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                });
            });
        }
        
        bulkActionForm.addEventListener('submit', function(e) {
            const selectedAction = actionSelect.value;
            const selectedItems = document.querySelectorAll('input[name="bulk_items[]"]:checked');
            
            if (!selectedAction) {
                e.preventDefault();
                alert('Please select an action to perform');
                return;
            }
            
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Please select at least one item');
                return;
            }
            
            if (selectedAction === 'delete' && !confirm('Are you sure you want to delete the selected items?')) {
                e.preventDefault();
                return;
            }
        });
    }

    // Tag input functionality
    const tagInputs = document.querySelectorAll('.tag-input');
    
    tagInputs.forEach(container => {
        const input = container.querySelector('input[type="text"]');
        const hiddenInput = container.querySelector('input[type="hidden"]');
        const tagList = container.querySelector('.tag-list');
        
        if (input && hiddenInput && tagList) {
            // Initialize from existing value
            if (hiddenInput.value) {
                const tags = hiddenInput.value.split(',');
                
                tags.forEach(tag => {
                    if (tag.trim()) {
                        addTag(tag.trim());
                    }
                });
            }
            
            // Add tag when pressing Enter
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    
                    const tag = this.value.trim();
                    if (tag) {
                        addTag(tag);
                        this.value = '';
                    }
                }
            });
            
            // Add tag when input loses focus
            input.addEventListener('blur', function() {
                const tag = this.value.trim();
                if (tag) {
                    addTag(tag);
                    this.value = '';
                }
            });
            
            function addTag(text) {
                // Create tag element
                const tag = document.createElement('span');
                tag.classList.add('tag');
                tag.innerHTML = `
                    ${text}
                    <button type="button" class="tag-remove">&times;</button>
                `;
                tagList.appendChild(tag);
                
                // Set up remove button
                const removeButton = tag.querySelector('.tag-remove');
                removeButton.addEventListener('click', function() {
                    tag.remove();
                    updateHiddenInput();
                });
                
                // Update hidden input
                updateHiddenInput();
            }
            
            function updateHiddenInput() {
                const tags = Array.from(tagList.querySelectorAll('.tag'))
                    .map(tag => tag.textContent.trim())
                    .join(',');
                
                hiddenInput.value = tags;
            }
        }
    });
});