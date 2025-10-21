<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/src/FBE/FieldModel.php';
require_once __DIR__ . '/src/FBE/FieldModelCollections.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FieldModelVectorInt32;
use FBE\FieldModelArrayInt32;
use FBE\FieldModelMapInt32;
use FBE\FieldModelSetInt32;

echo "Testing FBE Collection Field Models...\n\n";

// Test 1: Vector
echo "1. FieldModelVectorInt32\n";
$buffer = new WriteBuffer();
$buffer->allocate(4 + 4 + 12); // pointer + size + 3 elements
$field = new FieldModelVectorInt32($buffer, 0);
$field->set([10, 20, 30]);
echo "   Size: {$field->size()} bytes (pointer)\n";
echo "   Binary: " . bin2hex($buffer->data()) . "\n";

// Read back
$reader = new ReadBuffer($buffer->data());
$field2 = new FieldModelVectorInt32($reader, 0);
$values = $field2->get();
echo "   Values: [" . implode(", ", $values) . "]\n";
echo "   Extra: {$field2->extra()} bytes\n";
assert($values === [10, 20, 30], "Vector read failed");
echo "   ✅ Vector test passed\n";

// Test 2: Array
echo "\n2. FieldModelArrayInt32\n";
$buffer = new WriteBuffer();
$buffer->allocate(12); // 3 × 4 bytes
$field = new FieldModelArrayInt32($buffer, 0, 3);
$field->set([100, 200, 300]);
echo "   Size: {$field->size()} bytes\n";
echo "   Binary: " . bin2hex($buffer->data()) . "\n";

// Read back
$reader = new ReadBuffer($buffer->data());
$field2 = new FieldModelArrayInt32($reader, 0, 3);
$values = $field2->get();
echo "   Values: [" . implode(", ", $values) . "]\n";
assert($values === [100, 200, 300], "Array read failed");
echo "   ✅ Array test passed\n";

// Test 3: Map
echo "\n3. FieldModelMapInt32\n";
$buffer = new WriteBuffer();
$buffer->allocate(4 + 4 + 16); // pointer + size + 2 pairs
$field = new FieldModelMapInt32($buffer, 0);
$field->set([1 => 100, 2 => 200]);
echo "   Size: {$field->size()} bytes (pointer)\n";
echo "   Binary: " . bin2hex($buffer->data()) . "\n";

// Read back
$reader = new ReadBuffer($buffer->data());
$field2 = new FieldModelMapInt32($reader, 0);
$map = $field2->get();
echo "   Map: {";
foreach ($map as $k => $v) {
    echo "$k => $v, ";
}
echo "}\n";
echo "   Extra: {$field2->extra()} bytes\n";
assert($map === [1 => 100, 2 => 200], "Map read failed");
echo "   ✅ Map test passed\n";

// Test 4: Set
echo "\n4. FieldModelSetInt32\n";
$buffer = new WriteBuffer();
$buffer->allocate(4 + 4 + 12); // pointer + size + 3 elements
$field = new FieldModelSetInt32($buffer, 0);
$field->set([5, 10, 15, 10]); // Duplicate 10 should be removed
echo "   Size: {$field->size()} bytes (pointer)\n";
echo "   Binary: " . bin2hex($buffer->data()) . "\n";

// Read back
$reader = new ReadBuffer($buffer->data());
$field2 = new FieldModelSetInt32($reader, 0);
$values = $field2->get();
echo "   Values: [" . implode(", ", $values) . "]\n";
echo "   Extra: {$field2->extra()} bytes\n";
assert(count($values) === 3, "Set uniqueness failed");
echo "   ✅ Set test passed\n";

echo "\n✅ All collection field models tested successfully!\n";

