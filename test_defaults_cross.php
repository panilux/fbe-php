<?php
/**
 * Cross-platform default values test: PHP ↔ Rust
 */

declare(strict_types=1);

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/test/gen_defaults/Config.php';
require_once __DIR__ . '/test/gen_defaults/Settings.php';
require_once __DIR__ . '/test/gen_defaults/Order.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== PHP ↔ Rust Default Values Cross-Platform Test ===\n\n";

// Test 1: PHP writes Config with defaults
echo "Test 1: PHP → Rust (Config)\n";
$config = new Config();  // Uses defaults
$buffer = new WriteBuffer();
$config->serialize($buffer);
file_put_contents('/tmp/php_config.bin', $buffer->data());
echo "✓ PHP wrote Config with defaults\n";
echo "  timeout={$config->timeout}, retries={$config->retries}, threshold={$config->threshold}\n\n";

// Test 2: Rust writes, PHP reads (Config)
echo "Test 2: Rust → PHP (Config)\n";
if (file_exists('/tmp/rust_config.bin')) {
    $data = file_get_contents('/tmp/rust_config.bin');
    $readBuffer = new ReadBuffer($data);
    $config2 = Config::deserialize($readBuffer);

    echo "✓ PHP read Config from Rust\n";
    echo "  timeout={$config2->timeout}, retries={$config2->retries}, threshold={$config2->threshold}\n";

    assert($config2->timeout === 30);
    assert($config2->retries === 3);
    assert(abs($config2->threshold - 0.95) < 0.001);
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_config.bin\n\n";
}

// Test 3: PHP writes Settings with defaults
echo "Test 3: PHP → Rust (Settings)\n";
$settings = new Settings();  // Uses defaults
$buffer = new WriteBuffer();
$settings->serialize($buffer);
file_put_contents('/tmp/php_settings.bin', $buffer->data());
echo "✓ PHP wrote Settings with defaults\n";
echo "  enabled={$settings->enabled}, name={$settings->name}, path={$settings->path}\n\n";

// Test 4: Rust writes, PHP reads (Settings)
echo "Test 4: Rust → PHP (Settings)\n";
if (file_exists('/tmp/rust_settings.bin')) {
    $data = file_get_contents('/tmp/rust_settings.bin');
    $readBuffer = new ReadBuffer($data);
    $settings2 = Settings::deserialize($readBuffer);

    echo "✓ PHP read Settings from Rust\n";
    echo "  enabled={$settings2->enabled}, name={$settings2->name}, path={$settings2->path}\n";

    assert($settings2->enabled === true);
    assert($settings2->debug === false);
    assert($settings2->name === "DefaultName");
    assert($settings2->path === "/var/log");
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_settings.bin\n\n";
}

// Test 5: PHP writes Order with defaults
echo "Test 5: PHP → Rust (Order)\n";
$order = new Order();  // Uses defaults
$buffer = new WriteBuffer();
$order->serialize($buffer);
file_put_contents('/tmp/php_order_defaults.bin', $buffer->data());
echo "✓ PHP wrote Order with defaults\n";
echo "  tp={$order->tp}, sl={$order->sl}\n\n";

// Test 6: Rust writes, PHP reads (Order)
echo "Test 6: Rust → PHP (Order)\n";
if (file_exists('/tmp/rust_order_defaults.bin')) {
    $data = file_get_contents('/tmp/rust_order_defaults.bin');
    $readBuffer = new ReadBuffer($data);
    $order2 = Order::deserialize($readBuffer);

    echo "✓ PHP read Order from Rust\n";
    echo "  tp={$order2->tp}, sl={$order2->sl}\n";

    assert($order2->tp === 10.0);
    assert($order2->sl === -10.0);
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_order_defaults.bin\n\n";
}

echo "=== Cross-Platform Default Values Tests Complete ===\n";

