<?php
/**
 * Common Functions
 * 
 * This file contains helper functions used throughout the website.
 */

/**
 * Format price with currency symbol
 * 
 * @param float $price
 * @param string $currency
 * @return string
 */
function formatPrice($price, $currency = '$') {
    return $currency . number_format($price, 2);
}

/**
 * Get truncated string with specified length
 * 
 * @param string $string
 * @param int $length
 * @param string $append
 * @return string
 */
function truncateString($string, $length = 100, $append = '...') {
    if (strlen($string) > $length) {
        $string = substr($string, 0, $length) . $append;
    }
    return $string;
}

/**
 * Generate slug from string
 * 
 * @param string $string
 * @return string
 */
function generateSlug($string) {
    // Replace non letter or digits by -
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    
    // Transliterate
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    
    // Remove unwanted characters
    $string = preg_replace('~[^-\w]+~', '', $string);
    
    // Trim
    $string = trim($string, '-');
    
    // Remove duplicate -
    $string = preg_replace('~-+~', '-', $string);
    
    // Lowercase
    $string = strtolower($string);
    
    if (empty($string)) {
        return 'n-a';
    }
    
    return $string;
}

/**
 * Get rating stars HTML
 * 
 * @param float $rating
 * @param int $max
 * @return string
 */
function getRatingStars($rating, $max = 5) {
    $html = '';
    
    for ($i = 1; $i <= $max; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star"></i>';
        } else if ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }
    
    return $html;
}

/**
 * Get pagination HTML
 * 
 * @param int $currentPage
 * @param int $totalPages
 * @param string $url
 * @param array $params
 * @return string
 */
function getPagination($currentPage, $totalPages, $url, $params = []) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<div class="pagination">';
    
    // Previous button
    if ($currentPage > 1) {
        $prevParams = $params;
        $prevParams['page'] = $currentPage - 1;
        $prevUrl = $url . '?' . http_build_query($prevParams);
        $html .= '<a href="' . $prevUrl . '" class="page-link">Previous</a>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $pageParams = $params;
        $pageParams['page'] = $i;
        $pageUrl = $url . '?' . http_build_query($pageParams);
        
        $activeClass = ($i == $currentPage) ? 'active' : '';
        $html .= '<a href="' . $pageUrl . '" class="page-link ' . $activeClass . '">' . $i . '</a>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $nextParams = $params;
        $nextParams['page'] = $currentPage + 1;
        $nextUrl = $url . '?' . http_build_query($nextParams);
        $html .= '<a href="' . $nextUrl . '" class="page-link">Next</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to specified URL
 * 
 * @param string $url
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Get current page URL
 * 
 * @return string
 */
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $currentUrl;
}

/**
 * Sanitize input data
 * 
 * @param string $data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Generate random string
 * 
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}