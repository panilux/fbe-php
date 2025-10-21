<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/src/FBE/FieldModel.php';
require_once __DIR__ . '/src/FBE/FieldModels.php';
require_once __DIR__ . '/src/FBE/FieldModelString.php';
require_once __DIR__ . '/test/Order.php';
require_once __DIR__ . '/test/OrderModel.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBETest\Order;
use FBETest\OrderModel;

echo "=" . str_repeat("=", 59) . "\n";
echo "THE CHALLENGE: PHP FBE Implementation Test\n";
echo "=" . str_repeat("=", 59) . "\n\n";

// Test 1: PHP Serialization
echo "TEST 1: PHP Serialization\n";
echo str_repeat("-", 60) . "\n";

$order = new Order(
    id: 42,
    symbol: "EURUSD",
    side: 0,  // buy
    type: 0,  // market
    price: 1.23456,
    volume: 1000.0
);

$buffer = new WriteBuffer();
$model = new OrderModel($buffer);
$size = $model->serialize($order);

echo "Serialized size: $size bytes\n";
echo "Buffer size: {$buffer->size} bytes\n";

$binary = $buffer->data();
echo "Binary (hex): " . bin2hex($binary) . "\n";
echo "Binary (first 50 bytes): " . bin2hex(substr($binary, 0, 50)) . "\n";

// Save to file
file_put_contents('/tmp/fbe_order_php.bin', $binary);
echo "\n✅ Saved to /tmp/fbe_order_php.bin\n";

// Test 2: PHP Deserialization
echo "\n" . str_repeat("=", 60) . "\n";
echo "TEST 2: PHP Deserialization (verify)\n";
echo str_repeat("=", 60) . "\n";

$reader = new ReadBuffer($binary);
$readerModel = new OrderModel($reader);
$order2 = $readerModel->deserialize();

echo "Order ID: {$order2->id}\n";
echo "Symbol: {$order2->symbol}\n";
echo "Side: {$order2->side} (0=buy)\n";
echo "Type: {$order2->type} (0=market)\n";
echo "Price: {$order2->price}\n";
echo "Volume: {$order2->volume}\n";

assert($order2->id === 42);
assert($order2->symbol === "EURUSD");
assert($order2->side === 0);

echo "\n✅ PHP FBE working!\n";

