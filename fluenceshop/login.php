<?php
    include_once 'includes/header.php';
    
    // Redirect if already logged in
    if(isset($_SESSION['user_id'])) {
        header('Location: profile.php');
        exit();
    }
    
    $error = '';
    $success = '';
    
    // Handle login form submission
    if(isset($_POST['login'])) {
        require_once 'includes/db_connect.php';
        
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        
        // Validate email and password
        if(empty($email) || empty($password)) {
            $error = "Please enter both email and password.";
        } else {
            // Check if user exists
            $query = "SELECT id, username, password FROM users WHERE email = '$email'";
            $result = $conn->query($query);
            
            if($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if(password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Update last login time
                    $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = {$user['id']}";
                    $conn->query($updateQuery);
                    
                    // Check if there's a redirect URL
                    if(isset($_SESSION['redirect_url'])) {
                        $redirectUrl = $_SESSION['redirect_url'];
                        unset($_SESSION['redirect_url']);
                        header("Location: $redirectUrl");
                    } else {
                        header('Location: profile.php');
                    }
                    exit();
                } else {
                    $error = "Invalid password. Please try again.";
                }
            } else {
                $error = "No account found with that email address.";
            }
        }
        
        $conn->close();
    }
    
    // Handle password reset request
    if(isset($_POST['reset_password'])) {
        require_once 'includes/db_connect.php';
        
        $email = $conn->real_escape_string($_POST['reset_email']);
        
        // Check if email exists
        $query = "SELECT id, username FROM users WHERE email = '$email'";
        $result = $conn->query($query);
        
        if($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $insertToken = "INSERT INTO password_resets (user_id, token, expires_at) 
                           VALUES ({$user['id']}, '$token', '$expires')";
            
            if($conn->query($insertToken)) {
                // In a real application, you would send an email with the reset link
                // For this example, we'll just show a success message
                $success = "Password reset instructions have been sent to your email address.";
            } else {
                $error = "An error occurred. Please try again later.";
            }
        } else {
            // Don't reveal that the email doesn't exist for security reasons
            $success = "If your email address exists in our database, you will receive a password recovery link at your email address in a few minutes.";
        }
        
        $conn->close();
    }
?>

<main>
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-image">
                    <img src="https://images.pexels.com/photos/1132335/pexels-photo-1132335.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" alt="Login">
                    <div class="auth-image-overlay">
                        <h2>Welcome Back</h2>
                        <p>Log in to access your account, manage your orders, and continue your influencer journey.</p>
                    </div>
                </div>
                
                <div class="auth-form-container">
                    <div class="auth-form-wrapper">
                        <h1>Login to Your Account</h1>
                        
                        <?php if(!empty($error)) { ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                        <?php } ?>
                        
                        <?php if(!empty($success)) { ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                        <?php } ?>
                        
                        <form action="login.php" method="POST" class="auth-form">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="youremail@example.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                            
                            <div class="form-footer">
                                <div class="remember-me">
                                    <input type="checkbox" id="remember" name="remember">
                                    <label for="remember">Remember me</label>
                                </div>
                                <a href="#" class="forgot-password" id="forgot-password-link">Forgot Password?</a>
                            </div>
                            
                            <button type="submit" name="login" class="btn-primary btn-block">Log In</button>
                        </form>
                        
                        <div class="auth-divider">
                            <span>or</span>
                        </div>
                        
                        <div class="auth-links">
                            <p>Don't have an account? <a href="register.php">Register Now</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Password Reset Modal -->
    <div class="modal" id="password-reset-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Reset Your Password</h2>
            <p>Enter your email address and we'll send you instructions to reset your password.</p>
            
            <form action="login.php" method="POST" class="reset-form">
                <div class="form-group">
                    <label for="reset_email">Email Address</label>
                    <input type="email" id="reset_email" name="reset_email" placeholder="youremail@example.com" required>
                </div>
                
                <button type="submit" name="reset_password" class="btn-primary btn-block">Send Reset Link</button>
            </form>
        </div>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password Reset Modal
        const modal = document.getElementById('password-reset-modal');
        const forgotLink = document.getElementById('forgot-password-link');
        const closeBtn = document.querySelector('.close');
        
        if(forgotLink && modal && closeBtn) {
            forgotLink.addEventListener('click', function(e) {
                e.preventDefault();
                modal.style.display = 'block';
            });
            
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', function(e) {
                if(e.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }
    });
</script>