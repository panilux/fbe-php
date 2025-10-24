# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

FBE (Fast Binary Encoding) for PHP - A **production-grade, rock-solid** binary serialization library with 100% compliance to the [Fast Binary Encoding](https://github.com/chronoxor/FastBinaryEncoding) specification. This implementation is cross-platform compatible with Rust, Python, C++, and other FBE implementations.

**Critical:** This is a PHP 8.4+ project that uses modern PHP features including property hooks and readonly properties.

**Status:** V2 production-grade implementation complete with 159 tests, 487 assertions, full FBE spec compliance, and security hardening.

**Performance:** 5-10 μs/op (10x faster than v1), bounds checking on all operations, 20-38% size reduction with Final format.

## Build & Test Commands

### Running Tests
```bash
# Run all V2 tests (RECOMMENDED - 159 tests, 487 assertions)
vendor/bin/phpunit tests/V2/ --colors=always --testdox

# Run V2 unit tests
vendor/bin/phpunit tests/V2/Unit/

# Run V2 integration tests
vendor/bin/phpunit tests/V2/Integration/

# Run all tests (including legacy v1)
composer test

# Run with coverage
composer test:coverage
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

## Core Architecture (V2 Production-Grade)

### Directory Structure

```
src/FBE/V2/
├── Common/                  # Shared base classes
│   ├── Buffer.php          # Base buffer with bounds checking
│   ├── WriteBuffer.php     # Write operations (9.93 μs/op)
│   ├── ReadBuffer.php      # Read operations (5.50 μs/op)
│   ├── FieldModel.php      # Base for field models
│   └── StructModel.php     # Base for struct models
├── Standard/                # Standard format (pointer-based, versioning)
│   ├── FieldModel*.php     # All field models
│   └── ...
├── Final/                   # Final format (inline, compact)
│   ├── FieldModel*.php     # All field models
│   └── ...
├── Types/                   # Complex types
│   ├── Uuid.php            # RFC 4122 big-endian ✅
│   └── Decimal.php         # 96-bit GMP precision ✅
└── Exceptions/              # Exception hierarchy
    ├── FBEException.php
    ├── BufferException.php
    └── BufferOverflowException.php
```

### Buffer System (Security Hardened)

**V2 introduces production-grade buffers with security-critical bounds checking:**

- **WriteBuffer** (`src/FBE/V2/Common/WriteBuffer.php`):
  - Performance: **9.93 μs/op** (10x faster than v1)
  - Bulk operations using `substr_replace`
  - Automatic buffer growth (2x expansion)
  - Bounds checking on EVERY write operation
  - Throws `BufferOverflowException` on overflow

- **ReadBuffer** (`src/FBE/V2/Common/ReadBuffer.php`):
  - Performance: **5.50 μs/op** (5x faster than v1)
  - Immutable, zero-copy reads
  - Bounds checking on EVERY read operation
  - Protection against malicious size values
  - Security-critical validation

### Serialization Patterns (V2)

**V2 uses TWO distinct serialization formats:**

#### 1. Standard Format (Pointer-Based, Versioning Support)

```php
// Namespace: FBE\V2\Standard\
// Format: [4-byte struct size header][fields with pointers]

class PersonModel extends StructModel {
    public function writeHeader(): void {
        $this->buffer->writeUInt32($this->offset, STRUCT_SIZE);
    }

    public function name(): FieldModelString {
        // String uses 4-byte pointer → data
        return new FieldModelString($this->buffer, $this->offset + 4);
    }
}
```

**Use when:**
- Forward/backward compatibility needed
- Protocol versioning required
- Schema evolution expected

**Size:** Larger (pointers + headers)

#### 2. Final Format (Inline, Maximum Compactness)

```php
// Namespace: FBE\V2\Final\
// Format: [fields inline, no header]

class PersonFinalModel extends StructModel {
    public function name(): FieldModelString {
        // String is inline: [4-byte size + data]
        return new FieldModelString($this->buffer, $this->offset);
    }
}
```

**Use when:**
- Binary size is critical
- No versioning needed
- Maximum performance required

**Size:** 20-38% smaller than Standard

**Example Comparison:**
```
Person {name: "Alice", age: 30}
├─ Standard: 21 bytes (header + pointers)
└─ Final:    13 bytes (inline) - 38% smaller
```

### FieldModel Pattern (Type-Safe Fields)

**V2 FieldModel classes are split by format:**

```
FBE\V2\Standard\          FBE\V2\Final\
├── FieldModelBool        ├── FieldModelBool
├── FieldModelInt32       ├── FieldModelInt32
├── FieldModelInt64       ├── FieldModelInt64
├── FieldModelFloat       ├── FieldModelFloat
├── FieldModelDouble      ├── FieldModelDouble
├── FieldModelString ★    ├── FieldModelString ★
├── FieldModelBytes ★     ├── FieldModelBytes ★
├── FieldModelUuid        ├── FieldModelUuid
├── FieldModelDecimal     ├── FieldModelDecimal
├── FieldModelTimestamp   ├── FieldModelTimestamp
├── FieldModelVector ★    ├── FieldModelVector ★
├── FieldModelOptional ★  ├── FieldModelOptional ★
├── FieldModelMap ★       ├── FieldModelMap ★
├── FieldModelSide        ├── FieldModelSide (enum)
└── FieldModelOrderStatus └── FieldModelOrderStatus (enum)
```

**★ = Different implementation between Standard/Final**

#### Key Methods:
- `size()`: Size in bytes (varies by format)
- `extra()`: Extra data size (only Standard format)
- `total()`: size() + extra()
- `get()/set()`: Type-safe operations

#### Format Differences:

**Primitives (Bool, Int, Float, Double):**
- Standard: Inline (same size)
- Final: Inline (same size)
- **No difference** (no versioning needed)

**String/Bytes:**
- Standard: Pointer-based (4-byte pointer → data)
- Final: Inline (4-byte size + data)
- **Final is more compact**

**Vector<T>:**
- Standard: Pointer-based ([4-byte pointer] → [4-byte count + elements])
- Final: Inline ([4-byte count + elements])
- **Final saves 4 bytes (no pointer)**

**Map<K,V>:**
- Standard: Pointer-based ([4-byte pointer] → [4-byte count + key-value pairs])
- Final: Inline ([4-byte count + key-value pairs])
- **Final saves 4 bytes (no pointer)**

**Optional<T>:**
- Standard: [1-byte flag + pointer or value]
- Final: [1-byte flag + inline value]
- **Final is more compact for strings**

**Enum:**
- Standard: Inline (underlying type: int8, int16, int32, etc.)
- Final: Inline (identical to Standard)
- **No difference** (enums are always fixed-size)

### FBE Binary Format Rules (V2)

#### Fixed-Size Types (Always Inline)
Primitives stored directly at offset (same in both formats):
- **bool**: 1 byte
- **int8/uint8**: 1 byte
- **int16/uint16**: 2 bytes (little-endian)
- **int32/uint32**: 4 bytes (little-endian)
- **int64/uint64**: 8 bytes (little-endian)
- **float**: 4 bytes (IEEE 754)
- **double**: 8 bytes (IEEE 754)
- **timestamp**: 8 bytes (nanoseconds since epoch)
- **uuid**: 16 bytes (big-endian, RFC 4122) ✅ FIXED
- **decimal**: 16 bytes (96-bit GMP, .NET compatible) ✅ FIXED

#### Variable-Size Types (Format-Dependent)

**String/Bytes:**
- Standard: `[4-byte pointer] → [4-byte size][data]`
- Final: `[4-byte size][data]` (inline)

**Vector<T>:**
- Standard: `[4-byte pointer] → [4-byte count][elements]`
- Final: `[4-byte count][elements]` (inline)

**Optional<T>:**
- Standard: `[1-byte has_value][pointer or value]`
- Final: `[1-byte has_value][inline value or nothing]`

#### Size Examples:

```
String "Hello" (5 bytes):
├─ Standard: 4 (ptr) + 4 (size) + 5 (data) = 13 bytes
└─ Final:    4 (size) + 5 (data) = 9 bytes

Vector<Int32> [1,2,3]:
├─ Standard: 4 (ptr) + 4 (count) + 12 (data) = 20 bytes
└─ Final:    4 (count) + 12 (data) = 16 bytes

Map<String,Int32> {"x":10}:
├─ Standard: 4 (ptr) + 4 (count) + (4+1 + 4) = 17 bytes
└─ Final:    4 (count) + (4+1 + 4) = 13 bytes

Optional<Int32> 42:
├─ Standard: 1 (flag) + 4 (value) = 5 bytes
└─ Final:    1 (flag) + 4 (value) = 5 bytes (same)

Optional<String> "Hi":
├─ Standard: 1 (flag) + 4 (ptr) + 4 (size) + 2 (data) = 11 bytes
└─ Final:    1 (flag) + 4 (size) + 2 (data) = 7 bytes

Enum Side::Buy (int32):
├─ Standard: 4 bytes (inline)
└─ Final:    4 bytes (identical)

Enum OrderStatus::Pending (int8):
├─ Standard: 1 byte (inline, compact!)
└─ Final:    1 byte (identical)
```

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

V2 implementation follows the FBE specification exactly, ensuring cross-platform binary compatibility.

## Development Patterns

### Adding New Type Support

1. **Add buffer methods** to `src/FBE/V2/Common/ReadBuffer.php` and `WriteBuffer.php`:
```php
public function readNewType(int $offset): mixed { }
public function writeNewType(int $offset, mixed $value): void { }
```

2. **Create FieldModel** in both `src/FBE/V2/Standard/` and `src/FBE/V2/Final/`:
```php
final class FieldModelNewType extends FieldModel {
    public function size(): int { return N; }
    public function get(): mixed { }
    public function set(mixed $value): void { }
}
```

3. **Add tests** in `tests/V2/Unit/`:
   - Test Standard format serialization
   - Test Final format serialization
   - Test edge cases (empty, null, large values)

4. **Update fbec generator** (`bin/fbec`) if needed:
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

1. Create PHPUnit test file in `tests/V2/Unit/` or `tests/V2/Integration/`
2. Extend `PHPUnit\Framework\TestCase`
3. Use PHPUnit assertions for validation
4. Run tests with `vendor/bin/phpunit tests/V2/`
5. Aim for comprehensive test coverage

## V2 Implementation Status

### ✅ Completed (Production-Ready)

1. **Buffer System**
   - ✅ Bounds checking on ALL operations
   - ✅ 10x performance improvement (5-10 μs/op)
   - ✅ Security hardening with BufferOverflowException
   - ✅ Bulk operations using substr_replace

2. **FBE Spec Compliance**
   - ✅ UUID: Big-endian byte order (RFC 4122) - FIXED
   - ✅ Decimal: 96-bit GMP precision - FIXED
   - ✅ Timestamp: 64-bit nanoseconds
   - ✅ All primitive types

3. **FieldModel Classes (30+ types)**
   - ✅ Standard format (pointer-based)
   - ✅ Final format (inline)
   - ✅ Primitives: Bool, Int8-64, UInt8-64, Float, Double
   - ✅ Complex: String, Bytes, UUID, Decimal, Timestamp
   - ✅ Collections: Vector<T>
   - ✅ Optionals: Optional<T>

4. **StructModel Foundation**
   - ✅ Standard format with 4-byte header
   - ✅ Final format without header
   - ✅ Example implementations (Person, Order)

5. **Testing**
   - ✅ 104 tests, 273 assertions
   - ✅ Unit tests (98 tests)
   - ✅ Integration tests (6 tests)
   - ✅ Size comparison tests
   - ✅ Edge case coverage (empty, null)
   - ✅ Large vector tests (100 elements)

### 🚧 Pending (Future Enhancements)

1. **Collections**
   - ⏳ Map<K,V> FieldModel
   - ⏳ Array<T> FieldModel (fixed-size)
   - ⏳ Set<T> FieldModel

2. **Advanced Features**
   - ⏳ Enum FieldModel
   - ⏳ Flags FieldModel
   - ⏳ Message/Protocol support
   - ⏳ Sender/Receiver pattern

3. **Code Generation**
   - ⏳ Update fbec for V2 namespace
   - ⏳ Auto-generate Standard/Final models
   - ⏳ Schema evolution support

4. **Performance**
   - ⏳ Memory pool allocators
   - ⏳ Zero-copy optimizations
   - ⏳ SIMD for bulk operations

### ✅ V1 Code Removed

V1 legacy code has been removed from this branch. All development should use V2:
- `src/FBE/V2/Common/ReadBuffer.php` - Security-hardened with bounds checking
- `src/FBE/V2/Common/WriteBuffer.php` - 10x faster with bulk operations
- `src/FBE/V2/Standard/*` - Pointer-based format with versioning
- `src/FBE/V2/Final/*` - Inline format for maximum compactness

## File Structure

```
src/FBE/V2/                  # V2 Production-Grade Implementation
├── Common/                  # Shared base classes
│   ├── Buffer.php          # Base buffer with bounds checking
│   ├── WriteBuffer.php     # Write operations (9.93 μs/op)
│   ├── ReadBuffer.php      # Read operations (5.50 μs/op)
│   ├── FieldModel.php      # Base for field models
│   └── StructModel.php     # Base for struct models
├── Standard/                # Standard format (pointer-based)
│   ├── FieldModel*.php     # All field models
│   └── ...
├── Final/                   # Final format (inline, compact)
│   ├── FieldModel*.php     # All field models
│   └── ...
├── Types/                   # Complex types
│   ├── Uuid.php            # RFC 4122 big-endian
│   ├── Decimal.php         # 96-bit GMP precision
│   ├── Side.php            # Example enum
│   └── OrderStatus.php     # Example enum
├── Protocol/                # Message/Protocol support
│   ├── Message.php         # Base message class
│   ├── MessageRegistry.php # Type registry
│   ├── Sender.php          # Stream-based sender
│   ├── Receiver.php        # Buffered receiver
│   ├── ProtocolVersion.php # Semantic versioning
│   └── Messages/           # Example messages
│       ├── AgentHeartbeat.php
│       ├── PanelCommand.php
│       └── CommandResponse.php
└── Exceptions/              # Exception hierarchy
    ├── FBEException.php
    ├── BufferException.php
    └── BufferOverflowException.php

tests/V2/                    # PHPUnit test suite
├── Unit/                    # Unit tests (153 tests)
│   ├── WriteBufferTest.php
│   ├── ReadBufferTest.php
│   ├── UuidTest.php
│   ├── DecimalTest.php
│   ├── FieldModel*Test.php
│   ├── StructModelTest.php
│   └── Protocol/
│       ├── MessageTest.php
│       ├── MessageRegistryTest.php
│       ├── SenderReceiverTest.php
│       └── ProtocolVersionTest.php
└── Integration/             # Integration tests (6 tests)
    └── ComplexStructTest.php

bin/fbec                     # Code generator (PHP script)
```

## Important Constants

- **PHP Version**: 8.4+ required
- **Byte Order**: Little-endian (all integer types)
- **String Encoding**: UTF-8
- **Timestamp Base**: Nanoseconds since Unix epoch
- **UUID Format**: 16-byte binary, output as standard UUID string

## Performance Considerations

- V2 buffer operations use optimized bulk writes (`substr_replace`)
- WriteBuffer: 9.93 μs/op (10x faster than v1)
- ReadBuffer: 5.50 μs/op (8x faster than v1)
- WriteBuffer grows by 2x when capacity exceeded
- Bounds checking adds minimal overhead (< 5%)
- Final format is 20-38% smaller than Standard format
