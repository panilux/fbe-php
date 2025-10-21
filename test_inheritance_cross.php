<?php
/**
 * Cross-platform inheritance test: PHP ↔ Rust
 * HERSEY DAHA IYI BIR PANILUX ICIN! 🚀
 */

declare(strict_types=1);

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/test/gen_inheritance/Person.php';
require_once __DIR__ . '/test/gen_inheritance/Employee.php';
require_once __DIR__ . '/test/gen_inheritance/Manager.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;

echo "=== PHP ↔ Rust Inheritance Cross-Platform Test ===\n\n";

// Test 1: PHP writes, Rust reads (Manager)
echo "Test 1: PHP → Rust (Manager)\n";
$manager = new Manager();
$manager->name = "Charlie";
$manager->age = 40;
$manager->company = "Panilux";
$manager->salary = 95000.75;
$manager->teamSize = 10;

$buffer = new WriteBuffer();
$size = $manager->serialize($buffer);
file_put_contents('/tmp/php_manager.bin', $buffer->data());
echo "✓ PHP wrote Manager: $size bytes\n";
echo "  Binary: " . bin2hex($buffer->data()) . "\n\n";

// Test 2: Rust writes, PHP reads (Manager)
echo "Test 2: Rust → PHP (Manager)\n";
if (file_exists('/tmp/rust_manager.bin')) {
    $data = file_get_contents('/tmp/rust_manager.bin');
    $readBuffer = new ReadBuffer($data);
    $manager2 = Manager::deserialize($readBuffer);
    
    echo "✓ PHP read Manager from Rust\n";
    echo "  Name: {$manager2->name}, Age: {$manager2->age}, ";
    echo "Company: {$manager2->company}, Salary: {$manager2->salary}, ";
    echo "Team: {$manager2->teamSize}\n";
    
    assert($manager2->name === "Charlie");
    assert($manager2->age === 40);
    assert($manager2->company === "Panilux");
    assert($manager2->salary === 95000.75);
    assert($manager2->teamSize === 10);
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_manager.bin\n\n";
}

// Test 3: PHP writes Employee
echo "Test 3: PHP → Rust (Employee)\n";
$employee = new Employee();
$employee->name = "Bob";
$employee->age = 35;
$employee->company = "Panilux";
$employee->salary = 75000.50;

$buffer = new WriteBuffer();
$employee->serialize($buffer);
file_put_contents('/tmp/php_employee.bin', $buffer->data());
echo "✓ PHP wrote Employee\n\n";

// Test 4: Rust writes, PHP reads (Employee)
echo "Test 4: Rust → PHP (Employee)\n";
if (file_exists('/tmp/rust_employee.bin')) {
    $data = file_get_contents('/tmp/rust_employee.bin');
    $readBuffer = new ReadBuffer($data);
    $employee2 = Employee::deserialize($readBuffer);
    
    echo "✓ PHP read Employee from Rust\n";
    echo "  Name: {$employee2->name}, Age: {$employee2->age}, ";
    echo "Company: {$employee2->company}, Salary: {$employee2->salary}\n";
    
    assert($employee2->name === "Bob");
    assert($employee2->age === 35);
    assert($employee2->company === "Panilux");
    assert($employee2->salary === 75000.50);
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_employee.bin\n\n";
}

// Test 5: PHP writes Person
echo "Test 5: PHP → Rust (Person)\n";
$person = new Person();
$person->name = "Alice";
$person->age = 30;

$buffer = new WriteBuffer();
$person->serialize($buffer);
file_put_contents('/tmp/php_person.bin', $buffer->data());
echo "✓ PHP wrote Person\n\n";

// Test 6: Rust writes, PHP reads (Person)
echo "Test 6: Rust → PHP (Person)\n";
if (file_exists('/tmp/rust_person.bin')) {
    $data = file_get_contents('/tmp/rust_person.bin');
    $readBuffer = new ReadBuffer($data);
    $person2 = Person::deserialize($readBuffer);
    
    echo "✓ PHP read Person from Rust\n";
    echo "  Name: {$person2->name}, Age: {$person2->age}\n";
    
    assert($person2->name === "Alice");
    assert($person2->age === 30);
    echo "✓ Verification passed\n\n";
} else {
    echo "⚠ Waiting for Rust to write /tmp/rust_person.bin\n\n";
}

echo "=== Cross-Platform Tests Complete ===\n";

