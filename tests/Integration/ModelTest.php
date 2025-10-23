<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../test/ProductModel.php';
require_once __DIR__ . '/../../test/ProductFinalModel.php';

final class ModelTest extends TestCase
{
    public function testModelWithHeader(): void
    {
        $product = new \Product();
        $product->id = 123;
        $product->name = "Laptop";
        $product->price = 999.99;
        $product->quantity = 5;
        
        $model = new \ProductModel();
        $modelSize = $model->serialize($product);
        $modelData = $model->getBuffer()->data();
        
        $this->assertGreaterThan(0, $modelSize);
        
        // Verify header
        $header = unpack('V', substr($modelData, 0, 4))[1];
        $this->assertEquals($modelSize, $header);
        
        // Deserialize
        [$product2, $readSize] = $model->deserialize();
        $this->assertEquals(123, $product2->id);
        $this->assertEquals("Laptop", $product2->name);
        $this->assertEquals(999.99, $product2->price);
        $this->assertEquals(5, $product2->quantity);
        $this->assertEquals($modelSize, $readSize);
    }
    
    public function testFinalModelWithoutHeader(): void
    {
        $product = new \Product();
        $product->id = 456;
        $product->name = "Mouse";
        $product->price = 29.99;
        $product->quantity = 10;
        
        $finalModel = new \ProductFinalModel();
        $finalSize = $finalModel->serialize($product);
        
        $this->assertGreaterThan(0, $finalSize);
        
        // Deserialize
        [$product2, $readSize] = $finalModel->deserialize();
        $this->assertEquals(456, $product2->id);
        $this->assertEquals("Mouse", $product2->name);
        $this->assertEquals(29.99, $product2->price);
        $this->assertEquals(10, $product2->quantity);
        $this->assertEquals($finalSize, $readSize);
    }
    
    public function testModelVsFinalModelSize(): void
    {
        $product = new \Product();
        $product->id = 789;
        $product->name = "Keyboard";
        $product->price = 79.99;
        $product->quantity = 3;
        
        $model = new \ProductModel();
        $modelSize = $model->serialize($product);
        
        $finalModel = new \ProductFinalModel();
        $finalSize = $finalModel->serialize($product);
        
        // Model should be 4 bytes larger (header)
        $this->assertEquals($modelSize, $finalSize + 4);
    }
}

