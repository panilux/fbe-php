<?php

declare(strict_types=1);

namespace FBE\Tests\Integration;

use PHPUnit\Framework\TestCase;
use FBE\WriteBuffer;
use FBE\ReadBuffer;

require_once __DIR__ . '/../../test/gen_inheritance/Person.php';
require_once __DIR__ . '/../../test/gen_inheritance/Employee.php';
require_once __DIR__ . '/../../test/gen_inheritance/Manager.php';

final class InheritanceTest extends TestCase
{
    public function testBaseStruct(): void
    {
        $person = new \Person();
        $person->name = "Alice";
        $person->age = 30;
        
        $buffer = new WriteBuffer();
        $size = $person->serialize($buffer);
        
        $this->assertGreaterThan(0, $size);
        
        $readBuffer = new ReadBuffer($buffer->data());
        $person2 = \Person::deserialize($readBuffer);
        
        $this->assertEquals("Alice", $person2->name);
        $this->assertEquals(30, $person2->age);
    }
    
    public function testDerivedStruct(): void
    {
        $employee = new \Employee();
        $employee->name = "Bob";
        $employee->age = 35;
        $employee->company = "Panilux";
        $employee->salary = 75000.50;
        
        $buffer = new WriteBuffer();
        $size = $employee->serialize($buffer);
        
        $this->assertGreaterThan(0, $size);
        
        $readBuffer = new ReadBuffer($buffer->data());
        $employee2 = \Employee::deserialize($readBuffer);
        
        $this->assertEquals("Bob", $employee2->name);
        $this->assertEquals(35, $employee2->age);
        $this->assertEquals("Panilux", $employee2->company);
        $this->assertEquals(75000.50, $employee2->salary);
    }
    
    public function testMultiLevelInheritance(): void
    {
        $manager = new \Manager();
        $manager->name = "Charlie";
        $manager->age = 40;
        $manager->company = "Panilux";
        $manager->salary = 95000.75;
        $manager->teamSize = 10;
        
        $buffer = new WriteBuffer();
        $size = $manager->serialize($buffer);
        
        $this->assertGreaterThan(0, $size);
        
        $readBuffer = new ReadBuffer($buffer->data());
        $manager2 = \Manager::deserialize($readBuffer);
        
        $this->assertEquals("Charlie", $manager2->name);
        $this->assertEquals(40, $manager2->age);
        $this->assertEquals("Panilux", $manager2->company);
        $this->assertEquals(95000.75, $manager2->salary);
        $this->assertEquals(10, $manager2->teamSize);
    }
    
    public function testFieldInheritanceVerification(): void
    {
        $manager = new \Manager();
        
        // Manager should have Person fields
        $this->assertTrue(property_exists($manager, 'name'));
        $this->assertTrue(property_exists($manager, 'age'));
        
        // Manager should have Employee fields
        $this->assertTrue(property_exists($manager, 'company'));
        $this->assertTrue(property_exists($manager, 'salary'));
        
        // Manager should have Manager fields
        $this->assertTrue(property_exists($manager, 'teamSize'));
    }
    
    public function testInheritanceChain(): void
    {
        // Test that Employee is a Person
        $employee = new \Employee();
        $this->assertInstanceOf(\Person::class, $employee);
        
        // Test that Manager is an Employee
        $manager = new \Manager();
        $this->assertInstanceOf(\Employee::class, $manager);
        
        // Test that Manager is a Person
        $this->assertInstanceOf(\Person::class, $manager);
    }
    
    public function testBinaryCompatibility(): void
    {
        // Serialize as Manager
        $manager = new \Manager();
        $manager->name = "David";
        $manager->age = 45;
        $manager->company = "TechCorp";
        $manager->salary = 120000.00;
        $manager->teamSize = 15;
        
        $buffer = new WriteBuffer();
        $manager->serialize($buffer);
        
        // Deserialize as Manager
        $readBuffer = new ReadBuffer($buffer->data());
        $manager2 = \Manager::deserialize($readBuffer);
        
        $this->assertEquals($manager->name, $manager2->name);
        $this->assertEquals($manager->age, $manager2->age);
        $this->assertEquals($manager->company, $manager2->company);
        $this->assertEquals($manager->salary, $manager2->salary);
        $this->assertEquals($manager->teamSize, $manager2->teamSize);
    }
}

