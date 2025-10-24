# FBE PHP Testing Guide

## Running Tests

### Quick Start

Run all tests:
```bash
vendor/bin/phpunit --colors=always --testdox
```

Run specific test suite:
```bash
# Unit tests only
vendor/bin/phpunit tests/Unit/ --colors=always --testdox

# Integration tests only
vendor/bin/phpunit tests/Integration/ --colors=always --testdox

# Specific test file
vendor/bin/phpunit tests/Unit/WriteBufferTest.php
```

### Using Composer

Run all tests via composer:
```bash
composer test
```

Run with coverage:
```bash
composer test:coverage
```

## Test Structure

### Test Organization

```
tests/
├── Unit/                    # Unit tests (153 tests)
│   ├── WriteBufferTest.php  # Buffer write operations
│   ├── ReadBufferTest.php   # Buffer read operations
│   ├── UuidTest.php         # UUID type tests
│   ├── DecimalTest.php      # Decimal type tests
│   ├── FieldModelStandardTest.php
│   ├── FieldModelFinalTest.php
│   ├── FieldModelVectorTest.php
│   ├── FieldModelOptionalTest.php
│   ├── FieldModelMapTest.php
│   ├── FieldModelEnumTest.php
│   ├── StructModelTest.php
│   ├── StructModelFinalTest.php
│   └── Protocol/
│       ├── MessageTest.php
│       ├── MessageRegistryTest.php
│       ├── SenderReceiverTest.php
│       └── ProtocolVersionTest.php
└── Integration/             # Integration tests (6 tests)
    └── ComplexStructTest.php
```

### Test Categories

**Buffer Tests** (37 tests)
- WriteBuffer operations (allocation, growth, primitives)
- ReadBuffer operations (bounds checking, primitives)
- Edge cases (empty, overflow, underflow)

**Type Tests** (25 tests)
- UUID serialization (RFC 4122 big-endian)
- Decimal serialization (96-bit GMP precision)
- Timestamp handling

**FieldModel Tests** (56 tests)
- Standard format (pointer-based)
- Final format (inline)
- Primitives: Bool, Int8-64, UInt8-64, Float, Double
- Complex: String, Bytes, UUID, Decimal, Timestamp
- Collections: Vector<T>, Optional<T>, Map<K,V>
- Enums: Side, OrderStatus

**StructModel Tests** (18 tests)
- Standard format with 4-byte header
- Final format without header
- Nested structures
- Complex object graphs

**Protocol Tests** (33 tests)
- Message framing and parsing
- Type registry
- Sender/Receiver round-trip
- Protocol versioning
- Batch operations

**Integration Tests** (6 tests)
- Complex nested structures
- Large collections (100+ elements)
- Real-world scenarios

## Test Output

```
PHPUnit 11.5.2 by Sebastian Bergmann and contributors.

Runtime:       PHP 8.4.2
Configuration: /Users/mit/Documents/works/gitlab/panilux/fbe-php/phpunit.xml

WriteBuffer (FBE\Tests\Unit\WriteBuffer)
 ✔ Constructor
 ✔ Allocate
 ✔ Write bool
 ✔ Write int32
 ✔ Write uint32
 ✔ Write int64
 ...

Time: 00:00.145, Memory: 10.00 MB

OK (159 tests, 487 assertions)
```

## Test Coverage

### Completed ✅

- ✅ Buffer operations (37 tests)
- ✅ UUID/Decimal types (25 tests)
- ✅ FieldModel primitives (19 tests)
- ✅ FieldModel collections (25 tests)
- ✅ FieldModel enums (12 tests)
- ✅ StructModel (18 tests)
- ✅ Protocol/Message (33 tests)
- ✅ Integration tests (6 tests)

**Total:** 159 tests, 487 assertions

### Metrics

- **Test Count:** 159
- **Assertions:** 487
- **Failures:** 0
- **Errors:** 0
- **Pass Rate:** 100%

## Adding New Tests

### 1. Create Test File

```php
<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use PHPUnit\Framework\TestCase;

class MyFeatureTest extends TestCase
{
    public function testBasicFunctionality(): void
    {
        // Arrange
        $buffer = new WriteBuffer(256);

        // Act
        $buffer->writeInt32(0, 42);

        // Assert
        $this->assertEquals(42, $buffer->readInt32(0));
    }
}
```

### 2. Run Test

```bash
vendor/bin/phpunit tests/Unit/MyFeatureTest.php
```

### 3. Test Naming Conventions

- Test file: `MyFeatureTest.php`
- Test class: `MyFeatureTest extends TestCase`
- Test methods: `testSomething(): void`
- Use descriptive names: `testWriteBufferGrowsWhenCapacityExceeded()`

### 4. Assertion Best Practices

```php
// Use specific assertions
$this->assertEquals($expected, $actual);
$this->assertTrue($condition);
$this->assertInstanceOf(ClassName::class, $object);

// Use delta for floats
$this->assertEqualsWithDelta(3.14, $actual, 0.01);

// Test exceptions
$this->expectException(BufferOverflowException::class);
$this->expectExceptionMessage('Buffer overflow');
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: gmp, mbstring
      - run: composer install
      - run: composer test
```

### Exit Codes

- `0` - All tests passed
- `1` - Test failures or errors
- `2` - Invalid configuration

## Troubleshooting

### Common Issues

**"Class not found"**
- Run `composer dump-autoload`
- Check namespace in test file

**"Call to undefined function"**
- Check PHP version: `php -v` (requires 8.4+)
- Install required extensions: `php -m | grep gmp`

**"Buffer overflow exception"**
- This is expected for bounds checking tests
- Use `$this->expectException()` to assert

**Float precision errors**
- Use `assertEqualsWithDelta()` instead of `assertEquals()`
- Example: `$this->assertEqualsWithDelta(3.14, $actual, 0.01);`

### Debug Mode

Run tests with verbose output:
```bash
vendor/bin/phpunit --verbose
```

Show test execution order:
```bash
vendor/bin/phpunit --testdox --order-by=depends
```

## Performance Testing

Benchmarks (macOS, PHP 8.4, Apple Silicon):

```
WriteBuffer: 9.93 μs/op
ReadBuffer:  5.50 μs/op

Person struct (Standard): 21 bytes
Person struct (Final):    13 bytes (38% smaller)

Vector<String> [100 elements]:
- Standard: ~4.2 KB
- Final:    ~2.8 KB (33% smaller)
```

## Cross-Platform Compatibility

Binary format is 100% compatible with:
- Rust implementation (panilux/fbe-rust)
- Python implementation (official FBE)
- C++ implementation (official FBE)

All implementations follow the FBE specification exactly.
