<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

startSession();

if (!isLoggedIn()) {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cart - Login Required - Crust Pizza</title>
        <link rel="stylesheet" href="assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center;">
            <div style="background: white; padding: 3rem; border-radius: 15px; text-align: center; max-width: 400px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                <div style="color: #ff6b35; font-size: 4rem; margin-bottom: 1rem;">
                    <i class="fas fa-lock"></i>
                </div>
                <h2 style="color: #333; margin-bottom: 1rem;">Login Required</h2>
                <p style="color: #666; margin-bottom: 2rem;">You need to be logged in to view your cart. Redirecting to login page...</p>
                <div style="display: flex; align-items: center; justify-content: center; gap: 10px; color: #ff6b35;">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>Redirecting in <span id="countdown">3</span> seconds</span>
                </div>
            </div>
        </div>

        <script>
            let countdown = 3;
            const countdownElement = document.getElementById('countdown');

            const timer = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = 'login.php?redirect=cart.php';
                }
            }, 1000);
        </script>
    </body>

    </html>
<?php
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Crust Pizza</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include 'header.php'; ?>

    <main style="margin-top: 80px; padding: 40px 20px;">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-shopping-cart"></i> Your Cart</h1>
                <p>Review your items and proceed to checkout</p>
            </div>

            <?php displayFlashMessages(); ?>

            <div class="cart-layout" id="cartContainer">
                <!-- Cart Items - Left Side -->
                <div class="cart-items-section">
                    <div class="card">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="margin: 0;"><i class="fas fa-shopping-bag"></i> Cart Items</h3>
                            <button class="btn btn-outline" onclick="CrustPizza.clearCart()" style="padding: 0.4rem 0.75rem; font-size: 0.85rem; min-width: auto;">
                                <i class="fas fa-trash"></i> Clear
                            </button>
                        </div>
                        <div class="card-body" id="cartItems">
                            <!-- Cart items will be loaded here by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Cart Summary - Right Side -->
                <div class="cart-summary-section">
                    <!-- Order Summary -->
                    <div class="card cart-summary-card">
                        <div class="card-header">
                            <h3 style="margin: 0;"><i class="fas fa-receipt"></i> Order Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="summary-details">
                                <div class="summary-line">
                                    <span>Subtotal:</span>
                                    <span id="cartSubtotal">$0.00</span>
                                </div>
                                <div class="summary-line">
                                    <span>GST (10%):</span>
                                    <span id="cartTax">$0.00</span>
                                </div>
                                <div class="summary-line">
                                    <span>Delivery Fee:</span>
                                    <span id="deliveryFee">$0.00</span>
                                </div>

                                <div class="summary-line total-line">
                                    <span><strong>Total:</strong></span>
                                    <span id="cartTotal"><strong>$0.00</strong></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="checkout.php" class="btn btn-primary checkout-btn" id="checkoutBtn">
                                <i class="fas fa-credit-card"></i> Proceed to Checkout
                            </a>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card quick-actions-card">
                        <div class="card-header">
                            <h4 style="margin: 0;"><i class="fas fa-bolt"></i> Quick Actions</h4>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions-grid">
                                <a href="menu.php" class="btn btn-outline quick-action-btn">
                                    <i class="fas fa-pizza-slice"></i>
                                    <span>Continue Shopping</span>
                                </a>
                                <a href="build-pizza.php" class="btn btn-outline quick-action-btn">
                                    <i class="fas fa-magic"></i>
                                    <span>Build Custom Pizza</span>
                                </a>
                                <a href="track-order.php" class="btn btn-outline quick-action-btn">
                                    <i class="fas fa-search"></i>
                                    <span>Track Order</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Cart Message -->
            <div id="emptyCartMessage" class="empty-cart-container">
                <div class="empty-cart-content">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some delicious pizzas to get started!</p>
                    <div class="empty-cart-actions">
                        <a href="menu.php" class="btn btn-primary">
                            <i class="fas fa-pizza-slice"></i> Browse Menu
                        </a>
                        <a href="build-pizza.php" class="btn btn-outline">
                            <i class="fas fa-magic"></i> Build Your Pizza
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            CrustPizza.loadCartItems();
        });

        function updateQuantity(cartId, quantity) {
            if (confirm(`Update quantity to ${quantity}?`)) {
                CrustPizza.updateCartQuantity(cartId, quantity);
            }
        }

        function removeFromCart(cartId) {
            if (confirm('Remove this item from your cart?')) {
                CrustPizza.removeFromCart(cartId);
            }
        }

        function clearCart() {
            if (confirm('Are you sure you want to clear your cart?')) {
                CrustPizza.clearCart();
            }
        }

        function toggleDropdown() {
            const dropdownMenu = document.getElementById('dropdownMenu');
            const isOpen = dropdownMenu.classList.toggle('show');
            document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', isOpen);
        }

        function toggleNavMenu() {
            const navMenu = document.getElementById('navMenu');
            navMenu.classList.toggle('active');
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.dropdown');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const navMenu = document.getElementById('navMenu');
            const navToggle = document.querySelector('.nav-toggle');
            if (!dropdown.contains(event.target) && !navToggle.contains(event.target)) {
                dropdownMenu.classList.remove('show');
                navMenu.classList.remove('active');
                document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.getElementById('dropdownMenu').classList.remove('show');
                document.getElementById('navMenu').classList.remove('active');
                document.querySelector('.dropdown-toggle').setAttribute('aria-expanded', 'false');
            }
        });
    </script>
    <style>
        /* Cart Layout Styles */
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-top: 2rem;
            align-items: start;
        }

        .cart-items-section {
            min-height: 400px;
        }

        .cart-summary-section {
            position: sticky;
            top: 100px;
            z-index: 10;
        }

        /* Cart Item Styles */
        .cart-item {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s ease;
        }

        .cart-item:hover {
            background-color: #f8f9fa;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-info {
            flex: 1;
            margin-right: 1rem;
        }

        .cart-item-info h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .item-detail {
            margin: 0.25rem 0;
            color: #666;
            font-size: 0.9rem;
        }

        .item-price {
            margin: 0.5rem 0 0 0;
            font-weight: 600;
            color: #ff6b35;
            font-size: 1.1rem;
        }

        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f8f9fa;
            border-radius: 25px;
            padding: 0.25rem;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #ff6b35;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .quantity-btn:hover {
            background: #ff6b35;
            color: white;
            transform: scale(1.1);
        }

        .quantity-display {
            min-width: 40px;
            text-align: center;
            font-weight: 600;
            color: #333;
        }

        .remove-btn {
            width: 36px;
            height: 36px;
            border: 2px solid #dc3545;
            background: transparent;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #dc3545;
        }

        .remove-btn:hover {
            background: #dc3545;
            color: white;
            transform: scale(1.1);
        }

        /* Summary Styles */
        .cart-summary-card {
            margin-bottom: 1.5rem;
        }

        .summary-details {
            font-size: 1rem;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding: 0.25rem 0;
        }

        .summary-line span {
            color: #333;
        }

        .total-line {
            font-size: 1.2rem;
            padding: 0.5rem 0;
            border-top: 2px solid #ff6b35;
            margin-top: 0.5rem;
        }

        .total-line span {
            color: #ff6b35;
        }

        .delivery-note {
            margin: 0.5rem 0;
            padding: 0.5rem;
            background: #e8f5e8;
            border-radius: 5px;
            text-align: center;
        }

        .delivery-note small {
            color: #28a745;
            font-weight: 500;
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        .checkout-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Quick Actions Styles */
        .quick-actions-card .card-header h4 {
            font-size: 1.1rem;
            color: #333;
        }

        .quick-actions-grid {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            text-align: left;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .quick-action-btn i {
            width: 20px;
            text-align: center;
            color: #ff6b35;
        }

        .quick-action-btn:hover {
            background: #ff6b35;
            color: white;
            transform: translateX(5px);
        }

        .quick-action-btn:hover i {
            color: white;
        }

        /* Empty Cart Styles */
        .empty-cart-container {
            display: none;
            justify-content: center;
            align-items: center;
            min-height: 500px;
            text-align: center;
        }

        .empty-cart-content {
            max-width: 400px;
            padding: 3rem;
        }

        .empty-cart-content i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 1.5rem;
        }

        .empty-cart-content h3 {
            margin: 0 0 1rem 0;
            color: #333;
            font-size: 1.8rem;
        }

        .empty-cart-content p {
            margin: 0 0 2rem 0;
            color: #666;
            font-size: 1.1rem;
        }

        .empty-cart-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .cart-layout {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .cart-summary-section {
                position: static;
                order: -1;
            }

            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .cart-item-controls {
                width: 100%;
                justify-content: space-between;
            }

            .empty-cart-actions {
                flex-direction: column;
            }

            .quick-actions-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .cart-item {
                padding: 1rem;
            }

            .checkout-btn {
                padding: 0.875rem;
                font-size: 1rem;
            }

            .empty-cart-content {
                padding: 2rem 1rem;
            }
        }
    </style>
</body>

</html>