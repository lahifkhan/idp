<?php
    include_once 'includes/header.php';
    
    // Redirect if already logged in
    if(isset($_SESSION['user_id'])) {
        header('Location: profile.php');
        exit();
    }
    
    $error = '';
    $success = '';
    
    // Handle registration form submission
    if(isset($_POST['register'])) {
        require_once 'includes/db_connect.php';
        
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate input
        if(empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = "All fields are required.";
        } else if(strlen($username) < 3 || strlen($username) > 20) {
            $error = "Username must be between 3 and 20 characters.";
        } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else if(strlen($password) < 8) {
            $error = "Password must be at least 8 characters.";
        } else if($password !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            // Check if username or email already exists
            $checkQuery = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
            $checkResult = $conn->query($checkQuery);
            
            if($checkResult->num_rows > 0) {
                $existingUser = $checkResult->fetch_assoc();
                if($existingUser['username'] == $username) {
                    $error = "Username already taken. Please choose another one.";
                } else {
                    $error = "Email already registered. Please use a different email address.";
                }
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $insertQuery = "INSERT INTO users (username, email, password, created_at) 
                               VALUES ('$username', '$email', '$hashedPassword', NOW())";
                
                if($conn->query($insertQuery)) {
                    $userId = $conn->insert_id;
                    
                    // Set session variables
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $username;
                    
                    // Create user profile
                    $createProfile = "INSERT INTO user_profiles (user_id) VALUES ($userId)";
                    $conn->query($createProfile);
                    
                    $success = "Registration successful! You are now logged in.";
                    
                    // Redirect to profile page after 2 seconds
                    header("refresh:2;url=profile.php");
                } else {
                    $error = "Registration failed. Please try again later.";
                }
            }
        }
        
        $conn->close();
    }
?>

<main>
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-image">
                    <img src="https://images.pexels.com/photos/3771097/pexels-photo-3771097.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" alt="Register">
                    <div class="auth-image-overlay">
                        <h2>Join Our Community</h2>
                        <p>Create an account to access exclusive products, discounts, and resources tailored for influencers.</p>
                    </div>
                </div>
                
                <div class="auth-form-container">
                    <div class="auth-form-wrapper">
                        <h1>Create an Account</h1>
                        
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
                        
                        <form action="register.php" method="POST" class="auth-form">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" placeholder="Choose a username" required>
                                <small>Username must be between 3 and 20 characters.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" placeholder="youremail@example.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" placeholder="Create a password" required>
                                <small>Password must be at least 8 characters long.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                            </div>
                            
                            <div class="form-terms">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label for="terms">I agree to the <a href="terms.php">Terms of Service</a> and <a href="privacy.php">Privacy Policy</a></label>
                            </div>
                            
                            <button type="submit" name="register" class="btn-primary btn-block">Create Account</button>
                        </form>
                        
                        <div class="auth-divider">
                            <span>or</span>
                        </div>
                        
                        <div class="auth-links">
                            <p>Already have an account? <a href="login.php">Log In</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'includes/footer.php'; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const form = document.querySelector('.auth-form');
        
        // Check password match
        function checkPasswordMatch() {
            if(password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        if(password && confirmPassword) {
            password.addEventListener('change', checkPasswordMatch);
            confirmPassword.addEventListener('keyup', checkPasswordMatch);
        }
        
        // Form validation
        if(form) {
            form.addEventListener('submit', function(e) {
                if(!document.getElementById('terms').checked) {
                    e.preventDefault();
                    alert('You must agree to the Terms of Service and Privacy Policy.');
                }
            });
        }
    });
</script>