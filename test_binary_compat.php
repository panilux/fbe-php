<?php

require_once __DIR__ . '/fbe-php/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/fbe-php/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=" . str_repeat("=", 59) . "\n";
echo "Binary Compatibility Test: Simple Struct\n";
echo "=" . str_repeat("=", 59) . "\n\n";

// Simple struct: id(int32) + name(string) + value(double)
echo "TEST 1: PHP Write\n";
echo str_repeat("-", 60) . "\n";

$buffer = new WriteBuffer();
$buffer->allocate(100);

// Write fields
$offset = 0;
$buffer->writeInt32($offset, 42);  // id
$offset += 4;

$buffer->writeString($offset, "EURUSD");  // name (4-byte len + data)
$offset += 4 + 6;  // length prefix + "EURUSD"

$buffer->writeDouble($offset, 1.23456);  // value
$offset += 8;

echo "Total written: $offset bytes\n";
echo "Binary (hex): " . bin2hex(substr($buffer->data(), 0, $offset)) . "\n";

// Save
file_put_contents('/tmp/simple_php.bin', substr($buffer->data(), 0, $offset));
echo "✅ Saved to /tmp/simple_php.bin\n";

// Test 2: PHP Read
echo "\n" . str_repeat("=", 60) . "\n";
echo "TEST 2: PHP Read\n";
echo str_repeat("=", 60) . "\n";

$reader = new ReadBuffer($buffer->data());
$offset = 0;

$id = $reader->readInt32($offset);
$offset += 4;

$name = $reader->readString($offset);
$offset += 4 + strlen($name);

$value = $reader->readDouble($offset);

echo "ID: $id\n";
echo "Name: $name\n";
echo "Value: $value\n";

assert($id === 42);
assert($name === "EURUSD");
assert(abs($value - 1.23456) < 0.00001);

echo "\n✅ PHP binary format working!\n";

