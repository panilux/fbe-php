<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/src/FBE/FieldModel.php';
require_once __DIR__ . '/src/FBE/FieldModels.php';
require_once __DIR__ . '/src/FBE/FieldModelString.php';
require_once __DIR__ . '/test/Address.php';
require_once __DIR__ . '/test/AddressModel.php';
require_once __DIR__ . '/test/UserWithAddress.php';
require_once __DIR__ . '/test/UserWithAddressModel.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\Test\Address;
use FBE\Test\UserWithAddress;
use FBE\Test\UserWithAddressModel;

echo "============================================================\n";
echo "Nested Struct Test: PHP\n";
echo "============================================================\n\n";

// Test 1: Write nested struct
echo "TEST 1: PHP Write Nested Struct\n";
echo "------------------------------------------------------------\n";

$buffer = new WriteBuffer();
$buffer->allocate(200);

$address = new Address("Istanbul", "Turkey");
$user = new UserWithAddress(42, "Panilux", $address);

$model = new UserWithAddressModel($buffer, 0);
$model->set($user);

echo "Total written: {$buffer->size} bytes\n";
$binary = substr($buffer->data(), 0, $buffer->size);
echo "Binary (hex): " . bin2hex($binary) . "\n";

// Save for Rust
file_put_contents('/tmp/nested_php.bin', $binary);
echo "✅ Saved to /tmp/nested_php.bin\n\n";

// Test 2: Read nested struct
echo "============================================================\n";
echo "TEST 2: PHP Read Nested Struct\n";
echo "============================================================\n";

$readBuffer = new ReadBuffer($binary);
$readModel = new UserWithAddressModel($readBuffer, 0);
$readUser = $readModel->get();

echo "User ID: {$readUser->id}\n";
echo "User Name: {$readUser->name}\n";
echo "Address City: {$readUser->address->city}\n";
echo "Address Country: {$readUser->address->country}\n";

assert($readUser->id === 42);
assert($readUser->name === "Panilux");
assert($readUser->address->city === "Istanbul");
assert($readUser->address->country === "Turkey");

echo "\n✅ PHP nested struct working!\n";

