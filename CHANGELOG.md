# Changelog

All notable changes to this project will be documented in this file.

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

