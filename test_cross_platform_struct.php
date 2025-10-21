<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/src/FBE/FieldModel.php';
require_once __DIR__ . '/src/FBE/FieldModelInt32.php';
require_once __DIR__ . '/src/FBE/FieldModelString.php';
require_once __DIR__ . '/test/Side.php';
require_once __DIR__ . '/test/UserData.php';
require_once __DIR__ . '/test/UserModel.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\Test\User;
use FBE\Test\UserModel;
use FBE\Test\Side;

echo "Testing cross-platform struct serialization...\n\n";

// Test 1: PHP → Rust
echo "1. PHP → Rust\n";
$user = new User(100, "Panilux", Side::Buy);

$buffer = new WriteBuffer();
$model = new UserModel($buffer);
$model->serialize($user, $buffer);

$phpBinary = bin2hex($buffer->data());
echo "   PHP binary: {$phpBinary}\n";

// Write to file for Rust to read
file_put_contents('/tmp/php_struct_to_rust.bin', $buffer->data());
echo "   Saved to /tmp/php_struct_to_rust.bin\n";

// Test 2: Read Rust binary (if exists)
echo "\n2. Rust → PHP\n";
if (file_exists('/tmp/rust_struct_to_php.bin')) {
    $rustData = file_get_contents('/tmp/rust_struct_to_php.bin');
    echo "   Rust binary: " . bin2hex($rustData) . "\n";
    
    $reader = new ReadBuffer($rustData);
    $model2 = new UserModel($reader);
    $user2 = $model2->deserialize($reader);
    
    echo "   Deserialized from Rust:\n";
    echo "     id: {$user2->id}\n";
    echo "     name: {$user2->name}\n";
    echo "     side: {$user2->side->name}\n";
    
    echo "   ✅ Rust → PHP successful!\n";
} else {
    echo "   ⏳ Waiting for Rust binary...\n";
}

echo "\n✅ Cross-platform struct test completed!\n";

