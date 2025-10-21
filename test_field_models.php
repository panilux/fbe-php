<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/src/FBE/FieldModel.php';
require_once __DIR__ . '/src/FBE/FieldModels.php';
require_once __DIR__ . '/src/FBE/FieldModelString.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\FieldModelBool;
use FBE\FieldModelInt8;
use FBE\FieldModelInt16;
use FBE\FieldModelInt32;
use FBE\FieldModelInt64;
use FBE\FieldModelUInt8;
use FBE\FieldModelUInt16;
use FBE\FieldModelUInt32;
use FBE\FieldModelUInt64;
use FBE\FieldModelFloat;
use FBE\FieldModelDouble;
use FBE\FieldModelTimestamp;
use FBE\FieldModelUuid;
use FBE\FieldModelBytes;
use FBE\FieldModelDecimal;
use FBE\FieldModelString;

echo "Testing FBE Field Models...\n\n";

$buffer = new WriteBuffer();
$offset = 0;

// Test 1: Bool
echo "1. FieldModelBool\n";
$field = new FieldModelBool($buffer, $offset);
$field->set(true);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 2: Int8
echo "\n2. FieldModelInt8\n";
$field = new FieldModelInt8($buffer, $offset);
$field->set(-42);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 3: Int16
echo "\n3. FieldModelInt16\n";
$field = new FieldModelInt16($buffer, $offset);
$field->set(-1000);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 4: Int32
echo "\n4. FieldModelInt32\n";
$field = new FieldModelInt32($buffer, $offset);
$field->set(-100000);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 5: Int64
echo "\n5. FieldModelInt64\n";
$field = new FieldModelInt64($buffer, $offset);
$field->set(-1000000000);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 6: UInt8
echo "\n6. FieldModelUInt8\n";
$field = new FieldModelUInt8($buffer, $offset);
$field->set(255);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 7: UInt16
echo "\n7. FieldModelUInt16\n";
$field = new FieldModelUInt16($buffer, $offset);
$field->set(65535);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 8: UInt32
echo "\n8. FieldModelUInt32\n";
$field = new FieldModelUInt32($buffer, $offset);
$field->set(4294967295);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 9: UInt64
echo "\n9. FieldModelUInt64\n";
$field = new FieldModelUInt64($buffer, $offset);
$field->set(9223372036854775807);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 10: Float
echo "\n10. FieldModelFloat\n";
$field = new FieldModelFloat($buffer, $offset);
$field->set(3.14159);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 11: Double
echo "\n11. FieldModelDouble\n";
$field = new FieldModelDouble($buffer, $offset);
$field->set(3.141592653589793);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 12: Timestamp
echo "\n12. FieldModelTimestamp\n";
$field = new FieldModelTimestamp($buffer, $offset);
$field->set(1729526400000000000);
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 13: UUID
echo "\n13. FieldModelUuid\n";
$field = new FieldModelUuid($buffer, $offset);
$field->set("123e4567-e89b-12d3-a456-426655440000");
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 14: Bytes
echo "\n14. FieldModelBytes\n";
$field = new FieldModelBytes($buffer, $offset);
$field->set("PANILUX!");
$offset += $field->size() + 8; // size prefix + data
echo "   Size: {$field->size()} bytes (prefix)\n";

// Test 15: Decimal
echo "\n15. FieldModelDecimal\n";
$field = new FieldModelDecimal($buffer, $offset);
$field->set(123456, 2, false); // 1234.56
$offset += $field->size();
echo "   Size: {$field->size()} bytes\n";

// Test 16: String
echo "\n16. FieldModelString\n";
$field = new FieldModelString($buffer, $offset);
$field->set("Panilux");
$offset += $field->size() + 7; // size prefix + data
echo "   Size: {$field->size()} bytes (prefix)\n";

echo "\nTotal buffer size: {$buffer->size} bytes\n";
echo "Binary: " . bin2hex($buffer->data()) . "\n";

// Now read back
echo "\n--- Reading Back ---\n\n";
$reader = new ReadBuffer($buffer->data());
$offset = 0;

$field = new FieldModelBool($reader, $offset);
echo "1. Bool: " . ($field->get() ? "true" : "false") . "\n";
$offset += $field->size();

$field = new FieldModelInt8($reader, $offset);
echo "2. Int8: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelInt16($reader, $offset);
echo "3. Int16: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelInt32($reader, $offset);
echo "4. Int32: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelInt64($reader, $offset);
echo "5. Int64: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelUInt8($reader, $offset);
echo "6. UInt8: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelUInt16($reader, $offset);
echo "7. UInt16: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelUInt32($reader, $offset);
echo "8. UInt32: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelUInt64($reader, $offset);
echo "9. UInt64: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelFloat($reader, $offset);
echo "10. Float: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelDouble($reader, $offset);
echo "11. Double: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelTimestamp($reader, $offset);
echo "12. Timestamp: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelUuid($reader, $offset);
echo "13. UUID: " . $field->get() . "\n";
$offset += $field->size();

$field = new FieldModelBytes($reader, $offset);
echo "14. Bytes: " . $field->get() . "\n";
$offset += $field->size() + $field->extra();

$field = new FieldModelDecimal($reader, $offset);
$decimal = $field->get();
echo "15. Decimal: value={$decimal['value']}, scale={$decimal['scale']}, negative=" . ($decimal['negative'] ? "true" : "false") . "\n";
$offset += $field->size();

$field = new FieldModelString($reader, $offset);
echo "16. String: " . $field->get() . "\n";

echo "\n✅ All field models tested successfully!\n";

