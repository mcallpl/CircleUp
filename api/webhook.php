<?php
require_once '../config.php';

header('Content-Type: application/json');

// Get Stripe event
$payload = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sig_header,
        STRIPE_WEBHOOK_SECRET
    );
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit();
}

$db = getDB();

switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        
        // Find and update order
        $stmt = $db->prepare("UPDATE orders SET status = 'completed' WHERE stripe_payment_intent_id = ?");
        $stmt->bind_param("s", $session->payment_intent);
        $stmt->execute();
        
        // TODO: Send confirmation email
        // TODO: Sync inventory
        
        break;
        
    case 'charge.refunded':
        $charge = $event->data->object;
        
        // Find order and update status
        $stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE stripe_payment_intent_id = ?");
        $stmt->bind_param("s", $charge->payment_intent);
        $stmt->execute();
        
        break;
}

http_response_code(200);
echo json_encode(['success' => true]);
