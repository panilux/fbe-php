<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

// Manually load generated files (not in autoloader)
$files = glob(__DIR__ . '/test_generated/*.php');
foreach ($files as $file) {
    require_once $file;
}

use FBE\Common\{WriteBuffer, ReadBuffer};
use Com\Example\Trading\{OrderSide, OrderType, OrderFlags, AccountModel, OrderModel};

echo "Testing generated code...\n\n";

// Test 1: Enum usage
echo "1. Testing enums:\n";
echo "   OrderSide::Buy = " . OrderSide::Buy->value . "\n";
echo "   OrderSide::Sell = " . OrderSide::Sell->value . "\n";
echo "   OrderType::Market = " . OrderType::Market->value . "\n";
assert(OrderSide::Buy->value === 0, "OrderSide::Buy should be 0");
assert(OrderSide::Sell->value === 1, "OrderSide::Sell should be 1");
echo "   ✓ Enums work correctly\n\n";

// Test 2: Flags usage
echo "2. Testing flags:\n";
$flags = OrderFlags::IOC | OrderFlags::HIDDEN;
echo "   Combined flags: $flags\n";
assert(OrderFlags::hasFlag($flags, OrderFlags::IOC), "Should have IOC flag");
assert(OrderFlags::hasFlag($flags, OrderFlags::HIDDEN), "Should have HIDDEN flag");
assert(!OrderFlags::hasFlag($flags, OrderFlags::GTC), "Should not have GTC flag");
echo "   ✓ Flags work correctly\n\n";

// Test 3: AccountModel Standard format
echo "3. Testing AccountModel (Standard format):\n";
$buffer = new WriteBuffer();
$buffer->allocate(256);

$account = new AccountModel($buffer, 0);
echo "   Created AccountModel\n";

// Access field models
$idField = $account->id();
$usernameField = $account->username();
$balanceField = $account->balance();

echo "   ✓ All field accessors work\n";
echo "   Field types:\n";
echo "   - id: " . get_class($idField) . "\n";
echo "   - username: " . get_class($usernameField) . "\n";
echo "   - balance: " . get_class($balanceField) . "\n\n";

// Test 4: OrderModel with enum fields
echo "4. Testing OrderModel with enum fields:\n";
$orderBuffer = new WriteBuffer();
$orderBuffer->allocate(512);

$order = new OrderModel($orderBuffer, 0);
$sideField = $order->side();
$typeField = $order->type();

echo "   ✓ Enum field accessors work\n";
echo "   Field types:\n";
echo "   - side: " . get_class($sideField) . " (should be FieldModelInt32)\n";
echo "   - type: " . get_class($typeField) . " (should be FieldModelInt32)\n\n";

assert(get_class($sideField) === 'FBE\Standard\FieldModelInt32', "Side field should be FieldModelInt32");
assert(get_class($typeField) === 'FBE\Standard\FieldModelInt32', "Type field should be FieldModelInt32");

echo "✓ All generator tests passed!\n";
echo "\nGenerator is working correctly and produces valid, usable code.\n";
