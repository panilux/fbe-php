<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== PHP Cross-Platform Vector Test ===\n\n";

// Write PHP binary
$writer = new WriteBuffer();
$values = [100, 200, 300, 400, 500];
$writer->writeVectorInt32(0, $values);

file_put_contents('/tmp/php_vector.bin', $writer->data());
echo "PHP wrote " . $writer->size() . " bytes\n";
echo "PHP binary: " . bin2hex($writer->data()) . "\n";

// Read back
$reader = new ReadBuffer($writer->data());
$readValues = $reader->readVectorInt32(0);

assert($values === $readValues, "PHP round-trip failed!");
echo "✓ PHP round-trip: [" . implode(", ", $readValues) . "]\n";

// Try reading Rust binary if exists
if (file_exists('/tmp/rust_vector.bin')) {
    echo "\nReading Rust binary...\n";
    $rustBinary = file_get_contents('/tmp/rust_vector.bin');
    echo "Rust binary: " . bin2hex($rustBinary) . "\n";
    
    $rustReader = new ReadBuffer($rustBinary);
    $rustValues = $rustReader->readVectorInt32(0);
    
    echo "Rust→PHP vector: [" . implode(", ", $rustValues) . "]\n";
    
    assert($values === $rustValues, "Rust→PHP mismatch!");
    
    echo "✓ Cross-platform vector test passed!\n";
}

