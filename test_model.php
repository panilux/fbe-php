<?php
/**
 * Test Model/FinalModel in PHP FBE
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */

declare(strict_types=1);

require_once __DIR__ . '/test/ProductModel.php';
require_once __DIR__ . '/test/ProductFinalModel.php';

use FBE\WriteBuffer;

echo "=== FBE Model/FinalModel Test ===\n\n";

// Create test product
$product = new Product();
$product->id = 123;
$product->name = "Laptop";
$product->price = 999.99;
$product->quantity = 5;

// Test 1: Model (with 4-byte header)
echo "Test 1: Model (with header)\n";
$model = new ProductModel();
$modelSize = $model->serialize($product);
$modelData = $model->getBuffer()->data();

echo "Serialized size: $modelSize bytes\n";
echo "Binary (hex): " . bin2hex($modelData) . "\n";

// Verify header
$header = unpack('V', substr($modelData, 0, 4))[1];
echo "Size header: $header bytes\n";
assert($header === $modelSize, "Header should match total size");

// Deserialize
[$product2, $readSize] = $model->deserialize();
echo "Deserialized: id={$product2->id}, name={$product2->name}, price={$product2->price}, quantity={$product2->quantity}\n";
assert($product2->id === 123);
assert($product2->name === "Laptop");
assert($product2->price === 999.99);
assert($product2->quantity === 5);
assert($readSize === $modelSize);
echo "✓ Model test passed\n\n";

// Test 2: FinalModel (without header)
echo "Test 2: FinalModel (without header)\n";
$finalModel = new ProductFinalModel();
$finalSize = $finalModel->serialize($product);
$finalData = $finalModel->getBuffer()->data();

echo "Serialized size: $finalSize bytes\n";
echo "Binary (hex): " . bin2hex($finalData) . "\n";

// Deserialize
[$product3, $readSize2] = $finalModel->deserialize();
echo "Deserialized: id={$product3->id}, name={$product3->name}, price={$product3->price}, quantity={$product3->quantity}\n";
assert($product3->id === 123);
assert($product3->name === "Laptop");
assert($product3->price === 999.99);
assert($product3->quantity === 5);
echo "✓ FinalModel test passed\n\n";

// Test 3: Size comparison
echo "Test 3: Size comparison\n";
echo "Model size: $modelSize bytes (with 4-byte header)\n";
echo "FinalModel size: $finalSize bytes (no header)\n";
echo "Difference: " . ($modelSize - $finalSize) . " bytes\n";

assert($modelSize === $finalSize + 4, "Model should be 4 bytes larger than FinalModel");
echo "✓ Size comparison test passed\n\n";

// Test 4: Data comparison (skip header in Model)
echo "Test 4: Data comparison\n";
$modelDataWithoutHeader = substr($modelData, 4);  // Skip 4-byte header
echo "Model data (without header): " . bin2hex($modelDataWithoutHeader) . "\n";
echo "FinalModel data:             " . bin2hex($finalData) . "\n";

assert($modelDataWithoutHeader === $finalData, "Data should be identical (excluding header)");
echo "✓ Data comparison test passed\n\n";

echo "=== All Model/FinalModel Tests Passed! ===\n";

