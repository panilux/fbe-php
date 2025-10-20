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
$uuid = "\x12\x3e\x45\x67\xe8\x9b\x12\xd3\xa4\x56\x42\x66\x55\x44\x00\x00";
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
$value = str_pad(pack('P', 123456123456), 12, "\x00");
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
// -999.99 with scale 2 = 99999 as unscaled value
$value2 = str_pad(pack('P', 99999), 12, "\x00");
$scale2 = 2;
$negative2 = true;
$buffer5->writeDecimal(0, $value2, $scale2, $negative2);

$reader5 = new ReadBuffer($buffer5->data());
$decimal2 = $reader5->readDecimal(0);
assert($decimal2['scale'] === $scale2, "Decimal scale mismatch!");
assert($decimal2['negative'] === $negative2, "Decimal sign mismatch!");
echo "   ✓ Decimal scale: {$decimal2['scale']}\n";
echo "   ✓ Decimal negative: " . ($decimal2['negative'] ? 'true' : 'false') . "\n";

echo "\n✅ All type tests passed!\n";

