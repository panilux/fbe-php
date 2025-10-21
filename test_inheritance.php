<?php
/**
 * Test struct inheritance in PHP FBE
 */

declare(strict_types=1);

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/test/gen_inheritance/Person.php';
require_once __DIR__ . '/test/gen_inheritance/Employee.php';
require_once __DIR__ . '/test/gen_inheritance/Manager.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== FBE Struct Inheritance Test ===\n\n";

// Test 1: Base struct (Person)
echo "Test 1: Base struct (Person)\n";
$person = new Person();
$person->name = "Alice";
$person->age = 30;

$buffer = new WriteBuffer();
$size = $person->serialize($buffer);
echo "Serialized Person: $size bytes\n";
echo "Binary: " . bin2hex($buffer->data()) . "\n";

$readBuffer = new ReadBuffer($buffer->data());
$person2 = Person::deserialize($readBuffer);
echo "Deserialized: {$person2->name}, age {$person2->age}\n";
assert($person2->name === "Alice");
assert($person2->age === 30);
echo "✓ Base struct test passed\n\n";

// Test 2: Derived struct (Employee)
echo "Test 2: Derived struct (Employee)\n";
$employee = new Employee();
$employee->name = "Bob";
$employee->age = 35;
$employee->company = "Panilux";
$employee->salary = 75000.50;

$buffer = new WriteBuffer();
$size = $employee->serialize($buffer);
echo "Serialized Employee: $size bytes\n";
echo "Binary: " . bin2hex($buffer->data()) . "\n";

$readBuffer = new ReadBuffer($buffer->data());
$employee2 = Employee::deserialize($readBuffer);
echo "Deserialized: {$employee2->name}, age {$employee2->age}, ";
echo "company {$employee2->company}, salary {$employee2->salary}\n";
assert($employee2->name === "Bob");
assert($employee2->age === 35);
assert($employee2->company === "Panilux");
assert($employee2->salary === 75000.50);
echo "✓ Derived struct test passed\n\n";

// Test 3: Multi-level inheritance (Manager)
echo "Test 3: Multi-level inheritance (Manager)\n";
$manager = new Manager();
$manager->name = "Charlie";
$manager->age = 40;
$manager->company = "Panilux";
$manager->salary = 95000.75;
$manager->teamSize = 10;

$buffer = new WriteBuffer();
$size = $manager->serialize($buffer);
echo "Serialized Manager: $size bytes\n";
echo "Binary: " . bin2hex($buffer->data()) . "\n";

$readBuffer = new ReadBuffer($buffer->data());
$manager2 = Manager::deserialize($readBuffer);
echo "Deserialized: {$manager2->name}, age {$manager2->age}, ";
echo "company {$manager2->company}, salary {$manager2->salary}, ";
echo "team size {$manager2->teamSize}\n";
assert($manager2->name === "Charlie");
assert($manager2->age === 40);
assert($manager2->company === "Panilux");
assert($manager2->salary === 95000.75);
assert($manager2->teamSize === 10);
echo "✓ Multi-level inheritance test passed\n\n";

// Test 4: Field inheritance verification
echo "Test 4: Field inheritance verification\n";
echo "Manager has Person fields: " . (property_exists($manager, 'name') ? 'YES' : 'NO') . "\n";
echo "Manager has Employee fields: " . (property_exists($manager, 'company') ? 'YES' : 'NO') . "\n";
echo "Manager has Manager fields: " . (property_exists($manager, 'teamSize') ? 'YES' : 'NO') . "\n";
assert(property_exists($manager, 'name'));
assert(property_exists($manager, 'company'));
assert(property_exists($manager, 'teamSize'));
echo "✓ Field inheritance verified\n\n";

echo "=== All Inheritance Tests Passed! ===\n";

