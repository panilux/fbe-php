<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "Testing FBE array...\n\n";

// Test 1: Basic array
echo "1. Testing basic array...\n";
$writer = new WriteBuffer();
$writer->allocate(12); // 3 × 4 bytes
$values = [10, 20, 30];
$writer->writeArrayInt32(0, $values);

echo "   Buffer size: " . $writer->size() . "\n";
echo "   Binary: " . bin2hex($writer->data()) . "\n";

$reader = new ReadBuffer($writer->data());
$readValues = $reader->readArrayInt32(0, 3);

assert($values === $readValues, "Array mismatch!");
echo "   ✓ Array: [" . implode(", ", $readValues) . "]\n";

// Test 2: Large array
echo "\n2. Testing large array...\n";
$writer2 = new WriteBuffer();
$writer2->allocate(400); // 100 × 4 bytes
$largeValues = range(0, 99);
$writer2->writeArrayInt32(0, $largeValues);

$reader2 = new ReadBuffer($writer2->data());
$readLarge = $reader2->readArrayInt32(0, 100);

assert($largeValues === $readLarge, "Large array mismatch!");
echo "   ✓ Large array: 100 elements\n";

echo "\n✅ All array tests passed!\n";

