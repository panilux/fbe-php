# FBE PHP Tests

PHPUnit test suite for Fast Binary Encoding (FBE) PHP implementation.

## Running Tests

```bash
# Run all tests
composer test

# Run with verbose output
vendor/bin/phpunit --verbose

# Run specific test suite
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpunit --testsuite=Integration

# Run specific test file
vendor/bin/phpunit tests/Unit/WriteBufferTest.php

# Run with coverage (requires xdebug)
composer test:coverage
```

## Test Structure

```
tests/
├── Unit/                    # Unit tests (isolated components)
│   ├── WriteBufferTest.php
│   └── ReadBufferTest.php
└── Integration/             # Integration tests (multiple components)
    ├── TypesTest.php
    ├── CollectionsTest.php
    └── OptionalTest.php
```

## Test Coverage

### Unit Tests
- **WriteBuffer** - All write methods (primitives, strings, collections)
- **ReadBuffer** - All read methods (primitives, strings, collections)

### Integration Tests
- **Types** - All primitive types round-trip
- **Collections** - Vectors, arrays, maps, sets
- **Optional** - Optional types (int32, string, double)

## Writing Tests

Example test:

```php
<?php

declare(strict_types=1);

namespace FBE\Tests\Unit;

use FBE\WriteBuffer;
use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function testSomething(): void
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(4);
        $buffer->writeInt32(0, 42);
        
        $this->assertEquals('2a000000', bin2hex($buffer->data()));
    }
}
```

## CI/CD

Tests run automatically on:
- Push to main branch
- Pull requests
- Manual workflow dispatch

Exit codes:
- `0` - All tests passed
- `1` - One or more tests failed

