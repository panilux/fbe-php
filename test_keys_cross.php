<?php
/**
 * Cross-platform struct keys test: PHP ↔ Rust
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */

declare(strict_types=1);

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/test/gen_keys/Order.php';
require_once __DIR__ . '/test/gen_keys/Balance.php';
require_once __DIR__ . '/test/gen_keys/UserSession.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== PHP ↔ Rust Struct Keys Cross-Platform Test ===\n\n";

// Test 1: PHP writes Order
echo "Test 1: PHP → Rust (Order)\n";
$order = new Order();
$order->id = 123;
$order->symbol = "AAPL";
$order->price = 150.50;

$buffer = new WriteBuffer();
$order->serialize($buffer);
file_put_contents('/tmp/php_order.bin', $buffer->data());
echo "✓ PHP wrote Order\n";
echo "  Key: " . json_encode($order->getKey()) . "\n\n";

// Test 2: Rust writes, PHP reads (Order)
echo "Test 2: Rust → PHP (Order)\n";
if (file_exists('/tmp/rust_order.bin')) {
    $data = file_get_contents('/tmp/rust_order.bin');
    $readBuffer = new ReadBuffer($data);
    $order2 = Order::deserialize($readBuffer);
    
    echo "✓ PHP read Order from Rust\n";
    echo "  Key: " . json_encode($order2->getKey()) . "\n";
    echo "  Symbol: {$order2->symbol}, Price: {$order2->price}\n";
    
    assert($order2->id === 123);
    assert($order2->symbol === "AAPL");
    assert($order2->price === 150.50);
    assert($order->equals($order2), "Orders should be equal (same key)");
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_order.bin\n\n";
}

// Test 3: PHP writes Balance
echo "Test 3: PHP → Rust (Balance)\n";
$balance = new Balance();
$balance->currency = "USD";
$balance->amount = 1000.00;

$buffer = new WriteBuffer();
$balance->serialize($buffer);
file_put_contents('/tmp/php_balance.bin', $buffer->data());
echo "✓ PHP wrote Balance\n";
echo "  Key: " . json_encode($balance->getKey()) . "\n\n";

// Test 4: Rust writes, PHP reads (Balance)
echo "Test 4: Rust → PHP (Balance)\n";
if (file_exists('/tmp/rust_balance.bin')) {
    $data = file_get_contents('/tmp/rust_balance.bin');
    $readBuffer = new ReadBuffer($data);
    $balance2 = Balance::deserialize($readBuffer);
    
    echo "✓ PHP read Balance from Rust\n";
    echo "  Key: " . json_encode($balance2->getKey()) . "\n";
    echo "  Amount: {$balance2->amount}\n";
    
    assert($balance2->currency === "USD");
    assert($balance2->amount === 1000.00);
    assert($balance->equals($balance2), "Balances should be equal (same key)");
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_balance.bin\n\n";
}

// Test 5: PHP writes UserSession
echo "Test 5: PHP → Rust (UserSession)\n";
$session = new UserSession();
$session->userId = 100;
$session->sessionId = "abc123";
$session->timestamp = 1234567890;
$session->ipAddress = "192.168.1.1";

$buffer = new WriteBuffer();
$session->serialize($buffer);
file_put_contents('/tmp/php_session.bin', $buffer->data());
echo "✓ PHP wrote UserSession\n";
echo "  Key: " . json_encode($session->getKey()) . "\n\n";

// Test 6: Rust writes, PHP reads (UserSession)
echo "Test 6: Rust → PHP (UserSession)\n";
if (file_exists('/tmp/rust_session.bin')) {
    $data = file_get_contents('/tmp/rust_session.bin');
    $readBuffer = new ReadBuffer($data);
    $session2 = UserSession::deserialize($readBuffer);
    
    echo "✓ PHP read UserSession from Rust\n";
    echo "  Key: " . json_encode($session2->getKey()) . "\n";
    echo "  Timestamp: {$session2->timestamp}, IP: {$session2->ipAddress}\n";
    
    assert($session2->userId === 100);
    assert($session2->sessionId === "abc123");
    assert($session2->timestamp === 1234567890);
    assert($session->equals($session2), "Sessions should be equal (same key)");
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_session.bin\n\n";
}

echo "=== Cross-Platform Keys Tests Complete ===\n";

