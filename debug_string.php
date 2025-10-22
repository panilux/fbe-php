<?php
require_once __DIR__ . '/src/FBE/ReadBuffer.php';

$binary = hex2bin('2a0000000700000050616e696c757800000000');
echo "Binary: " . bin2hex($binary) . "\n";
echo "Length: " . strlen($binary) . " bytes\n\n";

$reader = new \FBE\ReadBuffer($binary);

// Read id
$id = $reader->readInt32(0);
echo "ID at offset 0: $id\n";

// Read string length
$len = $reader->readUInt32(4);
echo "String length at offset 4: $len\n";

// Read string
$str = $reader->readString(4);
echo "String at offset 4: '$str'\n";
echo "String hex: " . bin2hex($str) . "\n";

// Manual read
$manual = substr($binary, 8, 7);
echo "\nManual read (offset 8, length 7): '$manual'\n";
echo "Manual hex: " . bin2hex($manual) . "\n";
