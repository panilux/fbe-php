<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "Testing FBE vector...\n\n";

// Test 1: Basic vector
echo "1. Testing basic vector...\n";
$writer = new WriteBuffer();
$values = [10, 20, 30, 40, 50];
$writer->writeVectorInt32(0, $values);

echo "   Buffer size: " . $writer->size . "\n";
echo "   Binary: " . bin2hex($writer->data()) . "\n";

$reader = new ReadBuffer($writer->data());
$readValues = $reader->readVectorInt32(0);

assert($values === $readValues, "Vector mismatch!");
echo "   ✓ Vector: [" . implode(", ", $readValues) . "]\n";

// Test 2: Empty vector
echo "\n2. Testing empty vector...\n";
$writer2 = new WriteBuffer();
$emptyValues = [];
$writer2->writeVectorInt32(0, $emptyValues);

$reader2 = new ReadBuffer($writer2->data());
$readEmpty = $reader2->readVectorInt32(0);

assert($emptyValues === $readEmpty, "Empty vector mismatch!");
echo "   ✓ Empty vector: []\n";

// Test 3: Large vector
echo "\n3. Testing large vector...\n";
$writer3 = new WriteBuffer();
$largeValues = range(0, 999);
$writer3->writeVectorInt32(0, $largeValues);

$reader3 = new ReadBuffer($writer3->data());
$readLarge = $reader3->readVectorInt32(0);

assert($largeValues === $readLarge, "Large vector mismatch!");
echo "   ✓ Large vector: 1000 elements\n";

echo "\n✅ All vector tests passed!\n";

