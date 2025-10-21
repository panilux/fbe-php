<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "Testing FBE map...\n\n";

// Test 1: Basic map
echo "1. Testing basic map...\n";
$writer = new WriteBuffer();
$entries = [1 => 100, 2 => 200, 3 => 300];
$writer->writeMapInt32(0, $entries);

echo "   Buffer size: " . $writer->size . "\n";
echo "   Binary: " . bin2hex($writer->data()) . "\n";

$reader = new ReadBuffer($writer->data());
$readEntries = $reader->readMapInt32(0);

assert($entries === $readEntries, "Map mismatch!");
echo "   ✓ Map: ";
foreach ($readEntries as $k => $v) {
    echo "($k => $v) ";
}
echo "\n";

// Test 2: Empty map
echo "\n2. Testing empty map...\n";
$writer2 = new WriteBuffer();
$emptyMap = [];
$writer2->writeMapInt32(0, $emptyMap);

$reader2 = new ReadBuffer($writer2->data());
$readEmpty = $reader2->readMapInt32(0);

assert($emptyMap === $readEmpty, "Empty map mismatch!");
echo "   ✓ Empty map: []\n";

echo "\n✅ All map tests passed!\n";

