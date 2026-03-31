<?php
require_once '../config.php';

$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    header('Location: /CircleUp/store/');
    exit();
}

try {
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    
    $db = getDB();
    $stmt = $db->prepare("UPDATE orders SET status = 'completed' WHERE stripe_payment_intent_id = ?");
    $stmt->bind_param("s", $session->payment_intent);
    $stmt->execute();
    
    $order_email = $session->customer_email;
    $order_amount = $session->amount_total / 100;
    
} catch (Exception $e) {
    $order_email = 'unknown';
    $order_amount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed — CircleUp</title>
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
            background: var(--navy-mid);
            border-bottom: 2px solid var(--gold);
            padding: 20px 40px;
            margin-top: 6px;
            text-align: center;
        }

        .logo {
            font-family: 'Oswald', sans-serif;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            text-decoration: none;
            color: var(--white-pure);
        }

        .logo span {
            color: var(--red);
        }

        .success-container {
            max-width: 600px;
            margin: 80px auto;
            padding: 60px 40px;
            background: var(--navy-light);
            border: 1px solid var(--gold);
            border-radius: 2px;
            text-align: center;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.4);
        }

        .checkmark {
            width: 80px;
            height: 80px;
            background: var(--gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
        }

        h1 {
            font-family: 'Oswald', sans-serif;
            font-size: 36px;
            margin-bottom: 12px;
            color: var(--gold-bright);
        }

        .subtitle {
            font-size: 16px;
            color: var(--gold);
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .order-details {
            background: var(--navy-mid);
            padding: 24px;
            border-radius: 2px;
            margin-bottom: 32px;
            text-align: left;
            border: 1px solid var(--gold);
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid var(--gold);
            padding-top: 16px;
            font-weight: 700;
            font-size: 16px;
        }

        .action-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .btn {
            padding: 13px 28px;
            border: 2px solid var(--gold);
            background: transparent;
            color: var(--gold);
            border-radius: 2px;
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn.primary {
            background: var(--red);
            color: var(--white-pure);
            border-color: var(--red);
        }

        .btn:hover {
            background: var(--gold);
            color: var(--navy);
            border-color: var(--gold);
        }

        .btn.primary:hover {
            background: var(--red-bright);
            box-shadow: 0 0 15px rgba(232, 41, 59, 0.5);
        }

        footer {
            background: var(--navy-mid);
            border-top: 2px solid var(--gold);
            padding: 40px 60px;
            text-align: center;
            color: var(--gold);
            font-size: 11px;
            letter-spacing: 1px;
            margin-top: 60px;
        }

        footer a {
            color: var(--gold);
            text-decoration: none;
            font-weight: 700;
        }

        @media (max-width: 600px) {
            .success-container {
                margin: 40px 20px;
                padding: 40px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="flag-stripe-top"></div>

    <header>
        <a href="/CircleUp/store/" class="logo">Circle<span>Up</span></a>
    </header>

    <div class="success-container">
        <div class="checkmark">✓</div>
        <h1>Order Confirmed</h1>
        <p class="subtitle">Thank you for your purchase. Your order has been received and will be shipped soon.</p>

        <div class="order-details">
            <div class="detail-row">
                <span>Email</span>
                <span><?php echo htmlspecialchars($order_email); ?></span>
            </div>
            <div class="detail-row">
                <span>Amount Paid</span>
                <span>$<?php echo number_format($order_amount, 2); ?></span>
            </div>
        </div>

        <div class="action-buttons">
            <a href="/CircleUp/store/" class="btn primary">Continue Shopping</a>
            <a href="/CircleUp/" class="btn">Go Home</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 <a href="#">CircleUp</a> — Premium Apparel | <a href="/CircleUp/admin/login.php">Admin</a></p>
    </footer>
</body>
</html>
