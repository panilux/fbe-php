<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/src/FBE/FieldModel.php';
require_once __DIR__ . '/src/FBE/FieldModelCollections.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FieldModelVectorString;
use FBE\FieldModelArrayString;

echo "Testing String Collections...\n\n";

// Test 1: VectorString
echo "1. VectorString\n";
$buffer = new WriteBuffer();
$buffer->allocate(4 + 100); // pointer + estimated data
$field = new FieldModelVectorString($buffer, 0);
$field->set(["Hello", "Panilux", "FBE"]);
echo "   Binary: " . bin2hex(substr($buffer->data(), 0, 50)) . "...\n";

$reader = new ReadBuffer($buffer->data());
$field2 = new FieldModelVectorString($reader, 0);
$values = $field2->get();
echo "   Values: [" . implode(", ", $values) . "]\n";
assert($values === ["Hello", "Panilux", "FBE"], "VectorString failed");
echo "   ✅ VectorString test passed\n";

// Test 2: ArrayString
echo "\n2. ArrayString\n";
$buffer = new WriteBuffer();
$buffer->allocate(100);
$field = new FieldModelArrayString($buffer, 0, 2);
$field->set(["Rust", "PHP"]);
echo "   Binary: " . bin2hex(substr($buffer->data(), 0, 30)) . "...\n";

$reader = new ReadBuffer($buffer->data());
$field2 = new FieldModelArrayString($reader, 0, 2);
$values = $field2->get();
echo "   Values: [" . implode(", ", $values) . "]\n";
assert($values === ["Rust", "PHP"], "ArrayString failed");
echo "   ✅ ArrayString test passed\n";

echo "\n✅ All string collection tests passed!\n";

