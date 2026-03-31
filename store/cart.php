<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart — CircleUp</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="store.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        .cart-items h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            margin-bottom: 32px;
            color: #1a1a1a;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #e8e8e8;
            align-items: start;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            background: #f5f5f5;
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .cart-item-info h3 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1a1a1a;
        }

        .cart-item-info p {
            font-size: 13px;
            color: #888;
            margin-bottom: 12px;
        }

        .quantity-control {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .quantity-control button {
            width: 24px;
            height: 24px;
            border: 1px solid #e0e0e0;
            background: #fff;
            cursor: pointer;
            border-radius: 2px;
            font-weight: 600;
            font-size: 12px;
        }

        .quantity-control input {
            width: 32px;
            border: 1px solid #e0e0e0;
            padding: 4px;
            text-align: center;
            font-size: 12px;
            border-radius: 2px;
        }

        .cart-item-price {
            text-align: right;
        }

        .cart-item-price .price {
            font-size: 15px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .remove-btn {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 12px;
            text-decoration: underline;
            transition: color 0.2s;
        }

        .remove-btn:hover {
            color: #1a1a1a;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart h3 {
            font-size: 20px;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .empty-cart p {
            color: #888;
            margin-bottom: 24px;
        }

        /* SIDEBAR */
        .order-summary {
            background: #f5f5f5;
            padding: 24px;
            border-radius: 6px;
            height: fit-content;
            position: sticky;
            top: 80px;
        }

        .order-summary h3 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            margin-bottom: 24px;
            color: #1a1a1a;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .summary-row.total {
            border-top: 1px solid #ddd;
            padding-top: 16px;
            margin-top: 16px;
            font-weight: 600;
            font-size: 16px;
        }

        .checkout-btn {
            width: 100%;
            padding: 14px;
            background: #1a1a1a;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 24px;
            transition: background 0.2s;
        }

        .checkout-btn:hover {
            background: #333;
        }

        .checkout-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .continue-shopping {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #666;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }

        .continue-shopping:hover {
            color: #1a1a1a;
        }

        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
                padding: 20px;
            }

            .order-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header>
        <a href="/CircleUp/store/" class="logo">Circle<span>Up</span></a>
        <nav class="header-nav">
            <a href="/CircleUp/store/">Shop</a>
            <a href="/CircleUp/admin/login.php">Admin</a>
            <div class="cart-btn">🛒<span class="cart-badge"></span></div>
        </nav>
    </header>

    <!-- CART -->
    <div style="margin-top: 60px;">
        <div class="cart-container">
            <div class="cart-items">
                <h2>Your Cart</h2>
                <div id="cart-list"></div>
            </div>

            <div class="order-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotal">$0</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span id="total-price">$0</span>
                </div>
                <button class="checkout-btn" id="checkout-btn" onclick="proceedToCheckout()">Proceed to Checkout</button>
                <a href="/CircleUp/store/" class="continue-shopping">Continue Shopping</a>
            </div>
        </div>
    </div>

    <script src="cart.js"></script>
    <script>
        function renderCart() {
            const cartList = document.getElementById('cart-list');
            
            if (cart.cart.length === 0) {
                cartList.innerHTML = `
                    <div class="empty-cart">
                        <h3>Your cart is empty</h3>
                        <p>Add items to get started</p>
                        <a href="/CircleUp/store/" class="cta-button">Continue Shopping</a>
                    </div>
                `;
                document.getElementById('checkout-btn').disabled = true;
                return;
            }

            cartList.innerHTML = cart.cart.map(item => `
                <div class="cart-item">
                    <div class="cart-item-image">
                        ${item.image ? `<img src="${item.image}" alt="${item.name}">` : 'No Image'}
                    </div>
                    <div class="cart-item-info">
                        <h3>${item.name}</h3>
                        <p>${item.category}</p>
                        <div class="quantity-control">
                            <button onclick="cart.updateQuantity(${item.id}, ${item.quantity - 1})">−</button>
                            <input type="number" value="${item.quantity}" min="1" onchange="cart.updateQuantity(${item.id}, this.value)">
                            <button onclick="cart.updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                        </div>
                    </div>
                    <div class="cart-item-price">
                        <div class="price">$${(item.price * item.quantity).toFixed(0)}</div>
                        <button class="remove-btn" onclick="cart.removeProduct(${item.id})">Remove</button>
                    </div>
                </div>
            `).join('');

            updateOrderSummary();
        }

        function updateOrderSummary() {
            const subtotal = cart.getTotal();
            document.getElementById('subtotal').textContent = '$' + Math.round(subtotal);
            document.getElementById('total-price').textContent = '$' + Math.round(subtotal);
        }

        function proceedToCheckout() {
            if (cart.cart.length === 0) {
                alert('Your cart is empty');
                return;
            }

            // Send to Stripe checkout
            fetch('/CircleUp/api/checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    items: cart.cart,
                    email: prompt('Enter your email:'),
                    name: prompt('Enter your name:')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.checkout_url) {
                    window.location.href = data.checkout_url;
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                alert('Checkout error: ' + error);
            });
        }

        // Initial render
        renderCart();
    </script>

    <style>
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #1a1a1a;
            color: #fff;
            padding: 16px 24px;
            border-radius: 4px;
            font-size: 14px;
            z-index: 10000;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</body>
</html>
