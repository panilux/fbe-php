# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

FBE (Fast Binary Encoding) for PHP - A high-performance binary serialization library that is 100% compatible with the [Fast Binary Encoding](https://github.com/chronoxor/FastBinaryEncoding) specification. This implementation is cross-platform compatible with Rust, Python, C++, and other FBE implementations.

**Critical:** This is a PHP 8.4+ project that uses modern PHP features including property hooks, asymmetric visibility (`public private(set)`), and readonly properties.

## Build & Test Commands

### Running Tests
```bash
# Run all tests via PHPUnit
composer test

# Run PHPUnit with coverage
composer test:coverage

# Run legacy test suite (all test*.php files)
php run-tests.php

# Run individual PHPUnit test suites
vendor/bin/phpunit tests/Unit          # Core buffer/field model tests
vendor/bin/phpunit tests/Integration   # Type/collection integration tests

# Run specific test file
php test_types.php
php test_collections.php
```

### Code Generation
```bash
# Generate PHP code from .fbe schema
bin/fbec schema.fbe output_directory/

# Example
bin/fbec examples/user.fbe generated/
```

### Dependencies
```bash
composer install          # Install dependencies
composer dump-autoload    # Regenerate autoloader
```

## Core Architecture

### Buffer System (Little-Endian Binary Format)

The foundation is a dual-buffer architecture:

- **WriteBuffer** (`src/FBE/WriteBuffer.php`): Write operations with dynamic growth
  - Uses property hooks for automatic size/offset validation
  - Methods: `write{Type}(offset, value)` for all FBE types
  - `allocate(size)` returns offset for dynamic structures
  - All writes are little-endian format

- **ReadBuffer** (`src/FBE/ReadBuffer.php`): Immutable read operations
  - Zero-copy reads from binary data
  - Methods: `read{Type}(offset)` for all FBE types
  - Handles pointer dereferencing for collections

### Serialization Patterns

There are THREE distinct serialization patterns in this codebase:

#### 1. Direct Serialization (Simple Structs)
Used in `test/` directory for basic examples:
```php
class User {
    public function serialize(WriteBuffer $buffer): int {
        $offset = 0;
        $buffer->writeInt32($offset, $this->id);
        $offset += 4;
        // ... sequential writes
        return $offset;
    }
}
```

#### 2. StructModel (Versioned with 4-byte Header)
Extends `src/FBE/StructModel.php` for protocol versioning:
```php
// Format: [4-byte size header][struct data]
abstract class StructModel {
    abstract protected function getStructSize($value): int;
    abstract protected function serializeStruct($value, WriteBuffer $buffer, int $offset): int;
    abstract protected function deserializeStruct(ReadBuffer $buffer, int $offset);
}
```
Use this when you need forward/backward compatibility.

#### 3. StructFinalModel (Compact, No Header)
Extends `src/FBE/StructFinalModel.php` for maximum performance:
```php
// Format: [struct data] (no header)
abstract class StructFinalModel {
    abstract protected function serializeStruct($value, WriteBuffer $buffer, int $offset): int;
    abstract protected function deserializeStruct(ReadBuffer $buffer, int $offset);
}
```
Use this when binary size is critical and versioning is not needed.

### FieldModel Pattern (Type-Safe Fields)

The FieldModel pattern (`src/FBE/FieldModel.php`) provides type-safe serialization:

- **Base class**: All field models extend `FieldModel`
- **Two implementations**:
  - `FieldModels.php`: Primitive types (Bool, Int8-64, UInt8-64, Float, Double, Timestamp, UUID, Bytes, Decimal)
  - `FieldModelCollections.php`: Collection types (Vector, Array, Map, Set for Int32 and String)

Key methods:
- `size()`: Fixed size in bytes (4 for pointers, actual size for inline types)
- `extra()`: Dynamic size for variable-length data (strings, collections)
- `get()/set()`: Type-safe read/write operations

**Important**: Field models store buffer reference + offset, enabling pointer-based serialization.

### FBE Binary Format Rules

#### Fixed-Size Types (Inline)
Primitives stored directly at offset:
- bool: 1 byte
- int8/uint8: 1 byte
- int16/uint16: 2 bytes (little-endian)
- int32/uint32: 4 bytes (little-endian)
- int64/uint64: 8 bytes (little-endian)
- float: 4 bytes (IEEE 754)
- double: 8 bytes (IEEE 754)
- timestamp: 8 bytes (nanoseconds since epoch)
- uuid: 16 bytes (binary format)
- decimal: 16 bytes (.NET Decimal format)

#### Variable-Size Types (Size-Prefixed)
Format: `[4-byte size][data]`
- string: `[uint32 length][UTF-8 bytes]`
- bytes: `[uint32 length][binary data]`

#### Collections (Pointer-Based)
Dynamic collections use pointer indirection:
- **Vector/Set format**: `[4-byte pointer] → [4-byte count][elements]`
- **Map format**: `[4-byte pointer] → [4-byte count][key-value pairs]`
- **Array format**: Inline storage (no pointer), fixed size

#### Optional Types
Format: `[1-byte has_value][4-byte pointer]`
- has_value = 0: null
- has_value = 1: pointer to actual data

### Code Generator (bin/fbec)

Parses `.fbe` schema files and generates PHP classes:

**Parser capabilities:**
- Enums: `enum Name : type { VALUE = n; }`
- Flags: `flags Name : type { FLAG = 0x01; }`
- Structs: `struct Name(id)? (: BaseStruct)? { fields }`
- Inheritance: Detects `: BaseClass` and generates `extends`
- Key fields: `[key]` attribute for hash map keys
- Default values: `field = value` syntax

**Generated code includes:**
- Public properties with PHP type hints
- Constructor with default initialization
- `serialize(WriteBuffer)` method
- `deserialize(ReadBuffer)` static method
- `getKey()` and `equals()` for structs with `[key]` fields

**Limitations:**
- Nested struct serialization is basic
- Collection field handling is simplified
- No FinalModel generation (manual only)

## Cross-Platform Compatibility

Binary format is 100% compatible with:
- Rust implementation (panilux/fbe-rust)
- Python implementation (official FBE)
- C++ implementation (official FBE)

**Cross-platform tests** (require Rust binaries):
- `test_inheritance_cross.php`
- `test_keys_cross.php`
- `test_defaults_cross.php`
- `test_model_cross.php`

These are skipped by default in `run-tests.php`.

## Development Patterns

### Adding New Type Support

1. **Add buffer methods** to `ReadBuffer.php` and `WriteBuffer.php`:
```php
public function readNewType(int $offset): mixed { }
public function writeNewType(int $offset, mixed $value): void { }
```

2. **Create FieldModel** in `FieldModels.php` or `FieldModelCollections.php`:
```php
final class FieldModelNewType extends FieldModel {
    public function size(): int { return N; }
    public function get(): mixed { }
    public function set(mixed $value): void { }
}
```

3. **Update fbec generator** (`bin/fbec`):
   - Add to `getWriteMethod()` and `getReadMethod()`
   - Add to `getTypeSize()`
   - Add to `mapFieldType()`

### Inheritance Chain

For multi-level inheritance (Person → Employee → Manager):
```php
class Employee extends Person {
    public function serialize(WriteBuffer $buffer): int {
        $offset = parent::serialize($buffer);
        // ... write Employee fields
        return $offset;
    }

    protected function deserializeFields(ReadBuffer $buffer): int {
        $offset = parent::deserializeFields($buffer);
        // ... read Employee fields
        return $offset;
    }
}
```

**Critical**: Use `deserializeFields()` (protected) for inheritance, not `deserialize()` (public static).

### Testing New Features

1. Create standalone test file: `test_feature.php`
2. Use `assert()` for validation
3. Exit with code 0 on success, 1 on failure
4. Add to `run-tests.php` if needed
5. Consider adding PHPUnit tests in `tests/` directory

## Known Issues & Limitations

### Critical Issues
1. **Decimal precision**: Only 64-bit support, not full 96-bit .NET Decimal
2. **Bounds checking**: Missing in most read operations (security concern)
3. **Error handling**: Minimal validation in buffer operations

### Missing FBE Features
- Message/Protocol support (core FBE feature)
- Sender/Receiver pattern
- Schema evolution/versioning (beyond basic Model/FinalModel)
- Memory pool allocators
- Zero-copy optimizations
- Linked list type (List vs Vector)
- True hash map (Hash vs Map)

### Code Generator Limitations
- Nested struct serialization is basic
- Collection serialization limited to simple cases
- No automatic FinalModel generation
- Cross-package references simplified

## File Structure

```
src/FBE/
├── ReadBuffer.php           # Binary reading (immutable)
├── WriteBuffer.php          # Binary writing (dynamic growth)
├── FieldModel.php           # Base class for field models
├── FieldModels.php          # Primitive type field models
├── FieldModelCollections.php # Collection type field models
├── FieldModelInt32.php      # Legacy int32 field model
├── FieldModelString.php     # Legacy string field model
├── StructModel.php          # Versioned serialization (with header)
├── StructFinalModel.php     # Compact serialization (no header)
└── Model.php                # Base model class

test/                        # Example structs for testing
├── User.php                 # Simple struct example
├── UserModel.php            # StructModel example
├── gen_inheritance/         # Generated inheritance examples
├── gen_keys/                # Generated key field examples
└── gen_defaults/            # Generated default value examples

tests/                       # PHPUnit test suite
├── Unit/                    # Buffer/FieldModel unit tests
└── Integration/             # Type/collection integration tests

bin/fbec                     # Code generator (PHP script)
```

## Important Constants

- **PHP Version**: 8.4+ required
- **Byte Order**: Little-endian (all integer types)
- **String Encoding**: UTF-8
- **Timestamp Base**: Nanoseconds since Unix epoch
- **UUID Format**: 16-byte binary, output as standard UUID string

## Performance Considerations

- Buffer operations use character-by-character writes (not optimized)
- Consider using `substr_replace` for bulk operations
- WriteBuffer grows by 2x when capacity exceeded
- No memory pooling (allocates on each operation)
- Cross-platform tests verify binary compatibility, not performance
