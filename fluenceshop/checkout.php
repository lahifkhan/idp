<?php
    include_once 'includes/header.php';
    include_once 'includes/db_connect.php';
    
    // Redirect if cart is empty
    if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        header('Location: cart.php');
        exit();
    }
    
    // Check if user is logged in or guest checkout
    $isLoggedIn = isset($_SESSION['user_id']);
    $isGuest = isset($_GET['guest']) && $_GET['guest'] == 1;
    
    if(!$isLoggedIn && !$isGuest) {
        // Store current URL for redirect after login
        $_SESSION['redirect_url'] = 'checkout.php';
        header('Location: login.php');
        exit();
    }
    
    // Get cart items
    $cart = $_SESSION['cart'];
    $cartItems = array();
    $subtotal = 0;
    
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
                    'item_total' => $itemTotal
                );
                
                $subtotal += $itemTotal;
            }
        }
    }
    
    // Calculate shipping and tax
    $shipping = 10.00;
    $tax = $subtotal * 0.08; // 8% tax
    $total = $subtotal + $shipping + $tax;
    
    // Handle applied discount
    $discountAmount = 0;
    $appliedCode = isset($_SESSION['applied_discount']) ? $_SESSION['applied_discount'] : null;
    
    if($appliedCode) {
        $discountQuery = "SELECT * FROM discount_codes WHERE code = '$appliedCode' AND active = 1";
        $discountResult = $conn->query($discountQuery);
        
        if($discountResult->num_rows > 0) {
            $discount = $discountResult->fetch_assoc();
            
            if($discount['type'] == 'percentage') {
                $discountAmount = $subtotal * ($discount['value'] / 100);
            } else {
                $discountAmount = $discount['value'];
            }
            
            $total -= $discountAmount;
        }
    }
    
    // Get user information if logged in
    $userInfo = array();
    
    if($isLoggedIn) {
        $userId = $_SESSION['user_id'];
        $userQuery = "SELECT u.*, up.* FROM users u
                     LEFT JOIN user_profiles up ON u.id = up.user_id
                     WHERE u.id = $userId";
        $userResult = $conn->query($userQuery);
        
        if($userResult->num_rows > 0) {
            $userInfo = $userResult->fetch_assoc();
        }
    }
    
    // Handle order submission
    $orderSuccess = false;
    $orderId = null;
    
    if(isset($_POST['place_order'])) {
        // Get form data
        $firstName = $conn->real_escape_string($_POST['first_name']);
        $lastName = $conn->real_escape_string($_POST['last_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);
        $city = $conn->real_escape_string($_POST['city']);
        $state = $conn->real_escape_string($_POST['state']);
        $zipCode = $conn->real_escape_string($_POST['zip_code']);
        $country = $conn->real_escape_string($_POST['country']);
        $paymentMethod = $conn->real_escape_string($_POST['payment_method']);
        
        // Create order
        $orderQuery = "INSERT INTO orders (
                      user_id, order_status, subtotal, shipping, tax, discount, total, 
                      first_name, last_name, email, phone, address, city, state, zip_code, country,
                      payment_method, discount_code, created_at
                    ) VALUES (
                      " . ($isLoggedIn ? $userId : "NULL") . ", 'pending', $subtotal, $shipping, $tax, $discountAmount, $total,
                      '$firstName', '$lastName', '$email', '$phone', '$address', '$city', '$state', '$zipCode', '$country',
                      '$paymentMethod', " . ($appliedCode ? "'$appliedCode'" : "NULL") . ", NOW()
                    )";
        
        if($conn->query($orderQuery)) {
            $orderId = $conn->insert_id;
            
            // Add order items
            foreach($cartItems as $item) {
                $productId = $item['id'];
                $price = $item['price'];
                $quantity = $item['quantity'];
                $itemTotal = $item['item_total'];
                
                $orderItemQuery = "INSERT INTO order_items (
                                  order_id, product_id, price, quantity, total
                                ) VALUES (
                                  $orderId, $productId, $price, $quantity, $itemTotal
                                )";
                
                $conn->query($orderItemQuery);
                
                // Update product stock
                $updateStockQuery = "UPDATE products SET stock = stock - $quantity WHERE id = $productId";
                $conn->query($updateStockQuery);
            }
            
            // If logged in, update user profile with shipping details
            if($isLoggedIn) {
                $updateProfileQuery = "UPDATE user_profiles SET
                                     address = '$address',
                                     city = '$city',
                                     state = '$state',
                                     zip_code = '$zipCode',
                                     country = '$country',
                                     phone = '$phone'
                                     WHERE user_id = $userId";
                
                $conn->query($updateProfileQuery);
            }
            
            // Clear cart and discount
            $_SESSION['cart'] = array();
            unset($_SESSION['applied_discount']);
            
            $orderSuccess = true;
        }
    }
?>

<main>
    <?php if($orderSuccess) { ?>
    <section class="order-success">
        <div class="container">
            <div class="success-container">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Order Placed Successfully!</h1>
                <p>Thank you for your order. Your order number is <strong>#<?php echo $orderId; ?></strong>.</p>
                <p>We've sent a confirmation email to your email address.</p>
                
                <div class="order-next-steps">
                    <p>What's next?</p>
                    <ul>
                        <li>You will receive an email confirmation with your order details.</li>
                        <li>We will process your order and ship it as soon as possible.</li>
                        <li>You can track your order status in your account.</li>
                    </ul>
                </div>
                
                <div class="success-actions">
                    <?php if($isLoggedIn) { ?>
                    <a href="profile.php?tab=orders" class="btn-primary">View Your Orders</a>
                    <?php } ?>
                    <a href="index.php" class="btn-secondary">Return to Home</a>
                </div>
            </div>
        </div>
    </section>
    <?php } else { ?>
    <section class="checkout-header">
        <div class="container">
            <h1>Checkout</h1>
        </div>
    </section>

    <section class="checkout-section">
        <div class="container">
            <form action="checkout.php<?php echo $isGuest ? '?guest=1' : ''; ?>" method="POST" id="checkout-form">
                <div class="checkout-container">
                    <div class="checkout-details">
                        <div class="checkout-block">
                            <h2>Contact Information</h2>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name*</label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo isset($userInfo['first_name']) ? $userInfo['first_name'] : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="last_name">Last Name*</label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo isset($userInfo['last_name']) ? $userInfo['last_name'] : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address*</label>
                                    <input type="email" id="email" name="email" value="<?php echo isset($userInfo['email']) ? $userInfo['email'] : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number*</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo isset($userInfo['phone']) ? $userInfo['phone'] : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checkout-block">
                            <h2>Shipping Address</h2>
                            
                            <div class="form-group">
                                <label for="address">Street Address*</label>
                                <input type="text" id="address" name="address" value="<?php echo isset($userInfo['address']) ? $userInfo['address'] : ''; ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">City*</label>
                                    <input type="text" id="city" name="city" value="<?php echo isset($userInfo['city']) ? $userInfo['city'] : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="state">State/Province*</label>
                                    <input type="text" id="state" name="state" value="<?php echo isset($userInfo['state']) ? $userInfo['state'] : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="zip_code">ZIP/Postal Code*</label>
                                    <input type="text" id="zip_code" name="zip_code" value="<?php echo isset($userInfo['zip_code']) ? $userInfo['zip_code'] : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="country">Country*</label>
                                    <select id="country" name="country" required>
                                        <option value="">Select Country</option>
                                        <option value="US" <?php echo (isset($userInfo['country']) && $userInfo['country'] == 'US') ? 'selected' : ''; ?>>United States</option>
                                        <option value="CA" <?php echo (isset($userInfo['country']) && $userInfo['country'] == 'CA') ? 'selected' : ''; ?>>Canada</option>
                                        <option value="UK" <?php echo (isset($userInfo['country']) && $userInfo['country'] == 'UK') ? 'selected' : ''; ?>>United Kingdom</option>
                                        <option value="AU" <?php echo (isset($userInfo['country']) && $userInfo['country'] == 'AU') ? 'selected' : ''; ?>>Australia</option>
                                        <!-- Add more countries as needed -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checkout-block">
                            <h2>Payment Method</h2>
                            
                            <div class="payment-methods">
                                <div class="payment-method">
                                    <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                                    <label for="credit_card">
                                        <div class="payment-icon"><i class="far fa-credit-card"></i></div>
                                        <div class="payment-label">Credit Card</div>
                                    </label>
                                </div>
                                
                                <div class="payment-method">
                                    <input type="radio" id="paypal" name="payment_method" value="paypal">
                                    <label for="paypal">
                                        <div class="payment-icon"><i class="fab fa-paypal"></i></div>
                                        <div class="payment-label">PayPal</div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="payment-details" id="credit-card-details">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="card_number">Card Number*</label>
                                        <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="card_name">Name on Card*</label>
                                        <input type="text" id="card_name" name="card_name">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="expiry_date">Expiry Date*</label>
                                        <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="cvv">CVV*</label>
                                        <input type="text" id="cvv" name="cvv" placeholder="123">
                                    </div>
                                </div>
                                
                                <div class="payment-security">
                                    <i class="fas fa-lock"></i> Your payment information is secure
                                </div>
                            </div>
                            
                            <div class="payment-details" id="paypal-details" style="display: none;">
                                <p>You will be redirected to PayPal to complete your payment.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="checkout-summary">
                        <div class="checkout-block">
                            <h2>Order Summary</h2>
                            
                            <div class="order-items">
                                <?php foreach($cartItems as $item) { ?>
                                <div class="order-item">
                                    <div class="order-item-image">
                                        <img src="<?php echo $item['image_url']; ?>" alt="<?php echo $item['name']; ?>">
                                        <span class="item-quantity"><?php echo $item['quantity']; ?></span>
                                    </div>
                                    <div class="order-item-details">
                                        <h3><?php echo $item['name']; ?></h3>
                                        <div class="item-price">$<?php echo number_format($item['price'], 2); ?></div>
                                    </div>
                                    <div class="order-item-total">
                                        $<?php echo number_format($item['item_total'], 2); ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            
                            <div class="order-totals">
                                <div class="order-subtotal">
                                    <span>Subtotal</span>
                                    <span>$<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                
                                <?php if($discountAmount > 0) { ?>
                                <div class="order-discount">
                                    <span>Discount</span>
                                    <span>-$<?php echo number_format($discountAmount, 2); ?></span>
                                </div>
                                <?php } ?>
                                
                                <div class="order-shipping">
                                    <span>Shipping</span>
                                    <span>$<?php echo number_format($shipping, 2); ?></span>
                                </div>
                                
                                <div class="order-tax">
                                    <span>Tax (8%)</span>
                                    <span>$<?php echo number_format($tax, 2); ?></span>
                                </div>
                                
                                <div class="order-total">
                                    <span>Total</span>
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="checkout-block">
                            <div class="terms-agreement">
                                <input type="checkbox" id="terms_agree" name="terms_agree" required>
                                <label for="terms_agree">I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></label>
                            </div>
                            
                            <button type="submit" name="place_order" class="btn-primary btn-block">Place Order</button>
                            
                            <div class="checkout-security">
                                <div class="security-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <p>Your personal data will be used to process your order, support your experience, and for other purposes described in our privacy policy.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <?php } ?>
</main>

<?php include_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const creditCardDetails = document.getElementById('credit-card-details');
        const paypalDetails = document.getElementById('paypal-details');
        
        // Show/hide payment details based on selected method
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                if(this.value === 'credit_card') {
                    creditCardDetails.style.display = 'block';
                    paypalDetails.style.display = 'none';
                } else if(this.value === 'paypal') {
                    creditCardDetails.style.display = 'none';
                    paypalDetails.style.display = 'block';
                }
            });
        });
        
        // Form validation
        const checkoutForm = document.getElementById('checkout-form');
        
        if(checkoutForm) {
            checkoutForm.addEventListener('submit', function(e) {
                // This is a simulation for demo purposes
                // In a real application, you would validate all fields and process payment
                
                const firstName = document.getElementById('first_name').value;
                const lastName = document.getElementById('last_name').value;
                const email = document.getElementById('email').value;
                const phone = document.getElementById('phone').value;
                const address = document.getElementById('address').value;
                const city = document.getElementById('city').value;
                const state = document.getElementById('state').value;
                const zipCode = document.getElementById('zip_code').value;
                const country = document.getElementById('country').value;
                const termsAgree = document.getElementById('terms_agree').checked;
                
                let hasErrors = false;
                
                // Basic validation
                if(!firstName || !lastName || !email || !phone || !address || !city || !state || !zipCode || !country) {
                    alert('Please fill in all required fields.');
                    hasErrors = true;
                } else if(!termsAgree) {
                    alert('You must agree to the Terms of Service and Privacy Policy.');
                    hasErrors = true;
                }
                
                // Validate credit card if selected
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
                
                if(paymentMethod === 'credit_card') {
                    const cardNumber = document.getElementById('card_number').value;
                    const cardName = document.getElementById('card_name').value;
                    const expiryDate = document.getElementById('expiry_date').value;
                    const cvv = document.getElementById('cvv').value;
                    
                    if(!cardNumber || !cardName || !expiryDate || !cvv) {
                        alert('Please fill in all credit card details.');
                        hasErrors = true;
                    }
                }
                
                if(hasErrors) {
                    e.preventDefault();
                }
            });
        }
    });
</script>