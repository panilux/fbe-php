<?php
/**
 * Test struct keys in PHP FBE
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */

declare(strict_types=1);

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/test/gen_keys/Order.php';
require_once __DIR__ . '/test/gen_keys/Balance.php';
require_once __DIR__ . '/test/gen_keys/UserSession.php';
require_once __DIR__ . '/test/gen_keys/LogEntry.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== FBE Struct Keys Test ===\n\n";

// Test 1: Single key field (Order)
echo "Test 1: Single key field (Order)\n";
$order1 = new Order();
$order1->id = 123;
$order1->symbol = "AAPL";
$order1->price = 150.50;

$order2 = new Order();
$order2->id = 123;
$order2->symbol = "GOOGL";  // Different symbol
$order2->price = 200.00;    // Different price

$order3 = new Order();
$order3->id = 456;  // Different id
$order3->symbol = "AAPL";
$order3->price = 150.50;

echo "Order1 key: " . json_encode($order1->getKey()) . "\n";
echo "Order2 key: " . json_encode($order2->getKey()) . "\n";
echo "Order3 key: " . json_encode($order3->getKey()) . "\n";

assert($order1->equals($order2), "Order1 should equal Order2 (same id)");
assert(!$order1->equals($order3), "Order1 should NOT equal Order3 (different id)");
echo "✓ Single key equality test passed\n\n";

// Test 2: String key field (Balance)
echo "Test 2: String key field (Balance)\n";
$balance1 = new Balance();
$balance1->currency = "USD";
$balance1->amount = 1000.00;

$balance2 = new Balance();
$balance2->currency = "USD";
$balance2->amount = 2000.00;  // Different amount

$balance3 = new Balance();
$balance3->currency = "EUR";  // Different currency
$balance3->amount = 1000.00;

echo "Balance1 key: " . json_encode($balance1->getKey()) . "\n";
echo "Balance2 key: " . json_encode($balance2->getKey()) . "\n";
echo "Balance3 key: " . json_encode($balance3->getKey()) . "\n";

assert($balance1->equals($balance2), "Balance1 should equal Balance2 (same currency)");
assert(!$balance1->equals($balance3), "Balance1 should NOT equal Balance3 (different currency)");
echo "✓ String key equality test passed\n\n";

// Test 3: Composite key (UserSession)
echo "Test 3: Composite key (UserSession)\n";
$session1 = new UserSession();
$session1->userId = 100;
$session1->sessionId = "abc123";
$session1->timestamp = 1234567890;
$session1->ipAddress = "192.168.1.1";

$session2 = new UserSession();
$session2->userId = 100;
$session2->sessionId = "abc123";
$session2->timestamp = 9876543210;  // Different timestamp
$session2->ipAddress = "10.0.0.1";  // Different IP

$session3 = new UserSession();
$session3->userId = 100;
$session3->sessionId = "xyz789";  // Different sessionId
$session3->timestamp = 1234567890;
$session3->ipAddress = "192.168.1.1";

$session4 = new UserSession();
$session4->userId = 200;  // Different userId
$session4->sessionId = "abc123";
$session4->timestamp = 1234567890;
$session4->ipAddress = "192.168.1.1";

echo "Session1 key: " . json_encode($session1->getKey()) . "\n";
echo "Session2 key: " . json_encode($session2->getKey()) . "\n";
echo "Session3 key: " . json_encode($session3->getKey()) . "\n";
echo "Session4 key: " . json_encode($session4->getKey()) . "\n";

assert($session1->equals($session2), "Session1 should equal Session2 (same userId+sessionId)");
assert(!$session1->equals($session3), "Session1 should NOT equal Session3 (different sessionId)");
assert(!$session1->equals($session4), "Session1 should NOT equal Session4 (different userId)");
echo "✓ Composite key equality test passed\n\n";

// Test 4: No key fields (LogEntry)
echo "Test 4: No key fields (LogEntry)\n";
$log1 = new LogEntry();
$log1->timestamp = 1234567890;
$log1->message = "Test message";
$log1->level = "INFO";

// LogEntry should not have getKey() or equals() methods
assert(!method_exists($log1, 'getKey'), "LogEntry should NOT have getKey() method");
assert(!method_exists($log1, 'equals'), "LogEntry should NOT have equals() method");
echo "✓ No key fields test passed\n\n";

// Test 5: Hash map usage
echo "Test 5: Hash map usage\n";
$orderMap = [];

$o1 = new Order();
$o1->id = 1;
$o1->symbol = "AAPL";
$o1->price = 100.00;

$o2 = new Order();
$o2->id = 2;
$o2->symbol = "GOOGL";
$o2->price = 200.00;

// Use key as hash map key
$orderMap[json_encode($o1->getKey())] = $o1;
$orderMap[json_encode($o2->getKey())] = $o2;

// Lookup by key
$searchKey = json_encode([1]);
assert(isset($orderMap[$searchKey]), "Should find order with id=1");
assert($orderMap[$searchKey]->symbol === "AAPL", "Found order should be AAPL");

echo "✓ Hash map usage test passed\n\n";

echo "=== All Struct Keys Tests Passed! ===\n";

