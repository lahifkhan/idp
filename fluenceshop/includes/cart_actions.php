<?php
/**
 * Cart Actions
 * 
 * This file handles cart-related AJAX requests.
 */

session_start();
include_once 'db_connect.php';

// Initialize response
$response = [
    'success' => false,
    'message' => 'Invalid action',
    'cart_count' => 0,
    'subtotal' => 0,
    'tax' => 0,
    'shipping' => 0,
    'discount' => 0,
    'total' => 0
];

// Check if action is set
if(isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Initialize cart if not set
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    // Add to cart
    if($action == 'add') {
        if(isset($_POST['product_id']) && isset($_POST['quantity'])) {
            $productId = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            // Check if product exists
            $productQuery = "SELECT id, stock FROM products WHERE id = $productId";
            $productResult = $conn->query($productQuery);
            
            if($productResult->num_rows > 0) {
                $product = $productResult->fetch_assoc();
                
                // Check if enough stock
                if($product['stock'] >= $quantity) {
                    // Add to cart
                    if(isset($_SESSION['cart'][$productId])) {
                        $_SESSION['cart'][$productId] += $quantity;
                    } else {
                        $_SESSION['cart'][$productId] = $quantity;
                    }
                    
                    $response['success'] = true;
                    $response['message'] = 'Product added to cart';
                } else {
                    $response['message'] = 'Not enough stock available';
                }
            } else {
                $response['message'] = 'Product not found';
            }
        } else {
            $response['message'] = 'Invalid product or quantity';
        }
    }
    
    // Update cart
    else if($action == 'update') {
        if(isset($_POST['product_id']) && isset($_POST['quantity'])) {
            $productId = (int)$_POST['product_id'];
            $quantity = (int)$_POST['quantity'];
            
            // Check if product exists in cart
            if(isset($_SESSION['cart'][$productId])) {
                // Check if product exists
                $productQuery = "SELECT id, stock, price FROM products WHERE id = $productId";
                $productResult = $conn->query($productQuery);
                
                if($productResult->num_rows > 0) {
                    $product = $productResult->fetch_assoc();
                    
                    // Check if enough stock
                    if($product['stock'] >= $quantity) {
                        // Update cart
                        $_SESSION['cart'][$productId] = $quantity;
                        
                        $response['success'] = true;
                        $response['message'] = 'Cart updated';
                        $response['item_total'] = $product['price'] * $quantity;
                    } else {
                        $response['message'] = 'Not enough stock available';
                    }
                } else {
                    $response['message'] = 'Product not found';
                }
            } else {
                $response['message'] = 'Product not in cart';
            }
        } else {
            $response['message'] = 'Invalid product or quantity';
        }
    }
    
    // Remove from cart
    else if($action == 'remove') {
        if(isset($_POST['product_id'])) {
            $productId = (int)$_POST['product_id'];
            
            // Check if product exists in cart
            if(isset($_SESSION['cart'][$productId])) {
                // Remove from cart
                unset($_SESSION['cart'][$productId]);
                
                $response['success'] = true;
                $response['message'] = 'Product removed from cart';
            } else {
                $response['message'] = 'Product not in cart';
            }
        } else {
            $response['message'] = 'Invalid product';
        }
    }
    
    // Clear cart
    else if($action == 'clear') {
        // Clear cart
        $_SESSION['cart'] = array();
        
        // Remove discount code if applied
        if(isset($_SESSION['applied_discount'])) {
            unset($_SESSION['applied_discount']);
        }
        
        $response['success'] = true;
        $response['message'] = 'Cart cleared';
    }
    
    // Apply discount
    else if($action == 'apply_discount') {
        if(isset($_POST['code'])) {
            $code = $conn->real_escape_string($_POST['code']);
            
            // Check if discount code exists
            $discountQuery = "SELECT * FROM discount_codes WHERE code = '$code' AND active = 1";
            $discountResult = $conn->query($discountQuery);
            
            if($discountResult->num_rows > 0) {
                $discount = $discountResult->fetch_assoc();
                
                // Check if discount code is expired
                if($discount['expires_at'] && strtotime($discount['expires_at']) < time()) {
                    $response['message'] = 'Discount code has expired';
                } else {
                    // Apply discount
                    $_SESSION['applied_discount'] = $code;
                    
                    $response['success'] = true;
                    $response['message'] = 'Discount code applied';
                }
            } else {
                $response['message'] = 'Invalid discount code';
            }
        } else {
            $response['message'] = 'No discount code provided';
        }
    }
    
    // Remove discount
    else if($action == 'remove_discount') {
        // Remove discount code
        if(isset($_SESSION['applied_discount'])) {
            unset($_SESSION['applied_discount']);
        }
        
        $response['success'] = true;
        $response['message'] = 'Discount code removed';
    }
    
    // Calculate cart totals
    $cart = $_SESSION['cart'];
    $subtotal = 0;
    $cartCount = 0;
    
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
                $subtotal += $product['price'] * $quantity;
                $cartCount += $quantity;
            }
        }
    }
    
    // Calculate shipping and tax
    $shipping = $subtotal > 0 ? 10.00 : 0;
    $tax = $subtotal * 0.08; // 8% tax
    
    // Calculate discount
    $discountAmount = 0;
    if(isset($_SESSION['applied_discount'])) {
        $code = $_SESSION['applied_discount'];
        $discountQuery = "SELECT * FROM discount_codes WHERE code = '$code' AND active = 1";
        $discountResult = $conn->query($discountQuery);
        
        if($discountResult->num_rows > 0) {
            $discount = $discountResult->fetch_assoc();
            
            if($discount['type'] == 'percentage') {
                $discountAmount = $subtotal * ($discount['value'] / 100);
            } else {
                $discountAmount = $discount['value'];
            }
        }
    }
    
    // Calculate total
    $total = $subtotal - $discountAmount + $shipping + $tax;
    
    // Update response
    $response['cart_count'] = $cartCount;
    $response['subtotal'] = $subtotal;
    $response['tax'] = $tax;
    $response['shipping'] = $shipping;
    $response['discount'] = $discountAmount;
    $response['total'] = $total;
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);