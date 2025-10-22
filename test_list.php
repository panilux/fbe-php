<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "Testing list<T> collection...\n\n";

// Test 1: list<int32>
echo "1. Testing list<int32>...\n";
$writer = new WriteBuffer();
$list = [10, 20, 30, 40, 50];
$writer->writeListInt32(0, $list);

$reader = new ReadBuffer($writer->data());
$result = $reader->readListInt32(0);

assert(count($result) === 5, "List size mismatch");
assert($result === $list, "List values mismatch");
echo "   ✅ list<int32> test passed\n";
echo "   Values: " . implode(", ", $result) . "\n";

// Test 2: Empty list
echo "\n2. Testing empty list...\n";
$writer = new WriteBuffer();
$writer->writeListInt32(0, []);

$reader = new ReadBuffer($writer->data());
$result = $reader->readListInt32(0);

assert(count($result) === 0, "Empty list failed");
echo "   ✅ empty list test passed\n";

echo "\n✅ All list<T> tests passed!\n";

