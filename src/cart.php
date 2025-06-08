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
        <link rel="stylesheet" href="assets/css/cart.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body>
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: flex; align-items: center; justify-content: center;">
            <div style="background: white; padding: 3rem; border-radius: 15px; text-align: center; max-width: 400px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                <div style="color: #ff6b35; font-size: 4rem; margin-bottom: 1rem;">
                    <i class="fas fa-lock"></i>
                </div>
                <h2 style="color: #333; margin-bottom: 1rem;">Login Required</h2>
                <p style="color: #666; margin-bottom: 2rem;">You need to be logged in to view your cart. Redirecting to login page...</p>
                <div style="display: #ff6b35; align-items: center; gap: 10px; color                    <i class="fas fa-spinner fa-spinner"></i>
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
        <meta name="viewport" content="width=device-width, initial-scale="1.0">
        <title>Shopping Cart - Crust Pizza</title>
        <link rel="stylesheet" href="assets/css/cart.css">
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
        // CrustPizza namespace to manage cart operations
        const CrustPizza = {
            loadCartItems: async function() {
                try {
                    const userId = document.querySelector('meta[name="user-id"]')?.content;
                    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

                    if (!userId || !csrfToken) {
                        console.error('Missing userId or csrfToken:', { userId, csrfToken });
                        showNotification('Session error. Please log in again.', 'error');
                        setTimeout(() => window.location.href = 'login.php?redirect=cart.php', 2000);
                        return;
                    }

                    // Fetch cart items from API
                    const response = await fetch('api/cart_api.php?action=get', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            csrf_token: csrfToken
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('API Response:', result); // Debug log

                    const cartItemsContainer = document.getElementById('cartItems');
                    const emptyCartMessage = document.getElementById('emptyCartMessage');
                    const cartContainer = document.getElementById('cartContainer');
                    const checkoutBtn = document.getElementById('checkoutBtn');

                    if (result.success && result.data && Array.isArray(result.data.items) && result.data.items.length > 0) {
                        // Sync localStorage with API data
                        const mappedItems = result.data.items.map(item => ({
                            cart_id: item.cart_id,
                            pizza_id: item.pizza_id,
                            name: item.item_name,
                            size: item.size,
                            price: item.unit_price,
                            quantity: item.quantity,
                            item_type: item.item_type,
                            custom_ingredients: item.custom_ingredients,
                            special_instructions: item.special_instructions
                        }));
                        localStorage.setItem('crustPizzaCart', JSON.stringify(mappedItems));

                        // Clear existing items
                        cartItemsContainer.innerHTML = '';

                        let subtotal = 0;

                        // Render cart items
                        result.data.items.forEach(item => {
                            const price = parseFloat(item.unit_price) || 0;
                            const quantity = parseInt(item.quantity) || 1;
                            const itemTotal = price * quantity;
                            subtotal += itemTotal;

                            const cartItem = document.createElement('div');
                            cartItem.className = 'cart-item';
                            cartItem.innerHTML = `
                                <div class="cart-item-info">
                                    <h4>${item.item_name || 'Unknown Pizza'}</h4>
                                    <p class="item-detail">Size: ${item.size || 'Medium'}</p>
                                    ${item.custom_ingredients ? `<p class="item-detail">Extras: ${item.custom_ingredients}</p>` : ''}
                                    ${item.special_instructions ? `<p class="item-detail">Instructions: ${item.special_instructions}</p>` : ''}
                                    <p class="item-price">$${itemTotal.toFixed(2)}</p>
                                </div>
                                <div class="cart-item-controls">
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="CrustPizza.updateCartQuantity('${item.cart_id}', ${quantity - 1})" ${quantity <= 1 ? 'disabled' : ''}>
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <span class="quantity-display">${quantity}</span>
                                        <button class="quantity-btn" onclick="CrustPizza.updateCartQuantity('${item.cart_id}', ${quantity + 1})">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    <button class="remove-btn" onclick="CrustPizza.removeFromCart('${item.cart_id}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            `;
                            cartItemsContainer.appendChild(cartItem);
                        });

                        // Update cart summary
                        const tax = subtotal * 0.10; // 10% GST
                        const deliveryFee = subtotal > 50 ? 0 : 5.00; // Example: Free delivery over $50
                        const total = subtotal + tax + deliveryFee;

                        document.getElementById('cartSubtotal').textContent = `$${subtotal.toFixed(2)}`;
                        document.getElementById('cartTax').textContent = `$${tax.toFixed(2)}`;
                        document.getElementById('deliveryFee').textContent = `$${deliveryFee.toFixed(2)}`;
                        document.getElementById('cartTotal').textContent = `$${total.toFixed(2)}`;

                        // Show cart and hide empty message
                        emptyCartMessage.style.display = 'none';
                        cartContainer.style.display = 'grid';
                        checkoutBtn.classList.remove('disabled');

                        // Update cart count in header
                        updateCartCount();
                    } else {
                        // Show empty cart message
                        cartItemsContainer.innerHTML = '';
                        emptyCartMessage.style.display = 'flex';
                        cartContainer.style.display = 'none';
                        checkoutBtn.classList.add('disabled');

                        // Clear localStorage
                        localStorage.setItem('crustPizzaCart', JSON.stringify([]));
                        updateCartCount();

                        if (!result.success) {
                            console.error('API Error:', result.message);
                            showNotification(result.message || 'Failed to load cart items.', 'error');
                        }
                    }
                } catch (error) {
                    console.error('Error loading cart items:', error);
                    showNotification('Failed to load cart items. Please try again.', 'error');
                    document.getElementById('cartItems').innerHTML = '';
                    document.getElementById('emptyCartMessage').style.display = 'flex';
                    document.getElementById('cartContainer').style.display = 'none';
                    document.getElementById('checkoutBtn').classList.add('disabled');
                }
            },

            updateCartQuantity: async function(cartId, quantity) {
                if (quantity < 1) {
                    return this.removeFromCart(cartId);
                }

                try {
                    const userId = document.querySelector('meta[name="user-id"]')?.content;
                    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

                    if (!userId || !csrfToken) {
                        showNotification('Session error. Please log in again.', 'error');
                        return;
                    }

                    const response = await fetch('api/cart_api.php?action=update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            cart_id: cartId,
                            quantity: quantity,
                            user_id: userId,
                            csrf_token: csrfToken
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('Update Quantity Response:', result);

                    if (result.success) {
                        // Update localStorage
                        let cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
                        const itemIndex = cart.findIndex(item => item.cart_id === cartId);
                        if (itemIndex !== -1) {
                            cart[itemIndex].quantity = quantity;
                            localStorage.setItem('crustPizzaCart', JSON.stringify(cart));
                        }

                        showNotification('Quantity updated successfully!', 'success');
                        this.loadCartItems();
                    } else {
                        showNotification(result.message || 'Failed to update quantity.', 'error');
                    }
                } catch (error) {
                    console.error('Error updating quantity:', error);
                    showNotification('Failed to update quantity. Please try again.', 'error');
                }
            },

            removeFromCart: async function(cartId) {
                try {
                    const userId = document.querySelector('meta[name="user-id"]')?.content;
                    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

                    if (!userId || !csrfToken) {
                        showNotification('Session error. Please log in again.', 'error');
                        return;
                    }

                    const response = await fetch('api/cart_api.php?action=remove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            cart_id: cartId,
                            user_id: userId,
                            csrf_token: csrfToken
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('Remove Item Response:', result);

                    if (result.success) {
                        // Update localStorage
                        let cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
                        cart = cart.filter(item => item.cart_id !== cartId);
                        localStorage.setItem('crustPizzaCart', JSON.stringify(cart));

                        showNotification('Item removed from cart!', 'success');
                        this.loadCartItems();
                    } else {
                        showNotification(result.message || 'Failed to remove item.', 'error');
                    }
                } catch (error) {
                    console.error('Error removing item:', error);
                    showNotification('Failed to remove item. Please try again.', 'error');
                }
            },

            clearCart: async function() {
                try {
                    const userId = document.querySelector('meta[name="user-id"]')?.content;
                    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;

                    if (!userId || !csrfToken) {
                        showNotification('Session error. Please log in again.', 'error');
                        return;
                    }

                    const response = await fetch('api/cart_api.php?action=clear', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            user_id: userId,
                            csrf_token: csrfToken
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }

                    const result = await response.json();
                    console.log('Clear Cart Response:', result);

                    if (result.success) {
                        // Clear localStorage
                        localStorage.setItem('crustPizzaCart', JSON.stringify([]));

                        showNotification('Cart cleared successfully!', 'success');
                        this.loadCartItems();
                    } else {
                        showNotification(result.message || 'Failed to clear cart.', 'error');
                    }
                } catch (error) {
                    console.error('Error clearing cart:', error);
                    showNotification('Failed to clear cart. Please try again.', 'error');
                }
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            CrustPizza.loadCartItems();
        });

        function updateCartCount() {
            try {
                const cart = JSON.parse(localStorage.getItem('crustPizzaCart')) || [];
                if (!Array.isArray(cart)) {
                    console.error('Invalid cart data:', cart);
                    localStorage.setItem('crustPizzaCart', JSON.stringify([]));
                    document.getElementById('cartCount').textContent = '0';
                    return;
                }
                const cartCount = cart.reduce((total, item) => {
                    const qty = Number(item.quantity) || 1;
                    return total + qty;
                }, 0);
                const cartCountElement = document.getElementById('cartCount');
                if (cartCountElement) {
                    cartCountElement.textContent = cartCount.toString();
                }
            } catch (error) {
                console.error('Error updating cart count:', error);
                localStorage.setItem('crustPizzaCart', JSON.stringify([]));
                document.getElementById('cartCount').textContent = '0';
            }
        }

        function showNotification(message, type = "info") {
            const existingNotifications = document.querySelectorAll(".cart-notification");
            existingNotifications.forEach(notification => notification.remove());

            const notification = document.createElement("div");
            notification.className = `cart-notification ${type}`;

            let icon = 'info-circle';
            if (type === 'success') icon = 'check-circle';
            if (type === 'error') icon = 'exclamation-circle';
            if (type === 'warning') icon = 'exclamation-triangle';

            notification.innerHTML = `
                <i class="fas fa-${icon}"></i> 
                ${message}
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add("slide-out");
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 3000);
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

        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
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

        /* Notification Styles */
        .cart-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            z-index: 9999;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }

        .cart-notification.error {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
        }

        .cart-notification.warning {
            background: linear-gradient(135deg, #ffc107, #f39c12);
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
        }

        .cart-notification.info {
            background: linear-gradient(135deg, #17a2b8, #3498db);
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.4);
        }

        .cart-notification.slide-out {
            animation: slideOut 0.3s ease-in;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
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