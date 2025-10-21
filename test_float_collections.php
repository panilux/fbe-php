<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "Testing Float/Double Collections...\n\n";

// Test 1: VectorFloat
echo "1. VectorFloat\n";
$buffer = new WriteBuffer();
$buffer->allocate(100);
$buffer->writeVectorFloat(0, [1.5, 2.5, 3.14159]);
$reader = new ReadBuffer($buffer->data());
$values = $reader->readVectorFloat(0);
echo "   Values: [" . implode(", ", $values) . "]\n";
assert(abs($values[0] - 1.5) < 0.001 && abs($values[2] - 3.14159) < 0.001);
echo "   ✅ VectorFloat test passed\n";

// Test 2: ArrayDouble
echo "\n2. ArrayDouble\n";
$buffer = new WriteBuffer();
$buffer->allocate(100);
$buffer->writeArrayDouble(0, [2.718281828, 1.414213562]);
$reader = new ReadBuffer($buffer->data());
$values = $reader->readArrayDouble(0, 2);
echo "   Values: [" . implode(", ", $values) . "]\n";
assert(abs($values[0] - 2.718281828) < 0.000001);
echo "   ✅ ArrayDouble test passed\n";

echo "\n✅ All float/double collection tests passed!\n";

