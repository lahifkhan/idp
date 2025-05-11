/**
 * FluenceShop - eCommerce for Influencers
 * Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuClose = document.getElementById('mobile-menu-close');
    
    if(menuToggle && mobileMenu && mobileMenuClose) {
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
        
        mobileMenuClose.addEventListener('click', function() {
            mobileMenu.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
    
    // Back to Top Button
    const backToTopButton = document.getElementById('back-to-top');
    
    if(backToTopButton) {
        window.addEventListener('scroll', function() {
            if(window.scrollY > 300) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        });
        
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Add to Cart Buttons
    const addToCartButtons = document.querySelectorAll('.btn-add-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            const quantity = 1; // Default quantity
            
            addProductToCart(productId, quantity);
        });
    });
    
    // Add Product to Cart Function
    function addProductToCart(productId, quantity) {
        fetch('includes/cart_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=add&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showNotification('Product added to cart!', 'success');
                updateCartCount(data.cart_count);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
    }
    
    // Show Notification Function
    function showNotification(message, type) {
        // Check if notification already exists and remove it
        const existingNotification = document.querySelector('.notification');
        if(existingNotification) {
            document.body.removeChild(existingNotification);
        }
        
        // Create new notification
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
    
    // Update Cart Count Function
    function updateCartCount(count) {
        const cartCountElement = document.querySelector('.cart-count');
        
        if(cartCountElement) {
            cartCountElement.textContent = count;
            cartCountElement.classList.add('updated');
            
            setTimeout(() => {
                cartCountElement.classList.remove('updated');
            }, 300);
        }
    }
    
    // Product Image Gallery
    const productThumbnails = document.querySelectorAll('.thumbnail');
    const mainProductImage = document.getElementById('main-product-image');
    
    if(productThumbnails.length > 0 && mainProductImage) {
        productThumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                const imageUrl = this.getAttribute('data-image');
                
                // Update main image
                mainProductImage.src = imageUrl;
                
                // Update active thumbnail
                productThumbnails.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Product Tabs
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    if(tabButtons.length > 0 && tabPanes.length > 0) {
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and panes
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Add active class to clicked button and corresponding pane
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
            });
        });
    }
    
    // Quantity Selectors
    const quantityInputs = document.querySelectorAll('.quantity-selector input');
    const minusButtons = document.querySelectorAll('.quantity-btn.minus');
    const plusButtons = document.querySelectorAll('.quantity-btn.plus');
    
    if(quantityInputs.length > 0) {
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                const min = parseInt(this.getAttribute('min') || 1);
                const max = parseInt(this.getAttribute('max') || 100);
                let value = parseInt(this.value);
                
                if(isNaN(value) || value < min) {
                    this.value = min;
                } else if(value > max) {
                    this.value = max;
                }
            });
        });
        
        minusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input');
                let value = parseInt(input.value);
                let min = parseInt(input.getAttribute('min') || 1);
                
                if(value > min) {
                    input.value = value - 1;
                    
                    // Trigger change event
                    const event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                }
            });
        });
        
        plusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input');
                let value = parseInt(input.value);
                let max = parseInt(input.getAttribute('max') || 100);
                
                if(value < max) {
                    input.value = value + 1;
                    
                    // Trigger change event
                    const event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                }
            });
        });
    }
    
    // Newsletter Form
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if(newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('input[type="email"]');
            const email = emailInput.value.trim();
            
            if(email === '') {
                showNotification('Please enter your email address.', 'error');
                return;
            }
            
            // Simulate form submission (in a real application, you would send this to a server)
            emailInput.value = '';
            showNotification('Thank you for subscribing to our newsletter!', 'success');
        });
    }
    
    // Initialize animations for elements
    const fadeInElements = document.querySelectorAll('.fade-in');
    const slideUpElements = document.querySelectorAll('.slide-up');
    
    function handleScrollAnimation() {
        const triggerBottom = window.innerHeight * 0.8;
        
        fadeInElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            
            if(elementTop < triggerBottom) {
                element.style.opacity = 1;
            }
        });
        
        slideUpElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            
            if(elementTop < triggerBottom) {
                element.style.transform = 'translateY(0)';
                element.style.opacity = 1;
            }
        });
    }
    
    // Run once on load
    handleScrollAnimation();
    
    // Run on scroll
    window.addEventListener('scroll', handleScrollAnimation);
});

// Star Rating Functionality
function initRatingStars() {
    const ratingOptions = document.querySelector('.rating-options');
    
    if(ratingOptions) {
        const stars = ratingOptions.querySelectorAll('label');
        const ratingInputs = ratingOptions.querySelectorAll('input');
        
        stars.forEach((star, index) => {
            star.addEventListener('mouseover', function() {
                // Reset all stars
                stars.forEach(s => s.style.color = '');
                
                // Highlight stars up to the hovered one
                for(let i = 0; i <= index; i++) {
                    stars[stars.length - 1 - i].style.color = 'var(--color-warning)';
                }
            });
            
            star.addEventListener('mouseout', function() {
                // Reset star colors to default state based on selected rating
                stars.forEach(s => s.style.color = '');
                
                // Find checked input
                let checkedInput = null;
                ratingInputs.forEach(input => {
                    if(input.checked) {
                        checkedInput = input;
                    }
                });
                
                // If a rating is selected, highlight stars up to the selected rating
                if(checkedInput) {
                    const rating = parseInt(checkedInput.value);
                    for(let i = 0; i < rating; i++) {
                        stars[stars.length - 1 - i].style.color = 'var(--color-warning)';
                    }
                }
            });
        });
        
        ratingInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Reset star colors
                stars.forEach(s => s.style.color = '');
                
                // Highlight stars up to the selected rating
                const rating = parseInt(this.value);
                for(let i = 0; i < rating; i++) {
                    stars[stars.length - 1 - i].style.color = 'var(--color-warning)';
                }
            });
        });
    }
}

// Call the function when the DOM is loaded
document.addEventListener('DOMContentLoaded', initRatingStars);