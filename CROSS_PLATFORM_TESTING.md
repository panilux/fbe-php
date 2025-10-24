# Cross-Platform Testing Guide

This guide shows how to verify FBE binary compatibility between PHP and Python implementations.

## 🎯 Purpose

Validate that FBE-PHP generates binary data that is 100% compatible with other FBE implementations (Python, Rust, C++).

## 📋 Prerequisites

**PHP Side:**
```bash
composer install
php --version  # PHP 8.4+ required
```

**Python Side:**
```bash
python3 --version  # Python 3.8+ recommended
# No external dependencies needed - uses built-in struct module
```

## 🚀 Quick Start

### Step 1: Generate Binary Data (PHP)

```bash
php examples/cross_platform_test.php
```

**Output:**
```
Writing primitives...
Writing string...
Writing bytes...
Writing UUID...
Writing decimal...
Writing timestamp...
Writing vector<int32>...
Writing vector<string>...
Writing optional<int32> with value...
Writing optional<string> null...

Total size: XXX bytes
Saved to: test_cross_platform.fbe

✅ PHP side complete! Now run Python side to verify.
```

### Step 2: Verify Binary Data (Python)

```bash
python3 examples/cross_platform_test.py
```

**Expected Output:**
```
🐍 Python Cross-Platform Verification

📦 Loaded XXX bytes from test_cross_platform.fbe

✓ Reading primitives...
  Int32: 42 (expected: 42)
  Int64: 9876543210 (expected: 9876543210)
  Float: 3.14159 (expected: ~3.14159)
  Double: 2.718281828459045 (expected: ~2.718281828459045)
  Bool: True (expected: True)

✓ Reading string...
  String: 'Hello from PHP FBE!' (expected: 'Hello from PHP FBE!')

✓ Reading bytes...
  Bytes: 00010203 04fffefd (expected: 00010203 04fffefd)

✓ Reading UUID...
  UUID: 550e8400-e29b-41d4-a716-446655440000

✓ Reading decimal...
  Decimal: <Decimal: ...>

✓ Reading timestamp...
  Timestamp: 1234567890123456789 (expected: 1234567890123456789)

✓ Reading vector<int32>...
  Vector<Int32>: [10, 20, 30, 40, 50] (expected: [10, 20, 30, 40, 50])

✓ Reading vector<string>...
  Vector<String>: ['apple', 'banana', 'cherry'] (expected: ['apple', 'banana', 'cherry'])

✓ Reading optional<int32>...
  Optional<Int32>: 999 (expected: 999)

✓ Reading optional<string> null...
  Optional<String>: None (expected: None)

✅ All checks passed! Total bytes read: XXX
🎉 PHP ↔ Python cross-platform compatibility verified!
```

## 📊 What Gets Tested

### ✅ Primitive Types
- **Int32** - 4-byte signed integer (little-endian)
- **Int64** - 8-byte signed integer (little-endian)
- **Float** - 4-byte IEEE 754 float
- **Double** - 8-byte IEEE 754 double
- **Bool** - 1-byte boolean

### ✅ Complex Types
- **String** - UTF-8 encoded string with pointer
- **Bytes** - Raw binary data with pointer
- **UUID** - 16-byte UUID (big-endian, RFC 4122)
- **Decimal** - 16-byte high-precision decimal (96-bit + scale)
- **Timestamp** - 8-byte nanosecond timestamp

### ✅ Collections
- **Vector\<Int32\>** - Dynamic array of integers
- **Vector\<String\>** - Dynamic array of strings

### ✅ Optional Types
- **Optional\<Int32\>** - Optional integer (with value)
- **Optional\<String\>** - Optional string (null)

## 🔍 Binary Format Details

### Standard Format (Used in Tests)

All tests use **FBE Standard Format** which includes:
- **Pointers** - 4-byte offsets for variable-size data
- **Size prefixes** - 4-byte lengths for strings/bytes/vectors
- **Little-endian** - All integers use little-endian byte order
- **Big-endian UUID** - UUID uses big-endian (RFC 4122 compliance)

Example layout for String "Hello":
```
Offset 0: [04 00 00 00]     <- Pointer to offset 4
Offset 4: [05 00 00 00]     <- String size: 5 bytes
Offset 8: [48 65 6C 6C 6F]  <- "Hello" (UTF-8)
```

## 🐛 Troubleshooting

### Python script fails with "file not found"
```bash
# Run PHP script first to generate test_cross_platform.fbe
php examples/cross_platform_test.php
```

### Assertion failures
```bash
# Check hex dump in PHP output
php examples/cross_platform_test.php | grep "^[0-9a-f]\{4\}:"

# Compare with Python expectations
# May indicate:
# - Byte order issue (little vs big endian)
# - Pointer calculation error
# - Size prefix mismatch
```

### Type mismatches
Common issues:
- **Float precision** - Float values may have small rounding errors (<0.001 tolerance)
- **String encoding** - Ensure UTF-8 encoding on both sides
- **Pointer offsets** - Standard format uses pointers, check buffer allocations

## 🔗 Official FBE Implementations

### Python
- **Official:** https://github.com/chronoxor/FastBinaryEncoding/tree/master/python
- **Install:** `pip install fbe`

### Rust
- **Panilux:** https://github.com/panilux/fbe-rust
- **Install:** `cargo add fbe-rust`

### C++
- **Official:** https://github.com/chronoxor/FastBinaryEncoding
- **Build:** CMake-based build system

## 📝 Adding New Test Cases

To add new types to cross-platform tests:

### 1. Update PHP Script

```php
// examples/cross_platform_test.php

use FBE\Standard\FieldModelYourType;

$yourField = new FieldModelYourType($buffer, $offset);
$yourField->set($value);
$offset += $yourField->total();
```

### 2. Update Python Script

```python
# examples/cross_platform_test.py

def read_your_type(self, offset: int) -> tuple[YourType, int]:
    """Read YourType (Standard format)"""
    # Implementation
    return (value, size)

# In verify_php_data():
your_val, your_size = buffer.read_your_type(offset)
assert your_val == expected_value
offset += your_size
```

### 3. Run Both Scripts

```bash
php examples/cross_platform_test.php
python3 examples/cross_platform_test.py
```

## 🎯 Best Practices

1. **Always test new types** - Add cross-platform test when implementing new FieldModels
2. **Use Standard format** - Standard format has better cross-platform compatibility
3. **Check byte order** - Ensure little-endian for integers, big-endian for UUID
4. **Test edge cases** - Empty strings, null optionals, zero-length vectors
5. **Verify hex dumps** - When in doubt, compare hex dumps between implementations

## 🚀 Next Steps

- Add Rust cross-platform tests (requires fbe-rust binary)
- Add C++ cross-platform tests (requires FastBinaryEncoding C++)
- Test Final format compatibility
- Add struct serialization tests
- Test enum and flags types

## 📄 Related Documentation

- [README.md](README.md) - Main project documentation
- [FBE_SPEC_COMPLIANCE.md](FBE_SPEC_COMPLIANCE.md) - Specification compliance details
- [PROTOCOL_USAGE.md](PROTOCOL_USAGE.md) - Network protocol usage
- [CLAUDE.md](CLAUDE.md) - Development guide

---

**Questions or issues?** https://github.com/panilux/fbe-php/issues
