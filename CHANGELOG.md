# Changelog

All notable changes to this project will be documented in this file.

## [0.0.7] - 2025-10-21

### Added
- **Complete FieldModel Library:** All primitive and complex type field models
  - Primitives: Bool, Int8-64, UInt8-64, Float, Double
  - Complex: Timestamp, UUID, Bytes, Decimal
- **Comprehensive Test Suite:** test_field_models.php testing all 16 field models
- **Modern PHP 8.4 Syntax:** Clean, type-safe implementations

### Verified
- ✅ All 16 field models working correctly
- ✅ 106 bytes serialization test passed
- ✅ Round-trip serialization/deserialization

## [0.0.6] - 2025-10-21

### Added
- **FieldModel Pattern:** Base class for type-safe field models
- **FieldModelInt32:** Int32 field model implementation
- **FieldModelString:** String field model implementation
- **Struct-based Serialization:** UserModel example with modern PHP 8.4 patterns
- **Readonly Data Classes:** Immutable User data class

### Improved
- Type-safe struct serialization/deserialization
- Cross-platform struct compatibility (PHP ↔ Rust)
- Modern PHP 8.4 readonly properties for data classes

### Verified
- ✅ Struct serialization: User(id, name, side)
- ✅ PHP → Rust: Binary identical
- ✅ Rust → PHP: Binary identical

## [0.0.5] - 2025-10-21

### Changed
- **PHP 8.4+ Modernization:** Complete refactor using modern PHP features
- **Property hooks:** `$size` and `$offset` with automatic validation
- **Asymmetric visibility:** `public private(set)` for immutable properties
- **Modern syntax:** Cleaner, more readable code
- **Breaking change:** `$buffer->size()` → `$buffer->size` (property access)

### Improved
- Better type safety with property hooks
- Automatic validation on property assignment
- More concise code with modern PHP 8.4 syntax
- Enhanced UUID validation with proper error handling

### Verified
- ✅ All tests passing with PHP 8.4.13
- ✅ Binary compatibility maintained with Rust
- ✅ Performance improvements from JIT optimizations

## [0.0.4] - 2025-10-21

### Added
- **vector<T>** collection support (dynamic arrays with pointer-based storage)
- **array[N]** collection support (fixed-size inline arrays)
- **map<K,V>** collection support (key-value pairs)
- **set<T>** collection support (unique values, same format as vector)
- Individual collection tests for each type
- Cross-platform vector test (PHP ↔ Rust)

### Implemented
- `writeVectorInt32()` / `readVectorInt32()` for dynamic arrays
- `writeArrayInt32()` / `readArrayInt32()` for fixed-size arrays
- `writeMapInt32()` / `readMapInt32()` for key-value maps
- `writeSetInt32()` / `readSetInt32()` for unique value sets

### Verified
- ✅ All collections working in PHP
- ✅ Cross-platform binary compatibility for individual collections
- ✅ Vector cross-platform test passed

### Note
- Combined collection tests require struct-based serialization pattern
- Current implementation supports i32 types, extensible to other types

## [0.0.3] - 2025-10-21

### Added
- **timestamp** type support (uint64, nanoseconds since epoch)
- **uuid** type support (16 bytes, standard UUID format)
- **bytes** type support (size-prefixed binary data)
- **decimal** type support (16 bytes, .NET Decimal format)
- Cross-platform type tests (PHP ↔ Rust)

### Verified
- ✅ All new types working in PHP
- ✅ Cross-platform binary compatibility with Rust
- ✅ Round-trip serialization for all types

## [0.0.2] - 2025-10-21

### Fixed
- **Critical:** Fixed WriteBuffer size tracking bug that prevented serialization from working
  - Removed duplicate private `allocate()` method that didn't update size
  - Added `ensureSpace()` helper method for proper buffer growth and size tracking
  - All write methods (writeBool, writeInt32, writeString, etc.) now correctly update buffer size
- Fixed ReadBuffer constructor to accept optional buffer parameter for easier initialization
- Fixed User test class to use int8 for Side enum (was incorrectly using int32)

### Added
- Cross-platform serialization tests (PHP ↔ Rust)
- WriteBuffer basic functionality test
- Examples directory with cross_test.php

### Verified
- ✅ PHP → PHP round-trip serialization
- ✅ Rust → Rust round-trip serialization  
- ✅ PHP → Rust cross-platform binary compatibility
- ✅ Rust → PHP cross-platform binary compatibility

## [0.0.1] - 2025-10-20

### Added
- Initial PHP FBE implementation
- WriteBuffer and ReadBuffer classes
- FieldModel base classes
- Basic type support (primitives, strings)
- PHP code generator (fbec)

