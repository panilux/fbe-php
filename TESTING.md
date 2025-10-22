# FBE PHP Testing Guide

## Running Tests

### Quick Start

Run all tests:
```bash
php run-tests.php
```

Run a single test:
```bash
php test_types.php
```

### Test Categories

**Core Tests** (18 tests)
- Basic types and collections
- Field models
- Structs and inheritance
- Keys and defaults
- Model/FinalModel

**Cross-Platform Tests** (10 tests - require Rust binaries)
- Binary compatibility with Rust
- Skipped by default in `run-tests.php`

### Test Output

```
=== FBE PHP Test Runner ===

→ test_array.php ... ✓ PASS
→ test_types.php ... ✓ PASS
⊘ test_cross_platform_types.php (skipped - cross-platform/requires Rust)

=== Summary ===
Total: 28 tests
✓ Passed: 18
✗ Failed: 0
⊘ Skipped: 10

✓ All tests passed!
```

### Running Cross-Platform Tests

To run cross-platform tests, first generate Rust binaries:

```bash
# In fbe-rust directory
cargo test

# Then in fbe-php directory
php test_inheritance_cross.php
php test_keys_cross.php
php test_model_cross.php
```

### Using Composer

You can also run tests via Composer:

```bash
composer test
```

This runs the same `run-tests.php` script.

### CI/CD Integration

For continuous integration, use:

```bash
php run-tests.php
echo $?  # Exit code: 0 = success, 1 = failure
```

### Test Structure

Each test file:
- Is standalone (can run individually)
- Uses `assert()` for validation
- Exits with code 0 on success, 1 on failure
- Outputs human-readable results

### Adding New Tests

1. Create `test_yourfeature.php`
2. Use existing tests as template
3. Include required FBE classes
4. Write test logic with assertions
5. Run `php run-tests.php` to verify

### Troubleshooting

**"Failed to open stream"**
- Check file paths in `require_once`
- Should be `__DIR__ . '/src/FBE/ClassName.php'`

**"Type error" or "Undefined"**
- Ensure PHP 8.4+ is installed
- Check class names and namespaces

**Cross-platform tests fail**
- Generate Rust binaries first: `cd ../fbe-rust && cargo test`
- Check `/tmp/*.bin` files exist

## Test Coverage

- ✅ Base types (14)
- ✅ Complex types (5)
- ✅ Collections (5)
- ✅ Optional types
- ✅ Enums & Flags
- ✅ Structs
- ✅ Inheritance
- ✅ Struct keys
- ✅ Default values
- ✅ Model/FinalModel

**Total:** 18 core tests, 10 cross-platform tests

