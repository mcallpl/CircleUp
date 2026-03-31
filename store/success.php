<?php
require_once '../config.php';

$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    header('Location: /CircleUp/store/');
    exit();
}

// Get session from Stripe
try {
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    
    // Update order status
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: #fafafa;
            color: #1a1a1a;
        }

        header {
            background: #fff;
            border-bottom: 1px solid #e8e8e8;
            padding: 16px 40px;
            text-align: center;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 600;
            text-decoration: none;
            color: #1a1a1a;
        }

        .logo span {
            color: #d4a574;
        }

        .success-container {
            max-width: 600px;
            margin: 80px auto;
            padding: 60px 40px;
            background: #fff;
            border-radius: 6px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .checkmark {
            width: 80px;
            height: 80px;
            background: #d4a574;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 40px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            margin-bottom: 12px;
        }

        .subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 32px;
            line-height: 1.6;
        }

        .order-details {
            background: #f5f5f5;
            padding: 24px;
            border-radius: 4px;
            margin-bottom: 32px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid #e0e0e0;
            padding-top: 16px;
            font-weight: 600;
            font-size: 16px;
        }

        .action-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .btn {
            padding: 13px 28px;
            border: 1px solid #e0e0e0;
            background: #fff;
            color: #1a1a1a;
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn.primary {
            background: #1a1a1a;
            color: #fff;
            border-color: #1a1a1a;
        }

        .btn:hover {
            background: #333;
            border-color: #333;
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
            <a href="/" class="btn">Go Home</a>
        </div>
    </div>
</body>
</html>
