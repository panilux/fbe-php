<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=" . str_repeat("=", 59) . "\n";
echo "Optional Types Test: PHP\n";
echo "=" . str_repeat("=", 59) . "\n\n";

// Test 1: Write optional values
echo "TEST 1: PHP Write Optional Values\n";
echo str_repeat("-", 60) . "\n";

$buffer = new WriteBuffer();
$buffer->allocate(100);

$offset = 0;

// Optional int32 with value
$buffer->writeOptionalInt32($offset, 42);
$offset += 5 + 4;  // 1 byte flag + 4 bytes pointer + 4 bytes value

// Optional string with value
$buffer->writeOptionalString($offset, "EURUSD");
$offset1 = $offset;
$offset += 5 + 4 + 6;  // 1 byte flag + 4 bytes pointer + 4 bytes len + 6 bytes data

// Optional double NULL
$buffer->writeOptionalDouble($offset, null);
$offset += 5;  // 1 byte flag only

// Optional int32 NULL
$buffer->writeOptionalInt32($offset, null);
$offset += 5;  // 1 byte flag only

echo "Total written: {$buffer->size} bytes\n";
echo "Binary (hex): " . bin2hex(substr($buffer->data(), 0, $buffer->size)) . "\n";

// Save for Rust
file_put_contents('/tmp/optional_php.bin', substr($buffer->data(), 0, $buffer->size));
echo "✅ Saved to /tmp/optional_php.bin\n";

// Test 2: Read optional values
echo "\n" . str_repeat("=", 60) . "\n";
echo "TEST 2: PHP Read Optional Values\n";
echo str_repeat("=", 60) . "\n";

$reader = new ReadBuffer($buffer->data());
$offset = 0;

$val1 = $reader->readOptionalInt32($offset);
$offset += 5 + 4;

$val2 = $reader->readOptionalString($offset1);
$offset += 5 + 4 + 6;

$val3 = $reader->readOptionalDouble($offset);
$offset += 5;

$val4 = $reader->readOptionalInt32($offset);

echo "Optional int32: " . ($val1 ?? "NULL") . "\n";
echo "Optional string: " . ($val2 ?? "NULL") . "\n";
echo "Optional double: " . ($val3 ?? "NULL") . "\n";
echo "Optional int32 (null): " . ($val4 ?? "NULL") . "\n";

assert($val1 === 42);
assert($val2 === "EURUSD");
assert($val3 === null);
assert($val4 === null);

echo "\n✅ PHP optional types working!\n";

