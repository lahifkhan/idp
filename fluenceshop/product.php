<?php
    include_once 'includes/header.php';
    include_once 'includes/db_connect.php';
    
    // Check if product ID is provided
    if(!isset($_GET['id']) || empty($_GET['id'])) {
        header('Location: products.php');
        exit();
    }
    
    $productId = (int)$_GET['id'];
    
    // Get product details
    $productQuery = "SELECT p.*, c.name as category_name FROM products p
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.id = $productId";
    $productResult = $conn->query($productQuery);
    
    if($productResult->num_rows == 0) {
        header('Location: products.php');
        exit();
    }
    
    $product = $productResult->fetch_assoc();
    
    // Get product reviews
    $reviewsQuery = "SELECT r.*, u.username, u.profile_image FROM reviews r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.product_id = $productId
                    ORDER BY r.created_at DESC
                    LIMIT 5";
    $reviewsResult = $conn->query($reviewsQuery);
    
    // Get related products
    $relatedQuery = "SELECT * FROM products 
                    WHERE category_id = {$product['category_id']} 
                    AND id != $productId 
                    LIMIT 4";
    $relatedResult = $conn->query($relatedQuery);
    
    // Handle review submission
    $reviewSubmitted = false;
    $reviewError = false;
    
    if(isset($_POST['submit_review']) && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $rating = (int)$_POST['rating'];
        $comment = $conn->real_escape_string($_POST['comment']);
        
        if($rating >= 1 && $rating <= 5) {
            $insertReview = "INSERT INTO reviews (product_id, user_id, rating, comment, created_at) 
                            VALUES ($productId, $userId, $rating, '$comment', NOW())";
            
            if($conn->query($insertReview)) {
                // Update product average rating
                $updateRating = "UPDATE products SET 
                                avg_rating = (SELECT AVG(rating) FROM reviews WHERE product_id = $productId),
                                rating_count = (SELECT COUNT(*) FROM reviews WHERE product_id = $productId)
                                WHERE id = $productId";
                $conn->query($updateRating);
                $reviewSubmitted = true;
                
                // Refresh product data
                $productResult = $conn->query($productQuery);
                $product = $productResult->fetch_assoc();
                
                // Refresh reviews
                $reviewsResult = $conn->query($reviewsQuery);
            } else {
                $reviewError = true;
            }
        } else {
            $reviewError = true;
        }
    }
?>

<main>
    <section class="product-detail">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a> &gt;
                <a href="products.php">Products</a> &gt;
                <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a> &gt;
                <span><?php echo $product['name']; ?></span>
            </div>
            
            <div class="product-detail-content">
                <div class="product-images">
                    <div class="main-image">
                        <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" id="main-product-image">
                    </div>
                    <?php if(!empty($product['additional_images'])) { 
                        $images = json_decode($product['additional_images'], true);
                    ?>
                    <div class="thumbnail-images">
                        <div class="thumbnail active" data-image="<?php echo $product['image_url']; ?>">
                            <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                        </div>
                        <?php foreach($images as $image) { ?>
                        <div class="thumbnail" data-image="<?php echo $image; ?>">
                            <img src="<?php echo $image; ?>" alt="<?php echo $product['name']; ?>">
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
                
                <div class="product-info">
                    <h1><?php echo $product['name']; ?></h1>
                    
                    <div class="product-meta">
                        <div class="product-rating">
                            <?php
                            for($i = 1; $i <= 5; $i++) {
                                if($i <= $product['avg_rating']) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                            <span class="rating-count">(<?php echo $product['rating_count']; ?> reviews)</span>
                        </div>
                        <div class="product-category">
                            Category: <a href="products.php?category=<?php echo $product['category_id']; ?>"><?php echo $product['category_name']; ?></a>
                        </div>
                    </div>
                    
                    <div class="product-price">
                        $<?php echo number_format($product['price'], 2); ?>
                    </div>
                    
                    <?php if($product['stock'] > 0) { ?>
                    <div class="product-stock in-stock">
                        <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)
                    </div>
                    <?php } else { ?>
                    <div class="product-stock out-of-stock">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </div>
                    <?php } ?>
                    
                    <div class="product-description">
                        <?php echo nl2br($product['description']); ?>
                    </div>
                    
                    <?php if($product['stock'] > 0) { ?>
                    <div class="product-actions">
                        <div class="quantity-selector">
                            <button class="quantity-btn minus" id="qty-minus">-</button>
                            <input type="number" id="product-quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button class="quantity-btn plus" id="qty-plus">+</button>
                        </div>
                        <button class="btn-add-cart" id="add-to-cart" data-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    </div>
                    <?php } ?>
                    
                    <div class="product-meta-info">
                        <div class="meta-item">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>Secure Checkout</strong>
                                <p>Your data is protected</p>
                            </div>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-truck"></i>
                            <div>
                                <strong>Fast Shipping</strong>
                                <p>Delivered in 3-5 business days</p>
                            </div>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-undo"></i>
                            <div>
                                <strong>Easy Returns</strong>
                                <p>30 day return policy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="product-tabs">
        <div class="container">
            <div class="tabs">
                <button class="tab-btn active" data-tab="description">Description</button>
                <button class="tab-btn" data-tab="specifications">Specifications</button>
                <button class="tab-btn" data-tab="reviews">Reviews (<?php echo $product['rating_count']; ?>)</button>
            </div>
            
            <div class="tab-content">
                <div class="tab-pane active" id="description">
                    <h3>Product Description</h3>
                    <div class="content">
                        <?php echo nl2br($product['description']); ?>
                    </div>
                </div>
                
                <div class="tab-pane" id="specifications">
                    <h3>Product Specifications</h3>
                    <div class="content">
                        <?php if(!empty($product['specifications'])) { 
                            $specs = json_decode($product['specifications'], true);
                        ?>
                        <table class="specs-table">
                            <?php foreach($specs as $key => $value) { ?>
                            <tr>
                                <th><?php echo $key; ?></th>
                                <td><?php echo $value; ?></td>
                            </tr>
                            <?php } ?>
                        </table>
                        <?php } else { ?>
                            <p>No specifications available for this product.</p>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="tab-pane" id="reviews">
                    <h3>Customer Reviews</h3>
                    <div class="content">
                        <div class="reviews-summary">
                            <div class="rating-average">
                                <div class="big-rating"><?php echo number_format($product['avg_rating'], 1); ?></div>
                                <div class="stars">
                                    <?php
                                    for($i = 1; $i <= 5; $i++) {
                                        if($i <= $product['avg_rating']) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="total-reviews">Based on <?php echo $product['rating_count']; ?> reviews</div>
                            </div>
                        </div>
                        
                        <?php if($reviewSubmitted) { ?>
                        <div class="alert alert-success">
                            Thank you! Your review has been submitted successfully.
                        </div>
                        <?php } else if($reviewError) { ?>
                        <div class="alert alert-danger">
                            There was an error submitting your review. Please try again.
                        </div>
                        <?php } ?>
                        
                        <?php if(isset($_SESSION['user_id'])) { ?>
                        <div class="write-review">
                            <h4>Write a Review</h4>
                            <form action="product.php?id=<?php echo $productId; ?>#reviews" method="POST" class="review-form">
                                <div class="form-group">
                                    <label>Your Rating</label>
                                    <div class="rating-select">
                                        <div class="rating-options">
                                            <input type="radio" name="rating" id="rating-5" value="5" required>
                                            <label for="rating-5"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="rating" id="rating-4" value="4">
                                            <label for="rating-4"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="rating" id="rating-3" value="3">
                                            <label for="rating-3"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="rating" id="rating-2" value="2">
                                            <label for="rating-2"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="rating" id="rating-1" value="1">
                                            <label for="rating-1"><i class="fas fa-star"></i></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comment">Your Review</label>
                                    <textarea name="comment" id="comment" rows="5" required></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn-submit-review">Submit Review</button>
                            </form>
                        </div>
                        <?php } else { ?>
                        <div class="login-to-review">
                            <p>Please <a href="login.php">log in</a> to write a review.</p>
                        </div>
                        <?php } ?>
                        
                        <div class="reviews-list">
                            <?php
                            if($reviewsResult->num_rows > 0) {
                                while($review = $reviewsResult->fetch_assoc()) {
                            ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <div class="reviewer-avatar">
                                            <?php if(!empty($review['profile_image'])) { ?>
                                                <img src="<?php echo $review['profile_image']; ?>" alt="<?php echo $review['username']; ?>">
                                            <?php } else { ?>
                                                <div class="avatar-placeholder"><?php echo substr($review['username'], 0, 1); ?></div>
                                            <?php } ?>
                                        </div>
                                        <div class="reviewer-name"><?php echo $review['username']; ?></div>
                                    </div>
                                    <div class="review-date">
                                        <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?php
                                    for($i = 1; $i <= 5; $i++) {
                                        if($i <= $review['rating']) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="review-text">
                                    <?php echo nl2br($review['comment']); ?>
                                </div>
                            </div>
                            <?php
                                }
                            } else {
                            ?>
                            <div class="no-reviews">
                                <p>There are no reviews yet for this product. Be the first to review!</p>
                            </div>
                            <?php 
                            }
                            ?>
                            
                            <?php if($reviewsResult->num_rows > 0 && $product['rating_count'] > 5) { ?>
                            <div class="view-all-reviews">
                                <a href="reviews.php?product_id=<?php echo $productId; ?>" class="btn-secondary">View All Reviews</a>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="related-products">
        <div class="container">
            <h2 class="section-title">Related Products</h2>
            
            <div class="products-grid">
                <?php
                if($relatedResult->num_rows > 0) {
                    while($relatedProduct = $relatedResult->fetch_assoc()) {
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo $relatedProduct['image_url']; ?>" alt="<?php echo $relatedProduct['name']; ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo $relatedProduct['name']; ?></h3>
                        <div class="product-price">$<?php echo number_format($relatedProduct['price'], 2); ?></div>
                        <div class="product-rating">
                            <?php
                            for($i = 1; $i <= 5; $i++) {
                                if($i <= $relatedProduct['avg_rating']) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <a href="product.php?id=<?php echo $relatedProduct['id']; ?>" class="btn-view">View Details</a>
                        <button class="btn-add-cart" data-id="<?php echo $relatedProduct['id']; ?>">Add to Cart</button>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo "<p>No related products available.</p>";
                }
                ?>
            </div>
        </div>
    </section>
</main>

<?php include_once 'includes/footer.php'; ?>

<script>
    // Image gallery
    document.addEventListener('DOMContentLoaded', function() {
        const thumbnails = document.querySelectorAll('.thumbnail');
        const mainImage = document.getElementById('main-product-image');
        
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Remove active class from all thumbnails
                thumbnails.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked thumbnail
                this.classList.add('active');
                
                // Update main image
                mainImage.src = this.getAttribute('data-image');
            });
        });
        
        // Quantity selector
        const quantityInput = document.getElementById('product-quantity');
        const qtyMinus = document.getElementById('qty-minus');
        const qtyPlus = document.getElementById('qty-plus');
        
        if(quantityInput && qtyMinus && qtyPlus) {
            qtyMinus.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                if(value > 1) {
                    quantityInput.value = value - 1;
                }
            });
            
            qtyPlus.addEventListener('click', function() {
                let value = parseInt(quantityInput.value);
                let max = parseInt(quantityInput.getAttribute('max'));
                if(value < max) {
                    quantityInput.value = value + 1;
                }
            });
        }
        
        // Tabs
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and panes
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Show corresponding tab pane
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Add to cart
        const addToCart = document.getElementById('add-to-cart');
        
        if(addToCart) {
            addToCart.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const quantity = document.getElementById('product-quantity').value;
                
                addProductToCart(productId, quantity);
            });
        }
    });
    
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
    
    function showNotification(message, type) {
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
</script>