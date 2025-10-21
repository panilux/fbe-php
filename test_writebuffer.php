<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';

use FBE\WriteBuffer;

echo "Testing WriteBuffer fix...\n\n";

// Test 1: Basic size tracking
$buffer = new WriteBuffer();
echo "Initial size: " . $buffer->size . "\n";

$buffer->writeInt32(0, 42);
echo "After writeInt32(0, 42): size = " . $buffer->size . " (expected: 4)\n";

$buffer->writeString(4, "Hello");
echo "After writeString(4, 'Hello'): size = " . $buffer->size . " (expected: 13)\n";
// 4 bytes (int32) + 4 bytes (string length) + 5 bytes (string data) = 13

// Test 2: Verify binary data
$data = $buffer->data();
echo "\nBinary data length: " . strlen($data) . "\n";
echo "Binary data (hex): " . bin2hex($data) . "\n";

// Expected:
// 2a000000 = 42 as little-endian int32
// 05000000 = 5 as little-endian int32 (string length)
// 48656c6c6f = "Hello" in ASCII

echo "\nExpected hex: 2a00000005000000" . bin2hex("Hello") . "\n";

// Test 3: Multiple writes
$buffer2 = new WriteBuffer();
$buffer2->writeBool(0, true);
$buffer2->writeInt16(1, 1000);
$buffer2->writeDouble(3, 3.14159);

echo "\nBuffer2 size: " . $buffer2->size . " (expected: 11)\n";
// 1 (bool) + 2 (int16) + 8 (double) = 11

echo "Buffer2 hex: " . bin2hex($buffer2->data()) . "\n";

echo "\n✅ WriteBuffer fix test completed!\n";

