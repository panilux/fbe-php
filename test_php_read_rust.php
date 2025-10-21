<?php

require_once __DIR__ . '/fbe-php/src/FBE/ReadBuffer.php';

use FBE\ReadBuffer;

echo "=" . str_repeat("=", 59) . "\n";
echo "THE CHALLENGE: PHP reads Rust binary\n";
echo "=" . str_repeat("=", 59) . "\n\n";

// Read Rust binary
$binary = file_get_contents('/tmp/simple_rust.bin');
echo "Rust binary length: " . strlen($binary) . " bytes\n";
echo "Rust binary (hex): " . bin2hex($binary) . "\n\n";

$reader = new ReadBuffer($binary);
$offset = 0;

$id = $reader->readInt32($offset);
$offset += 4;

$name = $reader->readString($offset);
$offset += 4 + strlen($name);

$value = $reader->readDouble($offset);

echo "PHP reading Rust binary:\n";
echo "ID: $id\n";
echo "Name: $name\n";
echo "Value: $value\n";

assert($id === 42);
assert($name === "EURUSD");
assert(abs($value - 1.23456) < 0.00001);

echo "\n🎉 SUCCESS: PHP ↔ Rust binary compatible!\n";

