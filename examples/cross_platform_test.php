<?php
/**
 * Cross-Platform Test - PHP Side
 *
 * This script generates FBE binary data that can be verified by Python FBE implementation.
 *
 * Usage:
 *   php examples/cross_platform_test.php > test_data.fbe
 *   python3 examples/cross_platform_test.py < test_data.fbe
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use FBE\Common\WriteBuffer;
use FBE\Standard\{
    FieldModelInt32,
    FieldModelInt64,
    FieldModelFloat,
    FieldModelDouble,
    FieldModelBool,
    FieldModelString,
    FieldModelBytes,
    FieldModelUuid,
    FieldModelDecimal,
    FieldModelTimestamp,
    FieldModelVectorInt32,
    FieldModelVectorString,
    FieldModelOptionalInt32,
    FieldModelOptionalString
};
use FBE\Types\{Uuid, Decimal};

// Create buffer - DO NOT pre-allocate, let it grow naturally
$buffer = new WriteBuffer(0);  // Start empty
$offset = 0;

// Test 1: Primitives
echo "Writing primitives...\n" . PHP_EOL;

$int32 = new FieldModelInt32($buffer, $offset);
$int32->set(42);
$offset += $int32->total();

$int64 = new FieldModelInt64($buffer, $offset);
$int64->set(9876543210);
$offset += $int64->total();

$float = new FieldModelFloat($buffer, $offset);
$float->set(3.14159);
$offset += $float->total();

$double = new FieldModelDouble($buffer, $offset);
$double->set(2.718281828459045);
$offset += $double->total();

$bool = new FieldModelBool($buffer, $offset);
$bool->set(true);
$offset += $bool->total();

// Test 2: String
echo "Writing string...\n" . PHP_EOL;

$string = new FieldModelString($buffer, $offset);
$string->set('Hello from PHP FBE!');
$offset += $string->total();

// Test 3: Bytes
echo "Writing bytes...\n" . PHP_EOL;

$bytes = new FieldModelBytes($buffer, $offset);
$bytes->set("\x00\x01\x02\x03\x04\xFF\xFE\xFD");
$offset += $bytes->total();

// Test 4: UUID
echo "Writing UUID...\n" . PHP_EOL;

$uuid = new FieldModelUuid($buffer, $offset);
$uuid->set(new Uuid('550e8400-e29b-41d4-a716-446655440000'));
$offset += $uuid->total();

// Test 5: Decimal
echo "Writing decimal...\n" . PHP_EOL;

$decimal = new FieldModelDecimal($buffer, $offset);
$decimal->set(Decimal::fromString('123.456'));
$offset += $decimal->total();

// Test 6: Timestamp
echo "Writing timestamp...\n" . PHP_EOL;

$timestamp = new FieldModelTimestamp($buffer, $offset);
$timestamp->set(1234567890123456789); // ~2009-02-13 23:31:30
$offset += $timestamp->total();

// Test 7: Vector<Int32>
echo "Writing vector<int32>...\n" . PHP_EOL;

$vectorInt = new FieldModelVectorInt32($buffer, $offset);
$vectorInt->set([10, 20, 30, 40, 50]);
$offset += $vectorInt->total();

// Test 8: Vector<String>
echo "Writing vector<string>...\n" . PHP_EOL;

$vectorString = new FieldModelVectorString($buffer, $offset);
$vectorString->set(['apple', 'banana', 'cherry']);
$offset += $vectorString->total();

// Test 9: Optional<Int32> with value
echo "Writing optional<int32> with value...\n" . PHP_EOL;

$optionalInt = new FieldModelOptionalInt32($buffer, $offset);
$optionalInt->set(999);
$offset += $optionalInt->total();

// Test 10: Optional<String> null
echo "Writing optional<string> null...\n" . PHP_EOL;

$optionalString = new FieldModelOptionalString($buffer, $offset);
$optionalString->set(null);
$offset += $optionalString->total();

// Output binary data to stdout
echo "\nTotal size: {$offset} bytes\n" . PHP_EOL;
echo "Binary data follows:\n" . PHP_EOL;
echo "---BEGIN FBE DATA---\n";
echo base64_encode(substr($buffer->data(), 0, $offset)) . "\n";
echo "---END FBE DATA---\n";

// Also save to file
$outputFile = __DIR__ . '/../test_cross_platform.fbe';
file_put_contents($outputFile, substr($buffer->data(), 0, $offset));
echo "\nSaved to: {$outputFile}\n";

// Print hex dump for debugging
echo "\nHex dump (first 256 bytes):\n";
$hexData = substr($buffer->data(), 0, min(256, $offset));
$hexDump = '';
for ($i = 0; $i < strlen($hexData); $i += 16) {
    $hexDump .= sprintf("%04x: ", $i);
    $chunk = substr($hexData, $i, 16);

    // Hex bytes
    for ($j = 0; $j < 16; $j++) {
        if ($j < strlen($chunk)) {
            $hexDump .= sprintf("%02x ", ord($chunk[$j]));
        } else {
            $hexDump .= "   ";
        }
        if ($j == 7) $hexDump .= " ";
    }

    // ASCII representation
    $hexDump .= " |";
    for ($j = 0; $j < strlen($chunk); $j++) {
        $char = $chunk[$j];
        $hexDump .= (ord($char) >= 32 && ord($char) < 127) ? $char : '.';
    }
    $hexDump .= "|\n";
}
echo $hexDump;

echo "\n✅ PHP side complete! Now run Python side to verify.\n";
