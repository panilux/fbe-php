<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use FBE\Common\{WriteBuffer, ReadBuffer};
use FBE\Standard\{FieldModelInt32, FieldModelString, FieldModelVectorString, FieldModelOptionalString};
use FBE\Final\{
    FieldModelInt32 as FinalInt32,
    FieldModelString as FinalString,
    FieldModelVectorString as FinalVectorString,
    FieldModelOptionalString as FinalOptionalString
};
use PHPUnit\Framework\TestCase;

/**
 * Integration tests with complex nested structures
 *
 * Tests real-world scenarios with:
 * - Nested structs
 * - Vectors
 * - Optional fields
 * - Mixed field types
 */
class ComplexStructTest extends TestCase
{
    /**
     * Test Standard format: Order with customer name, items list, and optional notes
     */
    public function testStandardFormatOrder(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(1000);

        $offset = 0;

        // Order ID
        $orderId = new FieldModelInt32($writeBuffer, $offset);
        $orderId->set(12345);
        $offset += $orderId->size();

        // Customer Name
        $customerName = new FieldModelString($writeBuffer, $offset);
        $customerName->set('Alice Johnson');
        $offset += $customerName->size();

        // Items (Vector<String>)
        $items = new FieldModelVectorString($writeBuffer, $offset);
        $items->set(['Laptop', 'Mouse', 'Keyboard', 'Monitor']);
        $offset += $items->size();

        // Notes (Optional<String>)
        $notes = new FieldModelOptionalString($writeBuffer, $offset);
        $notes->set('Please deliver before 5 PM');
        $offset += $notes->size();

        // Total written
        $totalSize = $offset + $customerName->extra() + $items->extra() + $notes->extra();

        $this->assertGreaterThan(0, $totalSize);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());

        $readOffset = 0;

        $readOrderId = new FieldModelInt32($readBuffer, $readOffset);
        $this->assertEquals(12345, $readOrderId->get());
        $readOffset += $readOrderId->size();

        $readCustomerName = new FieldModelString($readBuffer, $readOffset);
        $this->assertEquals('Alice Johnson', $readCustomerName->get());
        $readOffset += $readCustomerName->size();

        $readItems = new FieldModelVectorString($readBuffer, $readOffset);
        $this->assertEquals(['Laptop', 'Mouse', 'Keyboard', 'Monitor'], $readItems->get());
        $this->assertEquals(4, $readItems->count());
        $readOffset += $readItems->size();

        $readNotes = new FieldModelOptionalString($readBuffer, $readOffset);
        $this->assertTrue($readNotes->hasValue());
        $this->assertEquals('Please deliver before 5 PM', $readNotes->get());
    }

    /**
     * Test Final format: Same order structure but more compact
     */
    public function testFinalFormatOrder(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(1000);

        $offset = 0;

        // Order ID
        $orderId = new FinalInt32($writeBuffer, $offset);
        $orderId->set(12345);
        $offset += $orderId->size();

        // Customer Name (inline)
        $customerName = new FinalString($writeBuffer, $offset);
        $customerName->set('Alice Johnson');
        $offset += $customerName->size();

        // Items (Vector<String> inline)
        $items = new FinalVectorString($writeBuffer, $offset);
        $items->set(['Laptop', 'Mouse', 'Keyboard', 'Monitor']);
        $offset += $items->size();

        // Notes (Optional<String> inline)
        $notes = new FinalOptionalString($writeBuffer, $offset);
        $notes->set('Please deliver before 5 PM');
        $offset += $notes->size();

        // Total size (all inline, no extra)
        $totalSize = $offset;

        $this->assertGreaterThan(0, $totalSize);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());

        $readOffset = 0;

        $readOrderId = new FinalInt32($readBuffer, $readOffset);
        $this->assertEquals(12345, $readOrderId->get());
        $readOffset += $readOrderId->size();

        $readCustomerName = new FinalString($readBuffer, $readOffset);
        $this->assertEquals('Alice Johnson', $readCustomerName->get());
        $readOffset += $readCustomerName->size();

        $readItems = new FinalVectorString($readBuffer, $readOffset);
        $this->assertEquals(['Laptop', 'Mouse', 'Keyboard', 'Monitor'], $readItems->get());
        $this->assertEquals(4, $readItems->count());
        $readOffset += $readItems->size();

        $readNotes = new FinalOptionalString($readBuffer, $readOffset);
        $this->assertTrue($readNotes->hasValue());
        $this->assertEquals('Please deliver before 5 PM', $readNotes->get());
    }

    /**
     * Test with empty/null optional fields
     */
    public function testOptionalFieldsEmpty(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        $offset = 0;

        // Standard format
        $orderId = new FieldModelInt32($writeBuffer, $offset);
        $orderId->set(999);
        $offset += $orderId->size();

        $notes = new FieldModelOptionalString($writeBuffer, $offset);
        $notes->set(null); // No notes

        $this->assertFalse($notes->hasValue());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readOffset = 4; // Skip order ID

        $readNotes = new FieldModelOptionalString($readBuffer, $readOffset);
        $this->assertFalse($readNotes->hasValue());
        $this->assertNull($readNotes->get());
    }

    /**
     * Test empty vectors
     */
    public function testEmptyVectors(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        // Standard format - empty vector
        $items = new FieldModelVectorString($writeBuffer, 0);
        $items->set([]);

        $this->assertEquals(0, $items->count());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readItems = new FieldModelVectorString($readBuffer, 0);
        $this->assertEquals([], $readItems->get());
        $this->assertEquals(0, $readItems->count());
    }

    /**
     * Compare Standard vs Final format size for same data
     */
    public function testStandardVsFinalSize(): void
    {
        $orderId = 12345;
        $customerName = 'Alice';
        $items = ['Laptop', 'Mouse'];
        $notes = 'Urgent';

        // Standard format
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(1000);

        $offset = 0;
        $orderIdStd = new FieldModelInt32($writeBufferStd, $offset);
        $orderIdStd->set($orderId);
        $offset += $orderIdStd->size();

        $nameStd = new FieldModelString($writeBufferStd, $offset);
        $nameStd->set($customerName);
        $offset += $nameStd->size();

        $itemsStd = new FieldModelVectorString($writeBufferStd, $offset);
        $itemsStd->set($items);
        $offset += $itemsStd->size();

        $notesStd = new FieldModelOptionalString($writeBufferStd, $offset);
        $notesStd->set($notes);

        $stdTotal = $orderIdStd->total() + $nameStd->total() + $itemsStd->total() + $notesStd->total();

        // Final format
        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(1000);

        $offset = 0;
        $orderIdFinal = new FinalInt32($writeBufferFinal, $offset);
        $orderIdFinal->set($orderId);
        $offset += $orderIdFinal->size();

        $nameFinal = new FinalString($writeBufferFinal, $offset);
        $nameFinal->set($customerName);
        $offset += $nameFinal->size();

        $itemsFinal = new FinalVectorString($writeBufferFinal, $offset);
        $itemsFinal->set($items);
        $offset += $itemsFinal->size();

        $notesFinal = new FinalOptionalString($writeBufferFinal, $offset);
        $notesFinal->set($notes);

        $finalTotal = $orderIdFinal->total() + $nameFinal->total() + $itemsFinal->total() + $notesFinal->total();

        // Final format should be more compact
        $this->assertLessThan($stdTotal, $finalTotal);

        // Calculate percentage saved
        $percentSaved = (($stdTotal - $finalTotal) / $stdTotal) * 100;
        $this->assertGreaterThan(20, $percentSaved); // At least 20% more compact
    }

    /**
     * Test large vector serialization
     */
    public function testLargeVector(): void
    {
        // Create vector with 100 items
        $items = [];
        for ($i = 0; $i < 100; $i++) {
            $items[] = "Item_$i";
        }

        // Final format
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(5000);

        $vector = new FinalVectorString($writeBuffer, 0);
        $vector->set($items);

        $this->assertEquals(100, $vector->count());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readVector = new FinalVectorString($readBuffer, 0);

        $readItems = $readVector->get();
        $this->assertCount(100, $readItems);
        $this->assertEquals('Item_0', $readItems[0]);
        $this->assertEquals('Item_99', $readItems[99]);
    }
}
