<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== PHP Cross-Platform Collections Test ===\n\n";

$writer = new WriteBuffer();

// Allocate space for 4 collection pointers (vector, map, set) + array inline
$writer->allocate(16);

// 1. Vector
$vectorValues = [10, 20, 30];
$writer->writeVectorInt32(0, $vectorValues);

// 2. Array (inline, no pointer)
$writer->allocate(12); // 3 × 4 bytes
$arrayValues = [40, 50, 60];
$writer->writeArrayInt32(16, $arrayValues);

// 3. Map
$mapEntries = [1 => 100, 2 => 200];
$writer->writeMapInt32(4, $mapEntries);

// 4. Set
$setValues = [70, 80, 90];
$writer->writeSetInt32(8, $setValues);

file_put_contents('/tmp/php_collections.bin', $writer->data());
echo "PHP wrote " . $writer->size . " bytes\n";

// Read back
$reader = new ReadBuffer($writer->data());

$readVector = $reader->readVectorInt32(0);
$readArray = $reader->readArrayInt32(16, 3);
$readMap = $reader->readMapInt32(4);
$readSet = $reader->readSetInt32(8);

assert($vectorValues === $readVector, "Vector mismatch!");
assert($arrayValues === $readArray, "Array mismatch!");
assert($mapEntries === $readMap, "Map mismatch!");
assert($setValues === $readSet, "Set mismatch!");

echo "✓ PHP round-trip passed\n";
echo "  Vector: [" . implode(", ", $readVector) . "]\n";
echo "  Array: [" . implode(", ", $readArray) . "]\n";
echo "  Map: ";
foreach ($readMap as $k => $v) {
    echo "($k => $v) ";
}
echo "\n";
echo "  Set: [" . implode(", ", $readSet) . "]\n";

// Try reading Rust binary if exists
if (file_exists('/tmp/rust_collections.bin')) {
    echo "\nReading Rust binary...\n";
    $rustBinary = file_get_contents('/tmp/rust_collections.bin');
    
    $rustReader = new ReadBuffer($rustBinary);
    
    $rustVector = $rustReader->readVectorInt32(0);
    $rustArray = $rustReader->readArrayInt32(16, 3);
    $rustMap = $rustReader->readMapInt32(4);
    $rustSet = $rustReader->readSetInt32(8);
    
    echo "Rust→PHP Vector: [" . implode(", ", $rustVector) . "]\n";
    echo "Rust→PHP Array: [" . implode(", ", $rustArray) . "]\n";
    echo "Rust→PHP Map: ";
    foreach ($rustMap as $k => $v) {
        echo "($k => $v) ";
    }
    echo "\n";
    echo "Rust→PHP Set: [" . implode(", ", $rustSet) . "]\n";
    
    assert($vectorValues === $rustVector, "Rust vector mismatch!");
    assert($arrayValues === $rustArray, "Rust array mismatch!");
    assert($mapEntries === $rustMap, "Rust map mismatch!");
    assert($setValues === $rustSet, "Rust set mismatch!");
    
    echo "✓ Cross-platform collections test passed!\n";
}

