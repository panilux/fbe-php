<?php
/**
 * Cross-platform Model/FinalModel test: PHP ↔ Rust
 */

declare(strict_types=1);

require_once __DIR__ . '/test/ProductModel.php';
require_once __DIR__ . '/test/ProductFinalModel.php';

echo "=== PHP ↔ Rust Model/FinalModel Cross-Platform Test ===\n\n";

// Create test product
$product = new Product();
$product->id = 123;
$product->name = "Laptop";
$product->price = 999.99;
$product->quantity = 5;

// Test 1: PHP writes Model
echo "Test 1: PHP → Rust (Model)\n";
$model = new ProductModel();
$modelSize = $model->serialize($product);
file_put_contents('/tmp/php_product_model.bin', $model->getBuffer()->data());
echo "✓ PHP wrote Product (Model): $modelSize bytes\n";
echo "  Binary: " . bin2hex($model->getBuffer()->data()) . "\n\n";

// Test 2: Rust writes Model, PHP reads
echo "Test 2: Rust → PHP (Model)\n";
if (file_exists('/tmp/rust_product_model.bin')) {
    $data = file_get_contents('/tmp/rust_product_model.bin');
    $readBuffer = new \FBE\ReadBuffer($data);

    // Read size header
    $totalSize = $readBuffer->readUInt32(0);

    // Deserialize struct
    $product2 = new Product();
    $product2->id = $readBuffer->readInt32(4);
    $product2->name = $readBuffer->readString(8);
    $product2->price = $readBuffer->readDouble(8 + 4 + strlen($product2->name));
    $product2->quantity = $readBuffer->readInt32(8 + 4 + strlen($product2->name) + 8);
    $readSize = $totalSize;

    echo "✓ PHP read Product (Model) from Rust: $readSize bytes\n";
    echo "  id={$product2->id}, name={$product2->name}, price={$product2->price}, quantity={$product2->quantity}\n";

    assert($product2->id === 123);
    assert($product2->name === "Laptop");
    assert($product2->price === 999.99);
    assert($product2->quantity === 5);
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_product_model.bin\n\n";
}

// Test 3: PHP writes FinalModel
echo "Test 3: PHP → Rust (FinalModel)\n";
$finalModel = new ProductFinalModel();
$finalSize = $finalModel->serialize($product);
file_put_contents('/tmp/php_product_final.bin', $finalModel->getBuffer()->data());
echo "✓ PHP wrote Product (FinalModel): $finalSize bytes\n";
echo "  Binary: " . bin2hex($finalModel->getBuffer()->data()) . "\n\n";

// Test 4: Rust writes FinalModel, PHP reads
echo "Test 4: Rust → PHP (FinalModel)\n";
if (file_exists('/tmp/rust_product_final.bin')) {
    $data = file_get_contents('/tmp/rust_product_final.bin');
    $readBuffer = new \FBE\ReadBuffer($data);

    // Deserialize struct (no header)
    $product3 = new Product();
    $product3->id = $readBuffer->readInt32(0);
    $product3->name = $readBuffer->readString(4);
    $product3->price = $readBuffer->readDouble(4 + 4 + strlen($product3->name));
    $product3->quantity = $readBuffer->readInt32(4 + 4 + strlen($product3->name) + 8);
    $readSize2 = strlen($data);

    echo "✓ PHP read Product (FinalModel) from Rust: $readSize2 bytes\n";
    echo "  id={$product3->id}, name={$product3->name}, price={$product3->price}, quantity={$product3->quantity}\n";

    assert($product3->id === 123);
    assert($product3->name === "Laptop");
    assert($product3->price === 999.99);
    assert($product3->quantity === 5);
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_product_final.bin\n\n";
}

echo "=== Cross-Platform Model/FinalModel Tests Complete ===\n";

