<?php

declare(strict_types=1);

namespace FBE\Tests\V2\Unit;

use FBE\V2\Common\{WriteBuffer, ReadBuffer};
use FBE\Tests\V2\Unit\Models\PersonModel;
use PHPUnit\Framework\TestCase;

class StructModelTest extends TestCase
{
    public function testPersonModelWrite(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $person = new PersonModel($writeBuffer, 0);

        // Write header first
        $person->writeHeader();

        // Set fields
        $person->name()->set('Alice');
        $person->age()->set(30);

        // Verify size calculations
        $this->assertEquals(12, $person->size()); // 4 + 4 + 4
        $this->assertGreaterThan(0, $person->extra()); // String data
    }

    public function testPersonModelRoundTrip(): void
    {
        // Write
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $personWrite = new PersonModel($writeBuffer, 0);
        $personWrite->writeHeader();
        $personWrite->name()->set('Bob');
        $personWrite->age()->set(25);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $personRead = new PersonModel($readBuffer, 0);

        // Verify
        $this->assertTrue($personRead->verify());
        $this->assertEquals('Bob', $personRead->name()->get());
        $this->assertEquals(25, $personRead->age()->get());
    }

    public function testPersonModelEmpty(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(200);

        $person = new PersonModel($writeBuffer, 0);
        $person->writeHeader();
        $person->name()->set('');
        $person->age()->set(0);

        // Read back
        $readBuffer = new ReadBuffer($writeBuffer->data());
        $personRead = new PersonModel($readBuffer, 0);

        $this->assertTrue($personRead->verify());
        $this->assertEquals('', $personRead->name()->get());
        $this->assertEquals(0, $personRead->age()->get());
    }

    public function testPersonModelMultiple(): void
    {
        $writeBuffer = new WriteBuffer();
        $writeBuffer->allocate(500);

        // Write first person
        $person1 = new PersonModel($writeBuffer, 0);
        $person1->writeHeader();
        $person1->name()->set('Alice');
        $person1->age()->set(30);

        $offset1Total = $person1->total();

        // Write second person after first
        $person2 = new PersonModel($writeBuffer, $offset1Total);
        $person2->writeHeader();
        $person2->name()->set('Bob');
        $person2->age()->set(25);

        // Read back both
        $readBuffer = new ReadBuffer($writeBuffer->data());

        $read1 = new PersonModel($readBuffer, 0);
        $this->assertTrue($read1->verify());
        $this->assertEquals('Alice', $read1->name()->get());
        $this->assertEquals(30, $read1->age()->get());

        $read2 = new PersonModel($readBuffer, $offset1Total);
        $this->assertTrue($read2->verify());
        $this->assertEquals('Bob', $read2->name()->get());
        $this->assertEquals(25, $read2->age()->get());
    }
}
