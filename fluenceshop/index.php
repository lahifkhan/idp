<?php
    include_once 'includes/header.php';
    include_once 'includes/db_connect.php';
    
    // Fetch featured products
    $featuredQuery = "SELECT * FROM products WHERE featured = 1 LIMIT 6";
    $featuredResult = $conn->query($featuredQuery);
    
    // Fetch combo deals
    $comboQuery = "SELECT * FROM combo_deals LIMIT 3";
    $comboResult = $conn->query($comboQuery);
?>

<main>
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>The Ultimate Shop for <span>Influencers</span></h1>
                <p>Everything you need to create stunning content that captivates your audience</p>
                <a href="products.php" class="btn-primary">Shop Now</a>
            </div>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="our-story">
        <div class="container">
            <h2 class="section-title">Our Story</h2>
            <div class="story-content">
                <div class="story-image">
                    <img src="https://images.pexels.com/photos/3811082/pexels-photo-3811082.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" alt="FluenceShop Story">
                </div>
                <div class="story-text">
                    <h3>Built for Creators by Creators</h3>
                    <p>At FluenceShop, we understand the unique needs of content creators and influencers. Founded in 2023, we've made it our mission to provide high-quality tools and equipment that help you stand out.</p>
                    <p>From lighting equipment to microphones, cameras to editing software, we've carefully curated the best products for creating professional content.</p>
                    <a href="about.php" class="btn-secondary">Learn More</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="products-grid">
                <?php
                if ($featuredResult->num_rows > 0) {
                    while($product = $featuredResult->fetch_assoc()) {
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                    </div>
                    <div class="product-info">
                        <h3><?php echo $product['name']; ?></h3>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
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
                        </div>
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-view">View Details</a>
                        <button class="btn-add-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo "<p>No featured products available.</p>";
                }
                ?>
            </div>
            <div class="view-all">
                <a href="products.php" class="btn-secondary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Combo Deals Section -->
    <section class="combo-deals">
        <div class="container">
            <h2 class="section-title">Combo Deals <span class="discount-tag">Save up to 30%</span></h2>
            <div class="deals-grid">
                <?php
                if ($comboResult->num_rows > 0) {
                    while($combo = $comboResult->fetch_assoc()) {
                        // Calculate discount percentage
                        $regularPrice = $combo['regular_price'];
                        $discountPrice = $combo['discount_price'];
                        $savingsPercent = round(($regularPrice - $discountPrice) / $regularPrice * 100);
                ?>
                <div class="combo-card">
                    <div class="combo-image">
                        <span class="save-badge">Save <?php echo $savingsPercent; ?>%</span>
                        <img src="<?php echo $combo['image_url']; ?>" alt="<?php echo $combo['name']; ?>">
                    </div>
                    <div class="combo-info">
                        <h3><?php echo $combo['name']; ?></h3>
                        <div class="combo-price">
                            <span class="original-price">$<?php echo number_format($combo['regular_price'], 2); ?></span>
                            <span class="discount-price">$<?php echo number_format($combo['discount_price'], 2); ?></span>
                        </div>
                        <p class="combo-description"><?php echo $combo['description']; ?></p>
                        <button class="btn-add-cart" data-id="<?php echo $combo['id']; ?>" data-type="combo">Add to Cart</button>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo "<p>No combo deals available.</p>";
                }
                ?>
            </div>
        </div>
    </section>
</main>

<?php include_once 'includes/footer.php'; ?>