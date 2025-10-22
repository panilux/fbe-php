<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "Testing new base types (char, wchar, byte)...\n\n";

// Test 1: byte
echo "1. Testing byte (alias for uint8)...\n";
$writer = new WriteBuffer();
$writer->writeByte(0, 255);
$writer->writeByte(1, 128);
$writer->writeByte(2, 0);

$reader = new ReadBuffer($writer->data());
assert($reader->readByte(0) === 255, "Byte 255 failed");
assert($reader->readByte(1) === 128, "Byte 128 failed");
assert($reader->readByte(2) === 0, "Byte 0 failed");
echo "   ✅ byte test passed\n";

// Test 2: char
echo "\n2. Testing char (1 byte, unsigned)...\n";
$writer = new WriteBuffer();
$writer->writeChar(0, ord('A')); // 65
$writer->writeChar(1, ord('Z')); // 90
$writer->writeChar(2, ord('0')); // 48

$reader = new ReadBuffer($writer->data());
assert($reader->readChar(0) === ord('A'), "Char 'A' failed");
assert($reader->readChar(1) === ord('Z'), "Char 'Z' failed");
assert($reader->readChar(2) === ord('0'), "Char '0' failed");
echo "   ✅ char test passed\n";

// Test 3: wchar
echo "\n3. Testing wchar (4 bytes, little-endian, unsigned)...\n";
$writer = new WriteBuffer();
$writer->writeWChar(0, 0x0410); // Cyrillic 'А'
$writer->writeWChar(4, 0x4E2D); // Chinese '中'
$writer->writeWChar(8, 0x1F600); // Emoji '😀'

$reader = new ReadBuffer($writer->data());
assert($reader->readWChar(0) === 0x0410, "WChar 0x0410 failed");
assert($reader->readWChar(4) === 0x4E2D, "WChar 0x4E2D failed");
assert($reader->readWChar(8) === 0x1F600, "WChar 0x1F600 failed");
echo "   ✅ wchar test passed\n";

echo "\n✅ All new base types tests passed!\n";

