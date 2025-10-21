<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== PHP Collections Debug ===\n\n";

$writer = new WriteBuffer();

// Test each collection separately
echo "1. Testing vector...\n";
$writer->allocate(4);
$vectorValues = [10, 20, 30];
$writer->writeVectorInt32(0, $vectorValues);

echo "   Buffer size after vector: " . $writer->size . "\n";
echo "   Binary: " . bin2hex($writer->data()) . "\n";

$reader = new ReadBuffer($writer->data());
$readVector = $reader->readVectorInt32(0);
echo "   Read vector: [" . implode(", ", $readVector) . "]\n";
assert($vectorValues === $readVector);
echo "   ✓ Vector OK\n\n";

// Test array
echo "2. Testing array...\n";
$writer2 = new WriteBuffer();
$writer2->allocate(12);
$arrayValues = [40, 50, 60];
$writer2->writeArrayInt32(0, $arrayValues);

echo "   Buffer size: " . $writer2->size . "\n";
echo "   Binary: " . bin2hex($writer2->data()) . "\n";

$reader2 = new ReadBuffer($writer2->data());
$readArray = $reader2->readArrayInt32(0, 3);
echo "   Read array: [" . implode(", ", $readArray) . "]\n";
assert($arrayValues === $readArray);
echo "   ✓ Array OK\n\n";

// Test map
echo "3. Testing map...\n";
$writer3 = new WriteBuffer();
$writer3->allocate(4);
$mapEntries = [1 => 100, 2 => 200];
$writer3->writeMapInt32(0, $mapEntries);

echo "   Buffer size: " . $writer3->size . "\n";
echo "   Binary: " . bin2hex($writer3->data()) . "\n";

$reader3 = new ReadBuffer($writer3->data());
$readMap = $reader3->readMapInt32(0);
echo "   Read map: ";
foreach ($readMap as $k => $v) {
    echo "($k => $v) ";
}
echo "\n";
assert($mapEntries === $readMap);
echo "   ✓ Map OK\n\n";

// Test set
echo "4. Testing set...\n";
$writer4 = new WriteBuffer();
$writer4->allocate(4);
$setValues = [70, 80, 90];
$writer4->writeSetInt32(0, $setValues);

echo "   Buffer size: " . $writer4->size . "\n";
echo "   Binary: " . bin2hex($writer4->data()) . "\n";

$reader4 = new ReadBuffer($writer4->data());
$readSet = $reader4->readSetInt32(0);
echo "   Read set: [" . implode(", ", $readSet) . "]\n";
assert($setValues === $readSet);
echo "   ✓ Set OK\n\n";

echo "✅ All individual tests passed!\n";

