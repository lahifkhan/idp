<?php
    include_once 'includes/header.php';
    include_once 'includes/db_connect.php';
    
    // Check if user is logged in
    $isLoggedIn = isset($_SESSION['user_id']);
    
    // Initialize or get cart from session
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    $cart = $_SESSION['cart'];
    $cartItems = array();
    $subtotal = 0;
    $totalItems = 0;
    
    // Process cart items
    if(!empty($cart)) {
        $productIds = array_keys($cart);
        $productIdString = implode(',', $productIds);
        
        // Fetch product details
        $cartQuery = "SELECT * FROM products WHERE id IN ($productIdString)";
        $cartResult = $conn->query($cartQuery);
        
        if($cartResult->num_rows > 0) {
            while($product = $cartResult->fetch_assoc()) {
                $productId = $product['id'];
                $quantity = $cart[$productId];
                $itemTotal = $product['price'] * $quantity;
                
                $cartItems[] = array(
                    'id' => $productId,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image_url' => $product['image_url'],
                    'quantity' => $quantity,
                    'stock' => $product['stock'],
                    'item_total' => $itemTotal
                );
                
                $subtotal += $itemTotal;
                $totalItems += $quantity;
            }
        }
    }
    
    // Calculate shipping and tax
    $shipping = $subtotal > 0 ? 10.00 : 0;
    $tax = $subtotal * 0.08; // 8% tax
    $total = $subtotal + $shipping + $tax;
    
    // Get discount codes
    $discountCodes = array();
    $discountQuery = "SELECT * FROM discount_codes WHERE active = 1";
    $discountResult = $conn->query($discountQuery);
    
    if($discountResult->num_rows > 0) {
        while($code = $discountResult->fetch_assoc()) {
            $discountCodes[] = $code;
        }
    }
    
    // Handle applied discount
    $discountAmount = 0;
    $appliedCode = isset($_SESSION['applied_discount']) ? $_SESSION['applied_discount'] : null;
    
    if($appliedCode) {
        foreach($discountCodes as $code) {
            if($code['code'] == $appliedCode) {
                if($code['type'] == 'percentage') {
                    $discountAmount = $subtotal * ($code['value'] / 100);
                } else {
                    $discountAmount = $code['value'];
                }
                $total -= $discountAmount;
                break;
            }
        }
    }
?>

<main>
    <section class="cart-header">
        <div class="container">
            <h1>Your Shopping Cart</h1>
        </div>
    </section>

    <section class="cart-section">
        <div class="container">
            <?php if(empty($cartItems)) { ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any products to your cart yet.</p>
                <a href="products.php" class="btn-primary">Continue Shopping</a>
            </div>
            <?php } else { ?>
            <div class="cart-container">
                <div class="cart-items">
                    <div class="cart-headers">
                        <div class="cart-header-product">Product</div>
                        <div class="cart-header-price">Price</div>
                        <div class="cart-header-quantity">Quantity</div>
                        <div class="cart-header-total">Total</div>
                        <div class="cart-header-remove"></div>
                    </div>
                    
                    <?php foreach($cartItems as $item) { ?>
                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                        <div class="cart-product">
                            <div class="cart-product-image">
                                <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="cart-product-details">
                                <h3><a href="product.php?id=<?php echo $item['id']; ?>"><?php echo $item['name']; ?></a></h3>
                            </div>
                        </div>
                        
                        <div class="cart-price">
                            $<?php echo number_format($item['price'], 2); ?>
                        </div>
                        
                        <div class="cart-quantity">
                            <div class="quantity-selector">
                                <button class="quantity-btn minus" data-id="<?php echo $item['id']; ?>">-</button>
                                <input type="number" class="item-quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" data-id="<?php echo $item['id']; ?>">
                                <button class="quantity-btn plus" data-id="<?php echo $item['id']; ?>">+</button>
                            </div>
                        </div>
                        
                        <div class="cart-item-total">
                            $<span class="item-total-price"><?php echo number_format($item['item_total'], 2); ?></span>
                        </div>
                        
                        <div class="cart-remove">
                            <button class="remove-item" data-id="<?php echo $item['id']; ?>">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <div class="cart-actions">
                        <a href="products.php" class="btn-secondary">Continue Shopping</a>
                        <button id="clear-cart" class="btn-outline">Clear Cart</button>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <span>$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <?php if($appliedCode) { 
                        foreach($discountCodes as $code) {
                            if($code['code'] == $appliedCode) {
                    ?>
                    <div class="summary-item discount">
                        <span>Discount (<?php echo $code['code']; ?>)</span>
                        <span>-$<?php echo number_format($discountAmount, 2); ?></span>
                    </div>
                    <?php 
                                break;
                            }
                        }
                    ?>
                    <div class="remove-discount">
                        <button id="remove-discount">Remove Discount</button>
                    </div>
                    <?php } else { ?>
                    <div class="discount-code">
                        <form id="discount-form">
                            <input type="text" id="discount-input" placeholder="Enter discount code">
                            <button type="submit" id="apply-discount">Apply</button>
                        </form>
                        <div id="discount-message"></div>
                    </div>
                    <?php } ?>
                    
                    <div class="summary-item">
                        <span>Shipping</span>
                        <span>$<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Tax (8%)</span>
                        <span>$<?php echo number_format($tax, 2); ?></span>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-total">
                        <span>Total</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <div class="checkout-button">
                        <?php if($isLoggedIn) { ?>
                        <a href="checkout.php" class="btn-primary btn-block">Proceed to Checkout</a>
                        <?php } else { ?>
                        <a href="login.php" class="btn-primary btn-block">Log In to Checkout</a>
                        <div class="guest-checkout">
                            <a href="checkout.php?guest=1">Continue as Guest</a>
                        </div>
                        <?php } ?>
                    </div>
                    
                    <div class="secure-checkout">
                        <div class="secure-checkout-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="secure-checkout-text">
                            <p>Secure Checkout</p>
                            <span>Your payment information is processed securely.</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>
</main>

<?php include_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update quantity
        const quantityInputs = document.querySelectorAll('.item-quantity');
        const minusButtons = document.querySelectorAll('.quantity-btn.minus');
        const plusButtons = document.querySelectorAll('.quantity-btn.plus');
        
        function updateCartItem(productId, quantity) {
            fetch('includes/cart_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Update item total price
                    const itemTotalElement = document.querySelector(`.cart-item[data-id="${productId}"] .item-total-price`);
                    if(itemTotalElement) {
                        itemTotalElement.textContent = data.item_total.toFixed(2);
                    }
                    
                    // Update cart summary
                    updateCartSummary(data.subtotal, data.tax, data.shipping, data.discount, data.total);
                    
                    // Update cart count in header
                    updateCartCount(data.cart_count);
                } else {
                    console.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.getAttribute('data-id');
                const quantity = parseInt(this.value);
                const max = parseInt(this.getAttribute('max'));
                
                if(quantity < 1) {
                    this.value = 1;
                } else if(quantity > max) {
                    this.value = max;
                }
                
                updateCartItem(productId, this.value);
            });
        });
        
        minusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const input = document.querySelector(`.item-quantity[data-id="${productId}"]`);
                let value = parseInt(input.value);
                
                if(value > 1) {
                    input.value = value - 1;
                    updateCartItem(productId, input.value);
                }
            });
        });
        
        plusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const input = document.querySelector(`.item-quantity[data-id="${productId}"]`);
                let value = parseInt(input.value);
                let max = parseInt(input.getAttribute('max'));
                
                if(value < max) {
                    input.value = value + 1;
                    updateCartItem(productId, input.value);
                }
            });
        });
        
        // Remove item
        const removeButtons = document.querySelectorAll('.remove-item');
        
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                
                fetch('includes/cart_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        // Remove item from DOM
                        const item = document.querySelector(`.cart-item[data-id="${productId}"]`);
                        item.classList.add('removing');
                        
                        setTimeout(() => {
                            item.remove();
                            
                            // Check if cart is empty
                            const cartItems = document.querySelectorAll('.cart-item');
                            if(cartItems.length === 0) {
                                location.reload();
                            } else {
                                // Update cart summary
                                updateCartSummary(data.subtotal, data.tax, data.shipping, data.discount, data.total);
                                
                                // Update cart count in header
                                updateCartCount(data.cart_count);
                            }
                        }, 300);
                    } else {
                        console.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
        
        // Clear cart
        const clearCartButton = document.getElementById('clear-cart');
        
        if(clearCartButton) {
            clearCartButton.addEventListener('click', function() {
                if(confirm('Are you sure you want to clear your cart?')) {
                    fetch('includes/cart_actions.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=clear'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            console.error(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            });
        }
        
        // Apply discount
        const discountForm = document.getElementById('discount-form');
        const discountInput = document.getElementById('discount-input');
        const discountMessage = document.getElementById('discount-message');
        
        if(discountForm) {
            discountForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const code = discountInput.value.trim();
                
                if(code === '') {
                    showDiscountMessage('Please enter a discount code.', 'error');
                    return;
                }
                
                fetch('includes/cart_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=apply_discount&code=${code}`
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        showDiscountMessage('Discount applied successfully!', 'success');
                        discountInput.value = '';
                        
                        // Reload the page to show the discount
                        location.reload();
                    } else {
                        showDiscountMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showDiscountMessage('An error occurred. Please try again.', 'error');
                });
            });
        }
        
        // Remove discount
        const removeDiscountButton = document.getElementById('remove-discount');
        
        if(removeDiscountButton) {
            removeDiscountButton.addEventListener('click', function() {
                fetch('includes/cart_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=remove_discount'
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        console.error(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        }
        
        function showDiscountMessage(message, type) {
            discountMessage.textContent = message;
            discountMessage.className = type;
            discountMessage.style.display = 'block';
            
            setTimeout(() => {
                discountMessage.style.display = 'none';
            }, 3000);
        }
        
        function updateCartSummary(subtotal, tax, shipping, discount, total) {
            const summaryItems = document.querySelectorAll('.summary-item');
            const summaryTotal = document.querySelector('.summary-total span:last-child');
            
            // Update subtotal
            summaryItems[0].querySelector('span:last-child').textContent = '$' + subtotal.toFixed(2);
            
            // Update discount if exists
            if(discount > 0) {
                const discountElement = document.querySelector('.summary-item.discount span:last-child');
                if(discountElement) {
                    discountElement.textContent = '-$' + discount.toFixed(2);
                }
            }
            
            // Update tax
            summaryItems[summaryItems.length - 2].querySelector('span:last-child').textContent = '$' + tax.toFixed(2);
            
            // Update shipping
            summaryItems[summaryItems.length - 3].querySelector('span:last-child').textContent = '$' + shipping.toFixed(2);
            
            // Update total
            summaryTotal.textContent = '$' + total.toFixed(2);
        }
        
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
    });
</script>