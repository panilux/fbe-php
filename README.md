# FBE PHP - Fast Binary Encoding for PHP

High-performance binary serialization library for PHP, fully compatible with the [Fast Binary Encoding](https://github.com/chronoxor/FastBinaryEncoding) specification.

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-blue.svg)](https://php.net)

## Features

- ✅ **Complete FBE Specification** - 100% alignment with official FBE
- ✅ **All Data Types** - Primitives, complex types, collections, optionals
- ✅ **Struct Inheritance** - Multi-level inheritance support
- ✅ **Versioning** - Model/FinalModel for protocol evolution
- ✅ **Type Safe** - Full PHP 8.1+ type declarations
- ✅ **High Performance** - Optimized binary serialization
- ✅ **Cross-Platform** - Binary compatible with Rust, Python, C++, etc.

## Installation

### Via Composer

```bash
composer require panilux/fbe-php
```

### Manual Installation

```bash
git clone https://github.com/panilux/fbe-php.git
cd fbe-php
```

## Quick Start

### Define Your Structs

```php
<?php

use FBE\WriteBuffer;
use FBE\ReadBuffer;

class Order
{
    public int $id;
    public string $symbol;
    public float $price;
    public int $quantity;

    public function __construct()
    {
        $this->id = 0;
        $this->symbol = '';
        $this->price = 0.0;
        $this->quantity = 0;
    }
}
```

### Serialize

```php
// Create order
$order = new Order();
$order->id = 123;
$order->symbol = "AAPL";
$order->price = 150.50;
$order->quantity = 100;

// Serialize
$buffer = new WriteBuffer();
$buffer->writeInt32(0, $order->id);
$buffer->writeString(4, $order->symbol);
$buffer->writeDouble(8 + strlen($order->symbol), $order->price);
$buffer->writeInt32(16 + strlen($order->symbol), $order->quantity);

// Get binary data
$binary = $buffer->data();
```

### Deserialize

```php
// Create read buffer
$buffer = new ReadBuffer($binary);

// Deserialize
$order = new Order();
$order->id = $buffer->readInt32(0);
$order->symbol = $buffer->readString(4);
$order->price = $buffer->readDouble(8 + strlen($order->symbol));
$order->quantity = $buffer->readInt32(16 + strlen($order->symbol));
```

## Supported Types

### Base Types (14)
- `bool` - Boolean (1 byte)
- `byte` - Unsigned byte (1 byte)
- `char`, `wchar` - Character (1/4 bytes)
- `int8`, `uint8` - 8-bit integers
- `int16`, `uint16` - 16-bit integers
- `int32`, `uint32` - 32-bit integers
- `int64`, `uint64` - 64-bit integers
- `float` - 32-bit floating point
- `double` - 64-bit floating point

### Complex Types (5)
- `bytes` - Binary data
- `decimal` - High-precision decimal (16 bytes)
- `string` - UTF-8 string
- `timestamp` - Unix timestamp (8 bytes)
- `uuid` - UUID (16 bytes)

### Collections (5)
- `array` - Fixed-size array
- `vector` - Dynamic array
- `list` - Linked list
- `map` - Ordered map
- `hash` - Hash map

### Advanced Features
- **Optional Types** - Nullable values with `?` syntax
- **Enums** - Simple and typed enumerations
- **Flags** - Bitwise flags
- **Structs** - Complex data structures
- **Inheritance** - Multi-level struct inheritance
- **Struct Keys** - Hash map keys with `[key]` attribute
- **Default Values** - Field defaults with `= value` syntax
- **Model/FinalModel** - Versioning support

## Advanced Usage

### Struct Inheritance

```php
class Person
{
    public string $name;
    public int $age;
}

class Employee extends Person
{
    public string $company;
    public float $salary;
}

class Manager extends Employee
{
    public int $teamSize;
}
```

### Struct Keys

```php
class Order
{
    public int $id;      // [key]
    public string $symbol;
    public float $price;

    public function getKey(): int
    {
        return $this->id;
    }

    public function equals(Order $other): bool
    {
        return $this->id === $other->id;
    }
}

// Use in hash map
$orders = [];
$orders[$order->getKey()] = $order;
```

### Default Values

```php
class Config
{
    public int $timeout = 30;
    public string $name = "Default";
    public bool $enabled = true;
    public float $threshold = 0.95;
}
```

### Model vs FinalModel

**Model** - With 4-byte size header (versioning support):
```php
use FBE\StructModel;

$model = new ProductModel();
$size = $model->serialize($product);  // Includes 4-byte header
```

**FinalModel** - Without header (maximum performance):
```php
use FBE\StructFinalModel;

$finalModel = new ProductFinalModel();
$size = $finalModel->serialize($product);  // No header, compact
```

## Binary Format

### Model (Versioned)
```
[4-byte size][struct data]
Example: 1e 00 00 00 7b 00 00 00 ... (30 bytes)
         ^header      ^data
```

### FinalModel (Compact)
```
[struct data]
Example: 7b 00 00 00 ... (26 bytes)
         ^data only
```

## Cross-Platform Compatibility

FBE PHP is 100% binary compatible with:
- ✅ FBE Rust (panilux/fbe-rust)
- ✅ FBE Python (official implementation)
- ✅ FBE C++ (official implementation)
- ✅ FBE C# (official implementation)
- ✅ FBE Go (official implementation)
- ✅ FBE Java (official implementation)

## Performance

- **Serialization:** ~1M operations/sec (typical struct)
- **Binary Size:** Minimal overhead (4 bytes for Model, 0 for FinalModel)
- **Memory:** Efficient dynamic allocation

## Requirements

- PHP 8.1 or higher
- Extensions: `mbstring` (for string handling)

## Testing

```bash
# Run all tests
php test_types.php
php test_collections.php
php test_inheritance.php
php test_keys.php
php test_defaults.php
php test_model.php

# Run cross-platform tests
php test_inheritance_cross.php
php test_keys_cross.php
php test_defaults_cross.php
php test_model_cross.php
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- Based on [Fast Binary Encoding](https://github.com/chronoxor/FastBinaryEncoding) by Ivan Shynkarenka
- Developed for [Panilux](https://panilux.com)
