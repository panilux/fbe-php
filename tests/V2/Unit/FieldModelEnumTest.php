<?php

declare(strict_types=1);

namespace FBE\Tests\V2\Unit;

use FBE\V2\Common\{WriteBuffer, ReadBuffer};
use FBE\V2\Standard\{
    FieldModelSide as StdSide,
    FieldModelOrderStatus as StdOrderStatus
};
use FBE\V2\Final\{
    FieldModelSide as FinalSide,
    FieldModelOrderStatus as FinalOrderStatus
};
use FBE\V2\Types\{Side, OrderStatus};
use PHPUnit\Framework\TestCase;

class FieldModelEnumTest extends TestCase
{
    public function testStandardEnumSide(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new StdSide($writeBuffer, 0);
        $field->set(Side::Buy);

        $this->assertEquals(4, $field->size()); // int32
        $this->assertEquals(0, $field->extra()); // No extra (fixed-size)

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new StdSide($readBuffer, 0);

        $this->assertEquals(Side::Buy, $readField->get());
    }

    public function testStandardEnumSideSell(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new StdSide($writeBuffer, 0);
        $field->set(Side::Sell);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new StdSide($readBuffer, 0);

        $this->assertEquals(Side::Sell, $readField->get());
    }

    public function testFinalEnumSide(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FinalSide($writeBuffer, 0);
        $field->set(Side::Buy);

        $this->assertEquals(4, $field->size()); // int32
        $this->assertEquals(0, $field->extra());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FinalSide($readBuffer, 0);

        $this->assertEquals(Side::Buy, $readField->get());
    }

    public function testStandardEnumOrderStatus(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new StdOrderStatus($writeBuffer, 0);
        $field->set(OrderStatus::Processing);

        $this->assertEquals(1, $field->size()); // int8 (compact!)
        $this->assertEquals(0, $field->extra());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new StdOrderStatus($readBuffer, 0);

        $this->assertEquals(OrderStatus::Processing, $readField->get());
    }

    public function testFinalEnumOrderStatus(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        $field = new FinalOrderStatus($writeBuffer, 0);
        $field->set(OrderStatus::Delivered);

        $this->assertEquals(1, $field->size()); // int8
        $this->assertEquals(0, $field->extra());

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $readField = new FinalOrderStatus($readBuffer, 0);

        $this->assertEquals(OrderStatus::Delivered, $readField->get());
    }

    public function testAllOrderStatusValues(): void
    {
        $statuses = [
            OrderStatus::Pending,
            OrderStatus::Processing,
            OrderStatus::Shipped,
            OrderStatus::Delivered,
            OrderStatus::Cancelled,
            OrderStatus::Refunded,
        ];

        foreach ($statuses as $status) {
            $writeBuffer = new WriteBuffer();
            $writeBuffer->allocate(100);

            $field = new StdOrderStatus($writeBuffer, 0);
            $field->set($status);

            // Read back
            $readBuffer = new ReadBuffer($writeBuffer->data());
            $readField = new StdOrderStatus($readBuffer, 0);

            $this->assertEquals($status, $readField->get());
        }
    }

    public function testEnumValuesAreCorrect(): void
    {
        // Verify enum values match FBE spec
        $this->assertEquals(0, Side::Buy->value);
        $this->assertEquals(1, Side::Sell->value);

        $this->assertEquals(0, OrderStatus::Pending->value);
        $this->assertEquals(1, OrderStatus::Processing->value);
        $this->assertEquals(2, OrderStatus::Shipped->value);
        $this->assertEquals(3, OrderStatus::Delivered->value);
        $this->assertEquals(4, OrderStatus::Cancelled->value);
        $this->assertEquals(5, OrderStatus::Refunded->value);
    }

    public function testStandardVsFinalIdentical(): void
    {
        // For enums, Standard and Final should produce identical binary
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(100);
        $fieldStd = new StdSide($writeBufferStd, 0);
        $fieldStd->set(Side::Sell);

        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(100);
        $fieldFinal = new FinalSide($writeBufferFinal, 0);
        $fieldFinal->set(Side::Sell);

        // Binary should be identical
        $this->assertEquals(
            substr($writeBufferStd->data(), 0, 4),
            substr($writeBufferFinal->data(), 0, 4)
        );
    }

    public function testEnumSizeComparison(): void
    {
        // Side: int32 (4 bytes)
        $writeBuffer1 = new WriteBuffer();
        $writeBuffer1->allocate(100);
        $side = new StdSide($writeBuffer1, 0);
        $side->set(Side::Buy);

        // OrderStatus: int8 (1 byte)
        $writeBuffer2 = new WriteBuffer();
        $writeBuffer2->allocate(100);
        $status = new StdOrderStatus($writeBuffer2, 0);
        $status->set(OrderStatus::Pending);

        // OrderStatus is 4x more compact
        $this->assertEquals(4, $side->size());
        $this->assertEquals(1, $status->size());
    }

    public function testMultipleEnumsSequential(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        // Write Side at offset 0
        $side = new StdSide($writeBuffer, 0);
        $side->set(Side::Sell);

        // Write OrderStatus at offset 4
        $status = new StdOrderStatus($writeBuffer, 4);
        $status->set(OrderStatus::Shipped);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());

        $readSide = new StdSide($readBuffer, 0);
        $this->assertEquals(Side::Sell, $readSide->get());

        $readStatus = new StdOrderStatus($readBuffer, 4);
        $this->assertEquals(OrderStatus::Shipped, $readStatus->get());
    }

    public function testEnumRoundTripWithDifferentOffsets(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        // Write at various offsets
        $offsets = [0, 10, 20, 50];
        $values = [
            OrderStatus::Pending,
            OrderStatus::Processing,
            OrderStatus::Delivered,
            OrderStatus::Cancelled,
        ];

        foreach ($offsets as $i => $offset) {
            $field = new StdOrderStatus($writeBuffer, $offset);
            $field->set($values[$i]);
        }

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());

        foreach ($offsets as $i => $offset) {
            $field = new StdOrderStatus($readBuffer, $offset);
            $this->assertEquals($values[$i], $field->get());
        }
    }

    public function testEnumInvalidValueThrows(): void
    {
        $this->expectException(\ValueError::class);

        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(100);

        // Write invalid value (99 doesn't exist in Side enum)
        $writeBuffer->writeInt32(0, 99);

        // Try to read as Side enum - should throw
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $field = new StdSide($readBuffer, 0);
        $field->get();
    }
}
