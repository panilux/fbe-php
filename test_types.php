<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "Testing new FBE types...\n\n";

// Test 1: Timestamp
echo "1. Testing timestamp...\n";
$buffer = new WriteBuffer();
$timestamp = 1729526400000000000; // 2024-10-21 12:00:00 UTC in nanoseconds
$buffer->writeTimestamp(0, $timestamp);

$reader = new ReadBuffer($buffer->data());
$readTimestamp = $reader->readTimestamp(0);
assert($timestamp === $readTimestamp, "Timestamp mismatch!");
echo "   ✓ Timestamp: $timestamp\n";

// Test 2: UUID
echo "\n2. Testing UUID...\n";
$buffer2 = new WriteBuffer();
$uuid = "123e4567-e89b-12d3-a456-426655440000";
$buffer2->writeUuid(0, $uuid);

$reader2 = new ReadBuffer($buffer2->data());
$readUuid = $reader2->readUuid(0);
assert($uuid === $readUuid, "UUID mismatch!");
echo "   ✓ UUID: " . bin2hex($uuid) . "\n";

// Test 3: Bytes
echo "\n3. Testing bytes...\n";
$buffer3 = new WriteBuffer();
$bytes = "Binary data test \x00\xFF\xAB";
$buffer3->writeBytes(0, $bytes);

$reader3 = new ReadBuffer($buffer3->data());
$readBytes = $reader3->readBytes(0);
assert($bytes === $readBytes, "Bytes mismatch!");
echo "   ✓ Bytes length: " . strlen($bytes) . "\n";
echo "   ✓ Bytes hex: " . bin2hex($bytes) . "\n";

// Test 4: Decimal (positive)
echo "\n4. Testing decimal (positive)...\n";
$buffer4 = new WriteBuffer();
// 123456.123456 with scale 6 = 123456123456 as unscaled value
$value = 123456123456;
$scale = 6;
$negative = false;
$buffer4->writeDecimal(0, $value, $scale, $negative);

$reader4 = new ReadBuffer($buffer4->data());
$decimal = $reader4->readDecimal(0);
assert($decimal['scale'] === $scale, "Decimal scale mismatch!");
assert($decimal['negative'] === $negative, "Decimal sign mismatch!");
echo "   ✓ Decimal scale: {$decimal['scale']}\n";
echo "   ✓ Decimal negative: " . ($decimal['negative'] ? 'true' : 'false') . "\n";

// Test 5: Decimal (negative)
echo "\n5. Testing decimal (negative)...\n";
$buffer5 = new WriteBuffer();
// -987654.987654 with scale 3
$value2 = 987654987654;
$scale2 = 3;
$negative2 = true;
$buffer5->writeDecimal(0, $value2, $scale2, $negative2);

$reader5 = new ReadBuffer($buffer5->data());
$decimal2 = $reader5->readDecimal(0);
assert($decimal2['scale'] === $scale2, "Decimal scale mismatch!");
assert($decimal2['negative'] === $negative2, "Decimal sign mismatch!");
echo "   ✓ Decimal scale: {$decimal2['scale']}\n";
echo "   ✓ Decimal negative: " . ($decimal2['negative'] ? 'true' : 'false') . "\n";

echo "\n✅ All type tests passed!\n";

