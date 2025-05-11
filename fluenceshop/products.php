<?php
    include_once 'includes/header.php';
    include_once 'includes/db_connect.php';
    
    // Handle filtering and pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $itemsPerPage = 12;
    $offset = ($page - 1) * $itemsPerPage;
    
    // Handle search
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $searchCondition = '';
    if(!empty($search)) {
        $searchCondition = " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
    }
    
    // Handle category filter
    $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    $categoryCondition = '';
    if($category > 0) {
        $categoryCondition = " AND category_id = $category";
    }
    
    // Handle price filter
    $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 10000;
    $priceCondition = " AND price BETWEEN $minPrice AND $maxPrice";
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM products WHERE 1=1$searchCondition$categoryCondition$priceCondition";
    $countResult = $conn->query($countQuery);
    $totalItems = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    // Get products
    $productsQuery = "SELECT p.*, c.name as category_name FROM products p
                     JOIN categories c ON p.category_id = c.id
                     WHERE 1=1$searchCondition$categoryCondition$priceCondition
                     ORDER BY p.id DESC LIMIT $offset, $itemsPerPage";
    $productsResult = $conn->query($productsQuery);
    
    // Get categories for filter
    $categoriesQuery = "SELECT * FROM categories ORDER BY name";
    $categoriesResult = $conn->query($categoriesQuery);
?>

<main>
    <section class="products-header">
        <div class="container">
            <h1>All Products</h1>
        </div>
    </section>

    <section class="products-container">
        <div class="container">
            <div class="products-layout">
                <!-- Filters Sidebar -->
                <aside class="filters">
                    <div class="filter-section">
                        <h3>Search</h3>
                        <form action="products.php" method="GET" class="search-form">
                            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>

                    <div class="filter-section">
                        <h3>Categories</h3>
                        <ul class="category-list">
                            <li>
                                <a href="products.php" <?php echo $category == 0 ? 'class="active"' : ''; ?>>
                                    All Categories
                                </a>
                            </li>
                            <?php
                            if ($categoriesResult->num_rows > 0) {
                                while($cat = $categoriesResult->fetch_assoc()) {
                                    $activeClass = $category == $cat['id'] ? 'class="active"' : '';
                                    echo '<li><a href="products.php?category=' . $cat['id'] . '" ' . $activeClass . '>' . $cat['name'] . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>

                    <div class="filter-section">
                        <h3>Price Range</h3>
                        <form action="products.php" method="GET" class="price-filter-form">
                            <?php if($category > 0) { ?>
                                <input type="hidden" name="category" value="<?php echo $category; ?>">
                            <?php } ?>
                            <?php if(!empty($search)) { ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php } ?>
                            <div class="price-inputs">
                                <div>
                                    <label for="min_price">Min $</label>
                                    <input type="number" id="min_price" name="min_price" value="<?php echo $minPrice; ?>" min="0">
                                </div>
                                <div>
                                    <label for="max_price">Max $</label>
                                    <input type="number" id="max_price" name="max_price" value="<?php echo $maxPrice; ?>" min="0">
                                </div>
                            </div>
                            <button type="submit" class="btn-apply-filter">Apply Filter</button>
                        </form>
                    </div>
                </aside>

                <!-- Products Grid -->
                <div class="products-grid-container">
                    <?php if($totalItems > 0) { ?>
                        <div class="products-count">
                            Showing <?php echo min($totalItems, $offset + 1); ?>-<?php echo min($totalItems, $offset + $itemsPerPage); ?> of <?php echo $totalItems; ?> products
                        </div>

                        <div class="products-grid">
                            <?php
                            while($product = $productsResult->fetch_assoc()) {
                            ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>">
                                </div>
                                <div class="product-info">
                                    <span class="product-category"><?php echo $product['category_name']; ?></span>
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
                                        <span class="rating-count">(<?php echo $product['rating_count']; ?>)</span>
                                    </div>
                                    <div class="product-actions">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn-view">View Details</a>
                                        <button class="btn-add-cart" data-id="<?php echo $product['id']; ?>">Add to Cart</button>
                                    </div>
                                </div>
                            </div>
                            <?php
                            }
                            ?>
                        </div>

                        <!-- Pagination -->
                        <?php if($totalPages > 1) { ?>
                        <div class="pagination">
                            <?php if($page > 1) { ?>
                                <a href="products.php?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $category > 0 ? '&category=' . $category : ''; ?><?php echo $minPrice > 0 ? '&min_price=' . $minPrice : ''; ?><?php echo $maxPrice < 10000 ? '&max_price=' . $maxPrice : ''; ?>" class="page-link">Previous</a>
                            <?php } ?>
                            
                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            for($i = $startPage; $i <= $endPage; $i++) {
                                $activeClass = $i == $page ? 'active' : '';
                                echo '<a href="products.php?page=' . $i . (!empty($search) ? '&search=' . urlencode($search) : '') . ($category > 0 ? '&category=' . $category : '') . ($minPrice > 0 ? '&min_price=' . $minPrice : '') . ($maxPrice < 10000 ? '&max_price=' . $maxPrice : '') . '" class="page-link ' . $activeClass . '">' . $i . '</a>';
                            }
                            ?>
                            
                            <?php if($page < $totalPages) { ?>
                                <a href="products.php?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo $category > 0 ? '&category=' . $category : ''; ?><?php echo $minPrice > 0 ? '&min_price=' . $minPrice : ''; ?><?php echo $maxPrice < 10000 ? '&max_price=' . $maxPrice : ''; ?>" class="page-link">Next</a>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="no-products">
                            <p>No products found matching your criteria.</p>
                            <a href="products.php" class="btn-secondary">Clear Filters</a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include_once 'includes/footer.php'; ?>