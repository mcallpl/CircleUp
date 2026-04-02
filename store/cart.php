<?php
require_once '../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart — CircleUp</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Barlow+Condensed:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #0a1628;
            --navy-mid: #1a2744;
            --navy-light: #243456;
            --red: #b22234;
            --red-bright: #e8293b;
            --white: #f5f0e8;
            --white-pure: #ffffff;
            --gold: #c9a84c;
            --gold-bright: #ffd700;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Barlow Condensed', sans-serif;
            background: var(--navy);
            color: var(--white);
        }

        .flag-stripe-top {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            z-index: 100;
            background: repeating-linear-gradient(
                90deg,
                var(--red) 0px,
                var(--red) 33.33%,
                var(--white-pure) 33.33%,
                var(--white-pure) 66.66%,
                var(--navy-mid) 66.66%,
                var(--navy-mid) 100%
            );
        }

        header {
            position: fixed;
            top: 6px;
            left: 0;
            right: 0;
            z-index: 1000;
            background: var(--navy-mid);
            border-bottom: 2px solid var(--gold);
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Oswald', sans-serif;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--white-pure);
            text-decoration: none;
        }

        .logo span {
            color: var(--red);
        }

        .header-nav {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .header-nav a {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold);
            text-decoration: none;
            transition: color 0.3s;
        }

        .header-nav a:hover {
            color: var(--white-pure);
        }

        .cart-btn {
            width: 45px;
            height: 45px;
            background: var(--white-pure);
            border: 2px solid var(--gold);
            border-radius: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .cart-btn:hover {
            background: var(--gold);
            box-shadow: 0 0 15px rgba(201, 168, 76, 0.6);
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--gold);
            color: var(--navy);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        .container {
            max-width: 1200px;
            margin: 85px auto 40px;
            padding: 0 60px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        .section-title {
            font-family: 'Oswald', sans-serif;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--gold-bright);
            margin-bottom: 30px;
            border-bottom: 2px solid var(--gold);
            padding-bottom: 15px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid var(--gold);
            align-items: start;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            background: var(--navy-light);
            border-radius: 2px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--gold);
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .cart-item-info h3 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--white-pure);
        }

        .cart-item-info p {
            font-size: 13px;
            color: var(--gold);
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
            border: 1px solid var(--gold);
            background: var(--navy-light);
            color: var(--white);
            cursor: pointer;
            border-radius: 2px;
            font-weight: 700;
            font-size: 12px;
            transition: all 0.2s;
        }

        .quantity-control button:hover {
            background: var(--red);
            border-color: var(--red);
        }

        .quantity-control input {
            width: 32px;
            border: 1px solid var(--gold);
            padding: 4px;
            text-align: center;
            font-size: 12px;
            border-radius: 2px;
            background: var(--navy-light);
            color: var(--white);
        }

        .cart-item-price {
            text-align: right;
        }

        .cart-item-price .price {
            font-size: 18px;
            font-weight: 700;
            color: var(--gold-bright);
            margin-bottom: 8px;
        }

        .remove-btn {
            background: none;
            border: none;
            color: var(--red);
            cursor: pointer;
            font-size: 12px;
            text-decoration: underline;
            transition: color 0.2s;
        }

        .remove-btn:hover {
            color: var(--red-bright);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart h3 {
            font-size: 24px;
            color: var(--gold-bright);
            margin-bottom: 12px;
        }

        .empty-cart p {
            color: var(--gold);
            margin-bottom: 24px;
        }

        .order-summary {
            background: var(--navy-light);
            padding: 24px;
            border-radius: 2px;
            border: 1px solid var(--gold);
            height: fit-content;
            position: sticky;
            top: 90px;
        }

        .order-summary h3 {
            font-family: 'Oswald', sans-serif;
            font-size: 20px;
            margin-bottom: 24px;
            color: var(--gold-bright);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .summary-row.total {
            border-top: 1px solid var(--gold);
            padding-top: 16px;
            margin-top: 16px;
            font-weight: 700;
            font-size: 16px;
        }

        .checkout-btn {
            width: 100%;
            padding: 14px;
            background: var(--red);
            color: var(--white-pure);
            border: 2px solid var(--gold);
            border-radius: 2px;
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            cursor: pointer;
            margin-top: 24px;
            transition: all 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 12px;
        }

        .checkout-btn:hover {
            background: var(--red-bright);
            box-shadow: 0 0 15px rgba(232, 41, 59, 0.5);
        }

        .checkout-btn:disabled {
            background: var(--gold);
            cursor: not-allowed;
            box-shadow: none;
        }

        .continue-shopping {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: var(--gold);
            text-decoration: none;
            font-size: 12px;
            transition: color 0.2s;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .continue-shopping:hover {
            color: var(--white-pure);
        }

        footer {
            background: var(--navy-mid);
            border-top: 2px solid var(--gold);
            padding: 40px 60px 30px;
            text-align: center;
            color: var(--gold);
            font-size: 11px;
            letter-spacing: 1px;
            margin-top: 60px;
        }

        .footer-nav {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 16px;
        }

        .footer-nav a {
            color: var(--gold);
            text-decoration: none;
            font-family: 'Oswald', sans-serif;
            font-weight: 600;
            font-size: 12px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: color 0.3s;
        }

        .footer-nav a:hover {
            color: var(--white-pure);
        }

        .footer-dot {
            width: 4px;
            height: 4px;
            background: var(--gold);
            border-radius: 50%;
            opacity: 0.5;
        }

        footer p {
            color: var(--chrome, #8a8a8a);
            font-size: 11px;
        }

        @media (max-width: 768px) {
            header {
                padding: 15px 30px;
            }

            .container {
                grid-template-columns: 1fr;
                padding: 0 30px;
            }

            .order-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="flag-stripe-top"></div>

    <header>
        <a href="/CircleUp/store/" class="logo">Circle<span>Up</span></a>
        <nav class="header-nav">
            <a href="/CircleUp/store/">Shop</a>
            <a href="/CircleUp/admin/login.php">Admin</a>
            <a href="/CircleUp/store/cart.php" style="position: relative;">
                <div class="cart-btn">🛒<span class="cart-badge"></span></div>
            </a>
        </nav>
    </header>

    <div style="margin-top: 76px;"></div>

    <div class="container">
        <div class="cart-items">
            <h2 class="section-title">Your Cart</h2>
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

    <footer>
        <nav class="footer-nav">
            <a href="/CircleUp/">Home</a>
            <span class="footer-dot"></span>
            <a href="/CircleUp/store/">Shop</a>
            <span class="footer-dot"></span>
            <a href="/CircleUp/store/cart.php">Cart</a>
            <span class="footer-dot"></span>
            <a href="/CircleUp/admin/login.php">Admin</a>
        </nav>
        <p>&copy; 2026 CircleUp — Premium Apparel</p>
    </footer>

    <script src="cart.js"></script>
    <script>
        function renderCart() {
            const cartList = document.getElementById('cart-list');
            
            if (cart.cart.length === 0) {
                cartList.innerHTML = `
                    <div class="empty-cart">
                        <h3>Your cart is empty</h3>
                        <p>Add items to get started</p>
                        <a href="/CircleUp/store/" style="display: inline-block; padding: 11px 24px; background: var(--red); color: var(--white-pure); border: 2px solid var(--gold); border-radius: 2px; font-family: 'Oswald', sans-serif; font-weight: 700; font-size: 11px; cursor: pointer; text-decoration: none; letter-spacing: 1px; text-transform: uppercase; transition: all 0.3s;">Continue Shopping</a>
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

            const email = prompt('Enter your email:');
            const name = prompt('Enter your name:');
            
            if (!email || !name) {
                alert('Email and name are required');
                return;
            }

            fetch('/CircleUp/api/checkout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    items: cart.cart,
                    email: email,
                    name: name
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error, status = ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.checkout_url) {
                        window.location.href = data.checkout_url;
                    } else {
                        alert('Error: ' + (data.error || 'Unknown error'));
                    }
                } catch (e) {
                    alert('Checkout error: Invalid response from server');
                    console.log('Response:', text);
                }
            })
            .catch(error => {
                alert('Checkout error: ' + error.message);
            });
        }

        renderCart();
    </script>
</body>
</html>
