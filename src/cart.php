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
    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-pizza-slice"></i>
                <p><a href="index.php" style="text-decoration: none; color: inherit;">Crust Pizza</a></p>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="menu.php" class="nav-link">Menu</a>
                <a href="build-pizza.php" class="nav-link">Build Your Pizza</a>
                <a href="track-order.php" class="nav-link">Track Order</a>
                <a href="profile.php" class="nav-link">Profile</a>
                <a href="cart.php" class="nav-link cart-link active">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="cart-main">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-shopping-cart"></i> Your Cart</h1>
                <p>Review your order before checkout</p>
            </div>

            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items">
                    <div class="card">
                        <div class="card-header">
                            <h3>Order Items</h3>
                        </div>
                        <div class="card-body">
                            <div id="cartItemsList">
                                <!-- Cart items will be populated here by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="card">
                        <div class="card-header">
                            <h3>Order Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="summary-line">
                                <span>Subtotal:</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                            <div class="summary-line">
                                <span>GST (10%):</span>
                                <span id="tax">$0.00</span>
                            </div>
                            <div class="summary-line">
                                <span>Delivery Fee:</span>
                                <span id="deliveryFee">$5.50</span>
                            </div>
                            <div class="summary-total">
                                <span>Total:</span>
                                <span id="total">$0.00</span>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button id="checkoutBtn" class="btn btn-primary" onclick="proceedToCheckout()">
                                Proceed to Checkout
                            </button>
                            <button class="btn btn-outline mt-2" onclick="clearCart()">
                                Clear Cart
                            </button>
                        </div>
                    </div>

                    <!-- Store Selection -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h4>Store Selection</h4>
                        </div>
                        <div class="card-body">
                            <div id="selectedStore" class="store-info">
                                <p class="store-status">No store selected</p>
                            </div>
                            <button class="btn btn-secondary mt-2" onclick="selectStore()">
                                Choose Store
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Crust Pizza</h3>
                    <p>Gourmet pizza delivered fresh since 2001</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="menu.php"><i class="fas fa-pizza-slice"></i> Menu</a></li>
                        <li><a href="build-pizza.php"><i class="fas fa-magic"></i> Build Your Pizza</a></li>
                        <li><a href="track-order.php"><i class="fas fa-search"></i> Track Order</a></li>
                        <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-phone"></i> 1300 CRUST (1300 278 787)</p>
                    <p><i class="fas fa-envelope"></i> info@crustpizza.com.au</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Pizza Street, Food City</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 Crust Pizza. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mock cart data for demonstration
        let mockCart = [{
                name: "Margherita Pizza",
                size: "Large",
                price: 18.90,
                quantity: 2,
                customIngredients: ["Extra Cheese", "Basil"],
                specialInstructions: "Extra crispy"
            },
            {
                name: "Pepperoni Supreme",
                size: "Medium",
                price: 22.50,
                quantity: 1,
                customIngredients: [],
                specialInstructions: ""
            }
        ];

        // Utility functions
        function formatCurrency(amount) {
            return '$' + amount.toFixed(2);
        }

        function showAlert(message, type) {
            alert(message); // Simple alert for demo
        }

        // Cart management functions
        function getCart() {
            return JSON.parse(localStorage.getItem('crustPizzaCart')) || mockCart;
        }

        function saveCart(cart) {
            localStorage.setItem('crustPizzaCart', JSON.stringify(cart));
        }

        function updateCartItemQuantity(index, quantity) {
            const cart = getCart();
            if (cart[index]) {
                cart[index].quantity = quantity;
                saveCart(cart);
                updateCartCount();
            }
        }

        function removeFromCart(index) {
            const cart = getCart();
            cart.splice(index, 1);
            saveCart(cart);
            updateCartCount();
        }

        function clearCart() {
            if (confirm('Are you sure you want to clear your cart?')) {
                localStorage.removeItem('crustPizzaCart');
                displayCart();
                updateCartCount();
            }
        }

        function updateCartCount() {
            const cart = getCart();
            const count = cart.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cartCount').textContent = count;
        }

        // Display cart function
        function displayCart() {
            const cartItemsList = document.getElementById('cartItemsList');
            const cart = getCart();

            if (cart.length === 0) {
                cartItemsList.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart empty-cart-icon"></i>
                        <h3>Your cart is empty</h3>
                        <p>Add some delicious pizzas to get started!</p>
                        <a href="menu.php" class="btn btn-primary mt-3">Browse Menu</a>
                    </div>
                `;
                updateOrderSummary(0, 0, 0, 0);
                return;
            }

            let cartHTML = '';
            cart.forEach((item, index) => {
                cartHTML += `
                    <div class="cart-item">
                        <div class="item-info">
                            <h4>${item.name}</h4>
                            ${item.size ? `<p class="item-details">Size: ${item.size}</p>` : ''}
                            ${item.customIngredients && item.customIngredients.length > 0 ? 
                                `<p class="item-details">Custom ingredients: ${item.customIngredients.join(', ')}</p>` : ''}
                            ${item.specialInstructions ? 
                                `<p class="item-details">Instructions: ${item.specialInstructions}</p>` : ''}
                        </div>
                        <div class="item-quantity">
                            <button class="btn btn-outline quantity-btn" onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                            <span class="quantity-display">${item.quantity}</span>
                            <button class="btn btn-outline quantity-btn" onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                        </div>
                        <div class="item-price">
                            ${formatCurrency(item.price * item.quantity)}
                        </div>
                        <button class="btn btn-outline remove-btn" onclick="removeItem(${index})" title="Remove item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            });

            cartItemsList.innerHTML = cartHTML;

            // Update order summary
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.1;
            const deliveryFee = 5.50;
            const total = subtotal + tax + deliveryFee;

            updateOrderSummary(subtotal, tax, deliveryFee, total);
        }

        function updateOrderSummary(subtotal, tax, deliveryFee, total) {
            document.getElementById('subtotal').textContent = formatCurrency(subtotal);
            document.getElementById('tax').textContent = formatCurrency(tax);
            document.getElementById('deliveryFee').textContent = formatCurrency(deliveryFee);
            document.getElementById('total').textContent = formatCurrency(total);

            // Enable/disable checkout button
            const checkoutBtn = document.getElementById('checkoutBtn');
            if (subtotal > 0) {
                checkoutBtn.disabled = false;
                checkoutBtn.textContent = 'Proceed to Checkout';
                checkoutBtn.style.width = '100%';
            } else {
                checkoutBtn.disabled = true;
                checkoutBtn.textContent = 'Cart is Empty';
                checkoutBtn.style.width = '100%';
            }
        }

        function updateQuantity(index, newQuantity) {
            if (newQuantity <= 0) {
                removeItem(index);
            } else {
                updateCartItemQuantity(index, newQuantity);
                displayCart();
            }
        }

        function removeItem(index) {
            removeFromCart(index);
            displayCart();
        }

        function proceedToCheckout() {
            const cart = getCart();
            if (cart.length === 0) {
                showAlert('Your cart is empty', 'warning');
                return;
            }

            // Check if user is logged in (mock check)
            const isLoggedIn = false; // This would come from PHP
            if (!isLoggedIn) {
                if (confirm('You need to log in to place an order. Would you like to log in now?')) {
                    window.location.href = 'login.php';
                }
                return;
            }

            // Proceed to checkout
            window.location.href = 'checkout.php';
        }

        function selectStore() {
            window.location.href = 'index.php#location-selector';
        }

        function displaySelectedStore() {
            const selectedStore = JSON.parse(localStorage.getItem('selectedStore'));
            const storeDiv = document.getElementById('selectedStore');

            if (selectedStore) {
                storeDiv.innerHTML = `
                    <p class="store-name">${selectedStore.name}</p>
                    <p class="store-status">Selected for pickup/delivery</p>
                `;
            }
        }

        // Initialize cart display
        document.addEventListener('DOMContentLoaded', function() {
            displayCart();
            displaySelectedStore();
            updateCartCount();
        });
    </script>

    <style>
        .cart-main {
            margin-top: 80px;
            /* Account for fixed navbar height */
            padding: 2rem 0;
        }

        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-top: 2rem;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-info {
            flex: 1;
        }

        .item-info h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }

        .item-details {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 1rem;
        }

        .quantity-btn {
            padding: 0.25rem 0.5rem;
            min-width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-display {
            min-width: 30px;
            text-align: center;
            font-weight: 600;
        }

        .item-price {
            font-weight: 600;
            margin: 0 1rem;
            color: #ff6b35;
            font-size: 1.1rem;
        }

        .remove-btn {
            padding: 0.5rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            font-size: 1.1rem;
            padding-top: 0.5rem;
            border-top: 2px solid #ddd;
            margin-top: 0.5rem;
        }

        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .empty-cart-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .store-info {
            margin: 0;
        }

        .store-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .store-status {
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }

            .cart-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1.5rem 0;
            }

            .item-quantity,
            .item-price {
                margin: 0;
            }

            .item-quantity {
                align-self: flex-start;
            }

            .remove-btn {
                align-self: flex-end;
            }
        }
    </style>
</body>

</html>