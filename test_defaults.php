<?php
/**
 * Test default values in PHP FBE
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */

declare(strict_types=1);

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/test/gen_defaults/Config.php';
require_once __DIR__ . '/test/gen_defaults/Settings.php';
require_once __DIR__ . '/test/gen_defaults/Order.php';
require_once __DIR__ . '/test/gen_defaults/OptionalFields.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== FBE Default Values Test ===\n\n";

// Test 1: Numeric defaults
echo "Test 1: Numeric defaults (Config)\n";
$config = new Config();
echo "timeout: {$config->timeout} (expected: 30)\n";
echo "retries: {$config->retries} (expected: 3)\n";
echo "threshold: {$config->threshold} (expected: 0.95)\n";
echo "ratio: {$config->ratio} (expected: 1.5)\n";

assert($config->timeout === 30, "timeout should be 30");
assert($config->retries === 3, "retries should be 3");
assert(abs($config->threshold - 0.95) < 0.001, "threshold should be 0.95");
assert(abs($config->ratio - 1.5) < 0.001, "ratio should be 1.5");
echo "✓ Numeric defaults test passed\n\n";

// Test 2: String and boolean defaults
echo "Test 2: String and boolean defaults (Settings)\n";
$settings = new Settings();
echo "enabled: " . ($settings->enabled ? 'true' : 'false') . " (expected: true)\n";
echo "debug: " . ($settings->debug ? 'true' : 'false') . " (expected: false)\n";
echo "name: {$settings->name} (expected: DefaultName)\n";
echo "path: {$settings->path} (expected: /var/log)\n";

assert($settings->enabled === true, "enabled should be true");
assert($settings->debug === false, "debug should be false");
assert($settings->name === "DefaultName", "name should be DefaultName");
assert($settings->path === "/var/log", "path should be /var/log");
echo "✓ String and boolean defaults test passed\n\n";

// Test 3: Mixed defaults
echo "Test 3: Mixed defaults (Order)\n";
$order = new Order();
echo "id: {$order->id} (expected: 0, type default)\n";
echo "symbol: '{$order->symbol}' (expected: '', type default)\n";
echo "price: {$order->price} (expected: 0.0, schema default)\n";
echo "volume: {$order->volume} (expected: 0.0, schema default)\n";
echo "tp: {$order->tp} (expected: 10.0, schema default)\n";
echo "sl: {$order->sl} (expected: -10.0, schema default)\n";

assert($order->id === 0, "id should be 0");
assert($order->symbol === '', "symbol should be empty");
assert($order->price === 0.0, "price should be 0.0");
assert($order->volume === 0.0, "volume should be 0.0");
assert($order->tp === 10.0, "tp should be 10.0");
assert($order->sl === -10.0, "sl should be -10.0");
echo "✓ Mixed defaults test passed\n\n";

// Test 4: Serialization with defaults
echo "Test 4: Serialization with defaults\n";
$config2 = new Config();
$buffer = new WriteBuffer();
$size = $config2->serialize($buffer);
echo "Serialized Config: $size bytes\n";

$readBuffer = new ReadBuffer($buffer->data());
$config3 = Config::deserialize($readBuffer);

assert($config3->timeout === 30, "Deserialized timeout should be 30");
assert($config3->retries === 3, "Deserialized retries should be 3");
assert(abs($config3->threshold - 0.95) < 0.001, "Deserialized threshold should be 0.95");
echo "✓ Serialization with defaults test passed\n\n";

// Test 5: Modify defaults
echo "Test 5: Modify defaults\n";
$order2 = new Order();
$order2->tp = 20.0;  // Override default
$order2->sl = -20.0; // Override default

echo "Modified tp: {$order2->tp} (expected: 20.0)\n";
echo "Modified sl: {$order2->sl} (expected: -20.0)\n";

assert($order2->tp === 20.0, "Modified tp should be 20.0");
assert($order2->sl === -20.0, "Modified sl should be -20.0");
echo "✓ Modify defaults test passed\n\n";

echo "=== All Default Values Tests Passed! ===\n";

