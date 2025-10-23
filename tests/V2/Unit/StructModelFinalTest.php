<?php

declare(strict_types=1);

namespace FBE\Tests\V2\Unit;

use FBE\V2\Common\{WriteBuffer, ReadBuffer};
use FBE\Tests\V2\Unit\Models\{PersonModel, PersonFinalModel};
use PHPUnit\Framework\TestCase;

class StructModelFinalTest extends TestCase
{
    public function testPersonFinalModelWrite(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $person = new PersonFinalModel($writeBuffer, 0);

        // Set fields (no header needed in Final format)
        $person->name()->set('Alice');
        $person->age()->set(30);

        // Verify size calculations
        $this->assertEquals(13, $person->size()); // 4 + 5 (Alice) + 4 (age)
        $this->assertEquals(0, $person->extra()); // No extra in Final format
    }

    public function testPersonFinalModelRoundTrip(): void
    {
        // Write
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $personWrite = new PersonFinalModel($writeBuffer, 0);
        $personWrite->name()->set('Bob');
        $personWrite->age()->set(25);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $personRead = new PersonFinalModel($readBuffer, 0);

        // Verify
        $this->assertTrue($personRead->verify());
        $this->assertEquals('Bob', $personRead->name()->get());
        $this->assertEquals(25, $personRead->age()->get());
    }

    public function testPersonFinalModelEmpty(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $person = new PersonFinalModel($writeBuffer, 0);
        $person->name()->set('');
        $person->age()->set(0);

        $this->assertEquals(8, $person->size()); // 4 (empty string) + 4 (age)

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $personRead = new PersonFinalModel($readBuffer, 0);

        $this->assertTrue($personRead->verify());
        $this->assertEquals('', $personRead->name()->get());
        $this->assertEquals(0, $personRead->age()->get());
    }

    /**
     * Compare Standard vs Final format size
     */
    public function testStandardVsFinalSize(): void
    {
        $testName = 'Alice';
        $testAge = 30;

        // Standard format
        $writeBufferStd = new WriteBuffer();
        $writeBufferStd->allocate(200);
        $personStd = new PersonModel($writeBufferStd, 0);
        $personStd->writeHeader();
        $personStd->name()->set($testName);
        $personStd->age()->set($testAge);

        // Final format
        $writeBufferFinal = new WriteBuffer();
        $writeBufferFinal->allocate(200);
        $personFinal = new PersonFinalModel($writeBufferFinal, 0);
        $personFinal->name()->set($testName);
        $personFinal->age()->set($testAge);

        // Standard: 4 (header) + 4 (name pointer) + 4 (age) + 4 (name size) + 5 (name data) = 21 bytes
        // Final: 4 (name size) + 5 (name data) + 4 (age) = 13 bytes

        $this->assertEquals(12, $personStd->size()); // Struct size (no extra)
        $this->assertGreaterThan(0, $personStd->extra()); // Has extra for string
        $this->assertEquals(21, $personStd->total()); // Total

        $this->assertEquals(13, $personFinal->size()); // All inline
        $this->assertEquals(0, $personFinal->extra()); // No extra
        $this->assertEquals(13, $personFinal->total()); // Total

        // Final format is more compact!
        $this->assertLessThan($personStd->total(), $personFinal->total());
    }

    public function testPersonFinalModelMultiple(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        // Write first person
        $person1 = new PersonFinalModel($writeBuffer, 0);
        $person1->name()->set('Alice');
        $person1->age()->set(30);

        $offset1Size = $person1->size();

        // Write second person after first
        $person2 = new PersonFinalModel($writeBuffer, $offset1Size);
        $person2->name()->set('Bob');
        $person2->age()->set(25);

        // Read back both
        $readBuffer = new ReadBuffer($writeBuffer->data());

        $read1 = new PersonFinalModel($readBuffer, 0);
        $this->assertTrue($read1->verify());
        $this->assertEquals('Alice', $read1->name()->get());
        $this->assertEquals(30, $read1->age()->get());

        $read2 = new PersonFinalModel($readBuffer, $offset1Size);
        $this->assertTrue($read2->verify());
        $this->assertEquals('Bob', $read2->name()->get());
        $this->assertEquals(25, $read2->age()->get());
    }
}
