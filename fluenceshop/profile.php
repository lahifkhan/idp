<?php
    include_once 'includes/header.php';
    include_once 'includes/db_connect.php';
    
    // Redirect if not logged in
    if(!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    
    $userId = $_SESSION['user_id'];
    
    // Get user info
    $userQuery = "SELECT u.*, up.* FROM users u
                 LEFT JOIN user_profiles up ON u.id = up.user_id
                 WHERE u.id = $userId";
    $userResult = $conn->query($userQuery);
    $user = $userResult->fetch_assoc();
    
    // Get user orders
    $ordersQuery = "SELECT * FROM orders WHERE user_id = $userId ORDER BY created_at DESC";
    $ordersResult = $conn->query($ordersQuery);
    
    // Get active tab
    $activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
    
    // Handle profile update
    $updateSuccess = false;
    $updateError = false;
    
    if(isset($_POST['update_profile'])) {
        $firstName = $conn->real_escape_string($_POST['first_name']);
        $lastName = $conn->real_escape_string($_POST['last_name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);
        $city = $conn->real_escape_string($_POST['city']);
        $state = $conn->real_escape_string($_POST['state']);
        $zipCode = $conn->real_escape_string($_POST['zip_code']);
        $country = $conn->real_escape_string($_POST['country']);
        
        // Update user profile
        $updateQuery = "UPDATE user_profiles SET
                       first_name = '$firstName',
                       last_name = '$lastName',
                       phone = '$phone',
                       address = '$address',
                       city = '$city',
                       state = '$state',
                       zip_code = '$zipCode',
                       country = '$country'
                       WHERE user_id = $userId";
        
        if($conn->query($updateQuery)) {
            $updateSuccess = true;
            
            // Refresh user data
            $userResult = $conn->query($userQuery);
            $user = $userResult->fetch_assoc();
        } else {
            $updateError = true;
        }
    }
    
    // Handle password change
    $passwordSuccess = false;
    $passwordError = false;
    
    if(isset($_POST['change_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Verify current password
        if(password_verify($currentPassword, $user['password'])) {
            if($newPassword === $confirmPassword) {
                // Hash the new password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Update password
                $updatePassQuery = "UPDATE users SET password = '$hashedPassword' WHERE id = $userId";
                
                if($conn->query($updatePassQuery)) {
                    $passwordSuccess = true;
                } else {
                    $passwordError = "Failed to update password. Please try again.";
                }
            } else {
                $passwordError = "New passwords do not match.";
            }
        } else {
            $passwordError = "Current password is incorrect.";
        }
    }
?>

<main>
    <section class="profile-header">
        <div class="container">
            <h1>Your Account</h1>
        </div>
    </section>

    <section class="profile-section">
        <div class="container">
            <div class="profile-tabs">
                <a href="profile.php?tab=dashboard" class="profile-tab <?php echo $activeTab == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="profile.php?tab=orders" class="profile-tab <?php echo $activeTab == 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag"></i> Orders
                </a>
                <a href="profile.php?tab=addresses" class="profile-tab <?php echo $activeTab == 'addresses' ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt"></i> Addresses
                </a>
                <a href="profile.php?tab=account" class="profile-tab <?php echo $activeTab == 'account' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i> Account Details
                </a>
                <a href="includes/logout.php" class="profile-tab">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <div class="profile-content">
                <?php if($activeTab == 'dashboard') { ?>
                <div class="dashboard-section">
                    <div class="welcome-box">
                        <h2>Welcome, <?php echo $user['username']; ?>!</h2>
                        <p>From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.</p>
                    </div>
                    
                    <div class="dashboard-stats">
                        <div class="stat-box">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo $ordersResult->num_rows; ?></div>
                                <div class="stat-label">Orders</div>
                            </div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">
                                    <?php
                                    // Get number of reviews
                                    $reviewsQuery = "SELECT COUNT(*) as count FROM reviews WHERE user_id = $userId";
                                    $reviewsResult = $conn->query($reviewsQuery);
                                    echo $reviewsResult->fetch_assoc()['count'];
                                    ?>
                                </div>
                                <div class="stat-label">Reviews</div>
                            </div>
                        </div>
                        
                        <div class="stat-box">
                            <div class="stat-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value">
                                    <?php
                                    // Get number of favorites (wishlist items)
                                    $wishlistQuery = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = $userId";
                                    $wishlistResult = $conn->query($wishlistQuery);
                                    echo $wishlistResult->fetch_assoc()['count'];
                                    ?>
                                </div>
                                <div class="stat-label">Wishlist</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="recent-orders">
                        <h3>Recent Orders</h3>
                        
                        <?php
                        if($ordersResult->num_rows > 0) {
                            $recentOrders = array();
                            $count = 0;
                            
                            while($order = $ordersResult->fetch_assoc()) {
                                if($count < 3) {
                                    $recentOrders[] = $order;
                                    $count++;
                                } else {
                                    break;
                                }
                            }
                        ?>
                        <div class="orders-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentOrders as $order) { ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <span class="order-status <?php echo strtolower($order['order_status']); ?>">
                                                <?php echo ucfirst($order['order_status']); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                                        <td>
                                            <a href="order.php?id=<?php echo $order['id']; ?>" class="btn-view-order">View</a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if($ordersResult->num_rows > 3) { ?>
                        <div class="view-all-link">
                            <a href="profile.php?tab=orders">View All Orders</a>
                        </div>
                        <?php } ?>
                        
                        <?php } else { ?>
                        <div class="no-orders">
                            <p>You haven't placed any orders yet.</p>
                            <a href="products.php" class="btn-secondary">Start Shopping</a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                
                <?php } else if($activeTab == 'orders') { ?>
                <div class="orders-section">
                    <h2>Your Orders</h2>
                    
                    <?php
                    if($ordersResult->num_rows > 0) {
                        // Reset the result pointer
                        $ordersResult->data_seek(0);
                    ?>
                    <div class="orders-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = $ordersResult->fetch_assoc()) { ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <span class="order-status <?php echo strtolower($order['order_status']); ?>">
                                            <?php echo ucfirst($order['order_status']); ?>
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($order['total'], 2); ?></td>
                                    <td>
                                        <a href="order.php?id=<?php echo $order['id']; ?>" class="btn-view-order">View</a>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } else { ?>
                    <div class="no-orders">
                        <p>You haven't placed any orders yet.</p>
                        <a href="products.php" class="btn-secondary">Start Shopping</a>
                    </div>
                    <?php } ?>
                </div>
                
                <?php } else if($activeTab == 'addresses') { ?>
                <div class="addresses-section">
                    <h2>Your Addresses</h2>
                    
                    <?php if($updateSuccess) { ?>
                    <div class="alert alert-success">
                        Your address has been updated successfully.
                    </div>
                    <?php } ?>
                    
                    <?php if($updateError) { ?>
                    <div class="alert alert-danger">
                        There was an error updating your address. Please try again.
                    </div>
                    <?php } ?>
                    
                    <div class="address-cards">
                        <div class="address-card">
                            <div class="address-header">
                                <h3>Shipping Address</h3>
                                <button class="btn-edit-address" data-target="shipping-address-form">Edit</button>
                            </div>
                            
                            <div class="address-content">
                                <?php if(!empty($user['address'])) { ?>
                                <p>
                                    <?php echo $user['first_name'] . ' ' . $user['last_name']; ?><br>
                                    <?php echo $user['address']; ?><br>
                                    <?php echo $user['city'] . ', ' . $user['state'] . ' ' . $user['zip_code']; ?><br>
                                    <?php echo $user['country']; ?><br>
                                    <?php echo $user['phone']; ?>
                                </p>
                                <?php } else { ?>
                                <p>No shipping address saved yet.</p>
                                <button class="btn-add-address" data-target="shipping-address-form">Add Address</button>
                                <?php } ?>
                            </div>
                            
                            <form action="profile.php?tab=addresses" method="POST" id="shipping-address-form" class="address-form" style="display: none;">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="first_name">First Name*</label>
                                        <input type="text" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="last_name">Last Name*</label>
                                        <input type="text" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Street Address*</label>
                                    <input type="text" id="address" name="address" value="<?php echo $user['address']; ?>" required>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city">City*</label>
                                        <input type="text" id="city" name="city" value="<?php echo $user['city']; ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="state">State/Province*</label>
                                        <input type="text" id="state" name="state" value="<?php echo $user['state']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="zip_code">ZIP/Postal Code*</label>
                                        <input type="text" id="zip_code" name="zip_code" value="<?php echo $user['zip_code']; ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="country">Country*</label>
                                        <select id="country" name="country" required>
                                            <option value="">Select Country</option>
                                            <option value="US" <?php echo $user['country'] == 'US' ? 'selected' : ''; ?>>United States</option>
                                            <option value="CA" <?php echo $user['country'] == 'CA' ? 'selected' : ''; ?>>Canada</option>
                                            <option value="UK" <?php echo $user['country'] == 'UK' ? 'selected' : ''; ?>>United Kingdom</option>
                                            <option value="AU" <?php echo $user['country'] == 'AU' ? 'selected' : ''; ?>>Australia</option>
                                            <!-- Add more countries as needed -->
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Phone Number*</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo $user['phone']; ?>" required>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="update_profile" class="btn-primary">Save Address</button>
                                    <button type="button" class="btn-cancel-edit">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <?php } else if($activeTab == 'account') { ?>
                <div class="account-section">
                    <h2>Account Details</h2>
                    
                    <div class="account-info">
                        <div class="account-overview">
                            <div class="account-field">
                                <span class="field-label">Username:</span>
                                <span class="field-value"><?php echo $user['username']; ?></span>
                            </div>
                            
                            <div class="account-field">
                                <span class="field-label">Email:</span>
                                <span class="field-value"><?php echo $user['email']; ?></span>
                            </div>
                            
                            <div class="account-field">
                                <span class="field-label">Member Since:</span>
                                <span class="field-value"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="password-change">
                            <h3>Change Password</h3>
                            
                            <?php if($passwordSuccess) { ?>
                            <div class="alert alert-success">
                                Your password has been changed successfully.
                            </div>
                            <?php } ?>
                            
                            <?php if($passwordError) { ?>
                            <div class="alert alert-danger">
                                <?php echo $passwordError; ?>
                            </div>
                            <?php } ?>
                            
                            <form action="profile.php?tab=account" method="POST" class="password-form">
                                <div class="form-group">
                                    <label for="current_password">Current Password*</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password*</label>
                                    <input type="password" id="new_password" name="new_password" required>
                                    <small>Password must be at least 8 characters long.</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password*</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn-primary">Change Password</button>
                            </form>
                        </div>
                        
                        <div class="account-delete">
                            <h3>Delete Account</h3>
                            <p>Once you delete your account, there is no going back. Please be certain.</p>
                            <button class="btn-delete-account" id="delete-account-btn">Delete Account</button>
                            
                            <div class="delete-confirmation" id="delete-confirmation" style="display: none;">
                                <p>Are you sure you want to delete your account? All of your data will be permanently removed.</p>
                                <div class="confirmation-actions">
                                    <form action="includes/delete_account.php" method="POST">
                                        <button type="submit" name="confirm_delete" class="btn-confirm-delete">Yes, Delete My Account</button>
                                    </form>
                                    <button class="btn-cancel-delete" id="cancel-delete">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>
</main>

<?php include_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit address
        const editButtons = document.querySelectorAll('.btn-edit-address, .btn-add-address');
        const cancelButtons = document.querySelectorAll('.btn-cancel-edit');
        
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const formId = this.getAttribute('data-target');
                document.getElementById(formId).style.display = 'block';
                this.closest('.address-card').querySelector('.address-content').style.display = 'none';
                this.style.display = 'none';
            });
        });
        
        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('.address-form');
                form.style.display = 'none';
                
                const addressCard = form.closest('.address-card');
                addressCard.querySelector('.address-content').style.display = 'block';
                addressCard.querySelector('.btn-edit-address, .btn-add-address').style.display = 'inline-block';
            });
        });
        
        // Delete account
        const deleteAccountBtn = document.getElementById('delete-account-btn');
        const deleteConfirmation = document.getElementById('delete-confirmation');
        const cancelDeleteBtn = document.getElementById('cancel-delete');
        
        if(deleteAccountBtn && deleteConfirmation && cancelDeleteBtn) {
            deleteAccountBtn.addEventListener('click', function() {
                deleteConfirmation.style.display = 'block';
                this.style.display = 'none';
            });
            
            cancelDeleteBtn.addEventListener('click', function() {
                deleteConfirmation.style.display = 'none';
                deleteAccountBtn.style.display = 'inline-block';
            });
        }
        
        // Password validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordForm = document.querySelector('.password-form');
        
        if(passwordForm && newPassword && confirmPassword) {
            passwordForm.addEventListener('submit', function(e) {
                if(newPassword.value.length < 8) {
                    alert('Password must be at least 8 characters long.');
                    e.preventDefault();
                    return;
                }
                
                if(newPassword.value !== confirmPassword.value) {
                    alert('New passwords do not match.');
                    e.preventDefault();
                }
            });
        }
    });
</script>