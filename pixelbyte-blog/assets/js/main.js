/**
 * PIXELBYTE - Unified JavaScript
 * Main script file for homepage, blog, and store sections
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    initMobileMenu();
    
    // Header scroll effect
    initHeaderScrollEffect();
    
    // Initialize animations
    initAnimations();
    
    // Initialize category filters if they exist
    if(document.querySelector('.filter-btn')) {
        initCategoryFilters();
    }
    
    // Initialize custom cursor on desktop
    if(window.innerWidth >= 1024) {
        initCustomCursor();
    }
    
    // Add smooth hover effect on cards
    initCardHoverEffects();
});

/**
 * Mobile menu toggle functionality
 */
function initMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if(!mobileMenuBtn || !mobileMenu) return;
    
    mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('active');
        document.body.classList.toggle('no-scroll');
        
        // Transform hamburger to X
        const spans = mobileMenuBtn.querySelectorAll('span');
        
        if (mobileMenu.classList.contains('active')) {
            document.body.style.overflow = 'hidden';
            spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
            spans[1].style.opacity = '0';
            spans[2].style.transform = 'rotate(-45deg) translate(8px, -8px)';
        } else {
            document.body.style.overflow = '';
            spans[0].style.transform = 'none';
            spans[1].style.opacity = '1';
            spans[2].style.transform = 'none';
        }
    });
    
    // Close mobile menu when clicking on a nav link
    const mobileLinks = document.querySelectorAll('.mobile-menu a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = '';
            
            // Reset hamburger icon
            const spans = mobileMenuBtn.querySelectorAll('span');
            spans.forEach(span => {
                span.style.transform = 'none';
                span.style.opacity = '1';
            });
        });
    });
}

/**
 * Header scroll effect
 */
function initHeaderScrollEffect() {
    const header = document.querySelector('header');
    if(!header) return;
    
    window.addEventListener('scroll', () => {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 50) {
            header.style.background = 'rgba(255, 255, 255, 0.95)';
            header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.1)';
        } else {
            header.style.background = 'rgba(255, 255, 255, 0.8)';
            header.style.boxShadow = '0 5px 20px rgba(0, 0, 0, 0.05)';
        }
    });
}

/**
 * Initialize scroll-based animations
 */
function initAnimations() {
    // Fade in elements as they scroll into view
    const fadeElements = document.querySelectorAll('.fade-in-up');
    
    if(fadeElements.length === 0) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    fadeElements.forEach(element => {
        observer.observe(element);
    });
}

/**
 * Initialize category filters on blog or store pages
 */
function initCategoryFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const items = document.querySelectorAll('[data-category]');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Remove active class from all buttons
            filterBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            btn.classList.add('active');
            
            // Get filter value
            const filter = btn.getAttribute('data-filter');
            
            // Step 1: Fade out all items
            items.forEach(item => {
                item.classList.add('filtering-fade-out');
            });
            
            // Step 2: After fade out completes, show/hide appropriate items
            setTimeout(() => {
                items.forEach(item => {
                    if (filter === 'all') {
                        // Show all items
                        item.style.display = item.classList.contains('featured-post') ? 'grid' : 'block';
                    } else {
                        // Filter by category
                        if (item.getAttribute('data-category') === filter) {
                            item.style.display = item.classList.contains('featured-post') ? 'grid' : 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                    
                    // Remove fade out class from visible items
                    if (item.style.display !== 'none') {
                        item.classList.remove('filtering-fade-out');
                    }
                });
                
                // Step 3: Fade in visible items
                setTimeout(() => {
                    items.forEach(item => {
                        if (item.style.display !== 'none') {
                            item.classList.add('filtering-fade-in');
                        }
                    });
                }, 50);
            }, 300); // Match this to CSS transition duration
        });
    });
    
    // Handle category links in sidebar if they exist
    const categoryLinks = document.querySelectorAll('.category-filter-link');
    
    categoryLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const categoryName = this.getAttribute('data-category');
            
            // Find and click the corresponding filter button
            const filterButton = document.querySelector(`.filter-btn[data-filter="category-${categoryName}"]`);
            if (filterButton) {
                filterButton.click();
                
                // Scroll back to top of posts section
                document.querySelector('.blog-filters').scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Initialize custom cursor effect for desktop
 */
function initCustomCursor() {
    const cursor = document.createElement('div');
    cursor.classList.add('cursor');
    
    const follower = document.createElement('div');
    follower.classList.add('cursor-follower');
    
    document.body.appendChild(cursor);
    document.body.appendChild(follower);
    
    document.addEventListener('mousemove', e => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
        
        setTimeout(() => {
            follower.style.left = e.clientX + 'px';
            follower.style.top = e.clientY + 'px';
        }, 100);
    });
    
   // Add active class on hover over links and buttons
    const links = document.querySelectorAll('a, .btn');
    
    links.forEach(link => {
        link.addEventListener('mouseenter', () => {
            cursor.classList.add('active');
            follower.classList.add('active');
        });
        
        link.addEventListener('mouseleave', () => {
            cursor.classList.remove('active');
            follower.classList.remove('active');
        });
    });
}

/**
 * Initialize subtle hover effects on cards
 */
function initCardHoverEffects() {
    const cards = document.querySelectorAll('.blog-card, .product-card, .glass-card, .sidebar-section');
    
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Calculate rotation based on mouse position (subtle effect)
            const rotateY = (x / rect.width - 0.5) * 3; // Max 1.5 degrees
            const rotateX = (y / rect.height - 0.5) * -3; // Max 1.5 degrees
            
            // Apply subtle rotation effect
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px)`;
        });
        
        card.addEventListener('mouseleave', () => {
            // Reset transform on mouse leave
            card.style.transform = '';
        });
    });
}

/**
 * Add to cart functionality for store
 * @param {number} productId - The ID of the product to add
 */
function addToCart(productId) {
    // Check if cart exists in localStorage
    let cart = JSON.parse(localStorage.getItem('pixelbyte_cart')) || [];
    
    // Check if product already exists in cart
    const existingProductIndex = cart.findIndex(item => item.id === productId);
    
    if (existingProductIndex > -1) {
        // Increment quantity if product already exists
        cart[existingProductIndex].quantity++;
    } else {
        // Add new product to cart with quantity 1
        cart.push({
            id: productId,
            quantity: 1
        });
    }
    
    // Save updated cart to localStorage
    localStorage.setItem('pixelbyte_cart', JSON.stringify(cart));
    
    // Show confirmation message
    showCartNotification();
    
    // Update cart count in the header
    updateCartCount();
}

/**
 * Show a notification when a product is added to cart
 */
function showCartNotification() {
    // Create notification element if it doesn't exist
    let notification = document.querySelector('.cart-notification');
    
    if (!notification) {
        notification = document.createElement('div');
        notification.classList.add('cart-notification');
        notification.innerHTML = 'Product added to cart!';
        document.body.appendChild(notification);
    }
    
    // Show notification
    notification.classList.add('show');
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

/**
 * Update the cart count displayed in the header
 */
function updateCartCount() {
    // Get cart from localStorage
    const cart = JSON.parse(localStorage.getItem('pixelbyte_cart')) || [];
    
    // Calculate total quantity
    const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
    
    // Update count element if it exists
    const cartCountElement = document.querySelector('.cart-count');
    
    if (cartCountElement) {
        cartCountElement.textContent = cartCount;
        
        // Show or hide based on count
        if (cartCount > 0) {
            cartCountElement.classList.add('show');
        } else {
            cartCountElement.classList.remove('show');
        }
    }
}

/**
 * Form validation for contact and checkout forms
 * @param {HTMLFormElement} form - The form to validate
 * @returns {boolean} - Whether the form is valid
 */
function validateForm(form) {
    let isValid = true;
    
    // Get all required inputs
    const requiredInputs = form.querySelectorAll('[required]');
    
    requiredInputs.forEach(input => {
        // Remove any existing error messages
        const existingError = input.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Remove error class
        input.classList.remove('error');
        
        // Check if input is empty
        if (input.value.trim() === '') {
            isValid = false;
            input.classList.add('error');
            
            // Add error message
            const errorMessage = document.createElement('div');
            errorMessage.classList.add('error-message');
            errorMessage.textContent = 'This field is required';
            input.parentNode.appendChild(errorMessage);
        }
        
        // Email validation
        if (input.type === 'email' && input.value.trim() !== '') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                isValid = false;
                input.classList.add('error');
                
                // Add error message
                const errorMessage = document.createElement('div');
                errorMessage.classList.add('error-message');
                errorMessage.textContent = 'Please enter a valid email address';
                input.parentNode.appendChild(errorMessage);
            }
        }
    });
    
    return isValid;
}

/**
 * Newsletter subscription handler
 * @param {Event} e - The submit event
 */
function handleNewsletterSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    
    if (validateForm(form)) {
        // Get email value
        const email = form.querySelector('input[type="email"]').value;
        
        // Here you would typically send this to a server endpoint
        console.log('Subscribing email:', email);
        
        // For now, let's show a success message
        const formContainer = form.parentNode;
        form.style.display = 'none';
        
        const successMessage = document.createElement('div');
        successMessage.classList.add('success-message');
        successMessage.innerHTML = `
            <h4>Thank you for subscribing!</h4>
            <p>You will now receive our latest updates and articles at ${email}.</p>
        `;
        
        formContainer.appendChild(successMessage);
    }
}

/**
 * Initialize search functionality
 */
function initSearch() {
    const searchForm = document.querySelector('.search-form');
    
    if (!searchForm) return;
    
    searchForm.addEventListener('submit', (e) => {
        const searchInput = searchForm.querySelector('.search-input');
        
        // Prevent empty search submissions
        if (searchInput.value.trim() === '') {
            e.preventDefault();
            searchInput.focus();
        }
    });
}

/**
 * Handle social media sharing
 * @param {string} platform - The social media platform
 * @param {string} url - The URL to share
 * @param {string} title - The title to share
 */
function shareSocial(platform, url, title) {
    let shareUrl;
    
    switch (platform) {
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
            break;
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            break;
        case 'linkedin':
            shareUrl = `https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}`;
            break;
        case 'pinterest':
            // For Pinterest, we'd need an image URL as well
            const imageUrl = document.querySelector('.blog-featured-img')?.src || '';
            shareUrl = `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(url)}&media=${encodeURIComponent(imageUrl)}&description=${encodeURIComponent(title)}`;
            break;
    }
    
    // Open in a new window
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

/**
 * Initialize product-specific functionality
 */
function initProductFunctions() {
    // Add to cart button functionality
    const addToCartBtn = document.getElementById('add-to-cart');
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = parseInt(addToCartBtn.getAttribute('data-product-id'));
            addToCart(productId);
        });
    }
    
    // Update cart count on page load
    updateCartCount();
}

// Initialize additional functionality if needed
document.addEventListener('DOMContentLoaded', function() {
    // Initialize search
    initSearch();
    
    // Initialize product functions
    initProductFunctions();
    
    // Initialize newsletter forms
    const newsletterForms = document.querySelectorAll('.newsletter-form');
    newsletterForms.forEach(form => {
        form.addEventListener('submit', handleNewsletterSubmit);
    });
    
    // Initialize contact forms
    const contactForms = document.querySelectorAll('.contact-form');
    contactForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });
    
    // Initialize checkout form
    const checkoutForm = document.querySelector('.checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', (e) => {
            if (!validateForm(checkoutForm)) {
                e.preventDefault();
            }
        });
    }
    
    // Initialize social share buttons
    const socialButtons = document.querySelectorAll('.social-sharing a');
    socialButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const platform = button.classList[0]; // The first class is the platform name
            const url = window.location.href;
            const title = document.title;
            
            shareSocial(platform, url, title);
        });
    });
});

// Handle theme preference if implemented
function setThemePreference(theme) {
    localStorage.setItem('pixelbyte_theme', theme);
    document.documentElement.setAttribute('data-theme', theme);
}

// Check for saved theme preference on page load
function loadThemePreference() {
    const savedTheme = localStorage.getItem('pixelbyte_theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        // If no preference saved but system is using dark mode
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}

// Call theme preference loader (if dark mode is implemented)
// loadThemePreference();