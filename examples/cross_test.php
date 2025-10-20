<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/FBE/WriteBuffer.php';
require_once __DIR__ . '/../src/FBE/ReadBuffer.php';
require_once __DIR__ . '/../test/Side.php';
require_once __DIR__ . '/../test/User.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== PHP FBE Cross-Platform Test ===\n\n";

// Create test data
$user = new User();
$user->id = 100;
$user->name = "Panilux";
$user->side = Side::Buy;

printf("Original: id=%d, name=%s, side=%s\n", $user->id, $user->name, $user->side->name);

// Serialize
$buffer = new WriteBuffer();
$size = $user->serialize($buffer);

printf("Serialized %d bytes\n", $size);
printf("Binary: %s\n", bin2hex($buffer->data()));

// Write to file for Rust to read
file_put_contents('/tmp/php_to_rust.bin', $buffer->data());
echo "\n✓ Wrote binary to /tmp/php_to_rust.bin\n";

// Deserialize
$readBuffer = new ReadBuffer($buffer->data());
$decoded = User::deserialize($readBuffer);

printf("\nDecoded: id=%d, name=%s, side=%s\n", $decoded->id, $decoded->name, $decoded->side->name);

assert($user->id === $decoded->id);
assert($user->name === $decoded->name);

echo "\n✓ PHP round-trip test passed!\n";

// Try reading Rust binary if exists
if (file_exists('/tmp/rust_to_php.bin')) {
    echo "\n=== Reading Rust Binary ===\n";
    $rustBinary = file_get_contents('/tmp/rust_to_php.bin');
    $rustBuffer = new ReadBuffer($rustBinary);
    $rustUser = User::deserialize($rustBuffer);
    
    printf("Rust→PHP: id=%d, name=%s, side=%s\n", $rustUser->id, $rustUser->name, $rustUser->side->name);
    echo "✓ Successfully read Rust binary!\n";
}

