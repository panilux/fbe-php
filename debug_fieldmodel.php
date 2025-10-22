<?php
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/src/FBE/FieldModel.php';
require_once __DIR__ . '/src/FBE/FieldModelString.php';

$binary = hex2bin('2a0000000700000050616e696c757800000000');
echo "Binary: " . bin2hex($binary) . "\n";
echo "Length: " . strlen($binary) . " bytes\n\n";

$reader = new \FBE\ReadBuffer($binary);

// Create FieldModelString at offset 4
$fieldModel = new \FBE\FieldModelString($reader, 4);

// Read pointer
$pointer = $reader->readUInt32(4);
echo "Pointer at offset 4: $pointer\n";

// Read size at pointer
$size = $reader->readUInt32($pointer);
echo "Size at pointer $pointer: $size\n";

// Read string
$str = $fieldModel->get();
echo "String via FieldModel: '$str'\n";
echo "String hex: " . bin2hex($str) . "\n";
