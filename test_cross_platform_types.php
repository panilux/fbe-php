<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== PHP Cross-Platform Types Test ===\n\n";

// Write PHP binary
$writer = new WriteBuffer();

// Timestamp
$timestamp = 1729526400000000000;
$writer->writeTimestamp(0, $timestamp);

// UUID
$uuid = "\x12\x3e\x45\x67\xe8\x9b\x12\xd3\xa4\x56\x42\x66\x55\x44\x00\x00";
$writer->writeUuid(8, $uuid);

// Bytes
$bytes = "Binary\x00\xFF";
$writer->writeBytes(24, $bytes);

// Decimal
$decimalValue = str_pad(pack('P', 123456123456), 12, "\x00");
$decimalScale = 6;
$decimalNegative = false;
$writer->writeDecimal(24 + 4 + strlen($bytes), $decimalValue, $decimalScale, $decimalNegative);

// Save to file
file_put_contents('/tmp/php_types.bin', $writer->data());
echo "PHP wrote " . $writer->size . " bytes to /tmp/php_types.bin\n";

// Read back
$reader = new ReadBuffer($writer->data());

$readTimestamp = $reader->readTimestamp(0);
$readUuid = $reader->readUuid(8);
$readBytes = $reader->readBytes(24);
$readDecimal = $reader->readDecimal(24 + 4 + strlen($readBytes));

assert($readTimestamp === $timestamp, "Timestamp mismatch!");
assert($readUuid === $uuid, "UUID mismatch!");
assert($readBytes === $bytes, "Bytes mismatch!");
assert($readDecimal['scale'] === $decimalScale, "Decimal scale mismatch!");
assert($readDecimal['negative'] === $decimalNegative, "Decimal sign mismatch!");

echo "✓ PHP round-trip passed\n";

// Try reading Rust binary if exists
if (file_exists('/tmp/rust_types.bin')) {
    echo "\nReading Rust binary...\n";
    $rustBinary = file_get_contents('/tmp/rust_types.bin');
    $rustReader = new ReadBuffer($rustBinary);
    
    $rustTimestamp = $rustReader->readTimestamp(0);
    $rustUuid = $rustReader->readUuid(8);
    $rustBytes = $rustReader->readBytes(24);
    $rustDecimal = $rustReader->readDecimal(24 + 4 + strlen($rustBytes));
    
    echo "Rust→PHP timestamp: $rustTimestamp\n";
    echo "Rust→PHP UUID: " . bin2hex($rustUuid) . "\n";
    echo "Rust→PHP bytes length: " . strlen($rustBytes) . "\n";
    echo "Rust→PHP decimal: scale={$rustDecimal['scale']}, negative=" . ($rustDecimal['negative'] ? 'true' : 'false') . "\n";
    
    assert($rustTimestamp === $timestamp, "Rust timestamp mismatch!");
    assert($rustUuid === $uuid, "Rust UUID mismatch!");
    assert($rustBytes === $bytes, "Rust bytes mismatch!");
    assert($rustDecimal['scale'] === $decimalScale, "Rust decimal scale mismatch!");
    assert($rustDecimal['negative'] === $decimalNegative, "Rust decimal sign mismatch!");
    
    echo "✓ Cross-platform types test passed!\n";
}

