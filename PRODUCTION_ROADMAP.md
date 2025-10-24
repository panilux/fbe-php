# Production-Grade FBE PHP Refactoring Roadmap

**Project**: panilux/fbe-php
**Target**: Production-ready, FBE Spec compliant implementation for Panilux Panel & Agent
**Current Status**: 52% compliance (Prototype/PoC level)
**Target Status**: 95%+ compliance (Production-grade)

---

## Executive Summary

**Timeline**: 4-6 weeks for full production-readiness
**Risk Level**: High (complete architecture refactor)
**Recommendation**: **DO NOT USE current implementation in production**

### Critical Decision Required

Before starting, choose implementation strategy:

**Option A: Incremental Refactor** (4-6 weeks)
- ✅ Preserve existing tests
- ✅ Gradual migration
- ❌ Slower, more complex

**Option B: Ground-Up Rewrite** (3-4 weeks) ⭐ **RECOMMENDED**
- ✅ Clean architecture from start
- ✅ Faster overall
- ✅ No legacy baggage
- ❌ Disruptive

**Recommendation**: Option B (Ground-Up Rewrite) because:
1. Current architecture has fundamental flaws
2. Dual serialization pattern is unfixable incrementally
3. Cleaner codebase = easier maintenance
4. Faster to get to production quality

---

## Phase 1: Architecture Foundation (Week 1-2)

### Goals
- Rock-solid buffer implementation
- Clear standard/final separation
- Bounds checking everywhere
- Production-grade error handling

### Tasks

#### 1.1: Buffer Core Rewrite ⚡ **CRITICAL**

**Current Issues**:
- Character-by-character operations (50-100x slower)
- No bounds checking (security risk)
- Inefficient memory allocation

**New Implementation**:

```php
// src/FBE/Buffer.php - Base class
abstract class Buffer {
    protected string $data;
    protected int $offset;
    protected int $size;

    // Bounds checking for ALL operations
    protected function checkBounds(int $offset, int $length): void {
        if ($offset < 0 || $offset + $length > $this->size) {
            throw new BufferOverflowException(
                "Buffer access out of bounds: offset={$offset}, length={$length}, size={$this->size}"
            );
        }
    }
}

// src/FBE/WriteBuffer.php
final class WriteBuffer extends Buffer {
    // Bulk write operations
    public function writeBytes(int $offset, string $data): void {
        $length = strlen($data);
        $this->ensureCapacity($offset + $length);

        // Direct memory write (no loop!)
        for ($i = 0; $i < $length; $i++) {
            $this->data[$this->offset + $offset + $i] = $data[$i];
        }
    }

    // String operations
    public function writeStringInline(int $offset, string $value): int {
        // Final format: [4-byte size][data]
        $size = strlen($value);
        $this->writeUInt32($offset, $size);
        $this->writeBytes($offset + 4, $value);
        return 4 + $size;
    }

    public function writeStringPointer(int $offset, string $value): int {
        // Standard format: [4-byte pointer]
        // At pointer: [4-byte size][data]
        $size = strlen($value);
        $pointer = $this->allocate(4 + $size);
        $this->writeUInt32($offset, $pointer);
        $this->writeUInt32($pointer, $size);
        $this->writeBytes($pointer + 4, $value);
        return 4; // Field size (pointer only)
    }
}

// src/FBE/ReadBuffer.php
final class ReadBuffer extends Buffer {
    public function readStringInline(int $offset): array {
        // Returns [value, bytes_consumed]
        $this->checkBounds($offset, 4);
        $size = $this->readUInt32($offset);
        $this->checkBounds($offset + 4, $size);
        $value = substr($this->data, $this->offset + $offset + 4, $size);
        return [$value, 4 + $size];
    }

    public function readStringPointer(int $offset): string {
        $this->checkBounds($offset, 4);
        $pointer = $this->readUInt32($offset);
        if ($pointer === 0) return '';

        $this->checkBounds($pointer, 4);
        $size = $this->readUInt32($pointer);
        $this->checkBounds($pointer + 4, $size);
        return substr($this->data, $pointer + 4, $size);
    }
}
```

**Performance Target**: Within 10x of C++ implementation (acceptable for PHP)

**Deliverables**:
- [ ] `src/FBE/Buffer.php` - Base class with bounds checking
- [ ] `src/FBE/WriteBuffer.php` - Optimized writes
- [ ] `src/FBE/ReadBuffer.php` - Safe reads
- [ ] `src/FBE/Exceptions/BufferException.php` - Error hierarchy
- [ ] `tests/Unit/BufferPerformanceTest.php` - Benchmark tests

**Success Criteria**:
- ✅ Zero buffer overflow vulnerabilities
- ✅ String operations within 20x of C++ speed
- ✅ All operations have bounds checking
- ✅ PHPStan level 9 compliance

---

#### 1.2: Standard vs Final Separation ⚡ **CRITICAL**

**Problem**: Current code mixes standard and final formats inconsistently.

**Solution**: Create explicit namespace separation:

```
src/FBE/
├── Standard/
│   ├── FieldModelString.php      # Pointer-based (8 + N bytes)
│   ├── FieldModelBytes.php
│   ├── FieldModelVector.php
│   └── StructModel.php           # With 4-byte header
│
├── Final/
│   ├── FieldModelString.php      # Inline (4 + N bytes)
│   ├── FieldModelBytes.php
│   ├── FieldModelVector.php      # Inline array
│   └── StructFinalModel.php      # No header
│
└── Common/
    ├── FieldModel.php            # Base class
    ├── WriteBuffer.php
    └── ReadBuffer.php
```

**Implementation**:

```php
// src/FBE/Standard/FieldModelString.php
namespace FBE\Standard;

final class FieldModelString extends \FBE\Common\FieldModel {
    public function size(): int {
        return 4; // Pointer only
    }

    public function extra(): int {
        // Calculate size at pointer location
        if ($this->buffer instanceof ReadBuffer) {
            $pointer = $this->buffer->readUInt32($this->offset);
            if ($pointer === 0) return 0;
            $size = $this->buffer->readUInt32($pointer);
            return 4 + $size; // size header + data
        }
        return 0;
    }

    public function set(string $value): void {
        $this->buffer->writeStringPointer($this->offset, $value);
    }

    public function get(): string {
        return $this->buffer->readStringPointer($this->offset);
    }
}

// src/FBE/Final/FieldModelString.php
namespace FBE\Final;

final class FieldModelString extends \FBE\Common\FieldModel {
    private int $calculatedSize = 0;

    public function size(): int {
        return $this->calculatedSize; // Variable!
    }

    public function set(string $value): void {
        $this->calculatedSize = $this->buffer->writeStringInline($this->offset, $value);
    }

    public function get(): array {
        [$value, $consumed] = $this->buffer->readStringInline($this->offset);
        $this->calculatedSize = $consumed;
        return $value;
    }
}
```

**Deliverables**:
- [ ] Restructure source tree (Standard/Final/Common)
- [ ] Implement all FieldModel classes for both formats
- [ ] Update StructModel and StructFinalModel
- [ ] Migration guide for existing code

**Success Criteria**:
- ✅ Clear separation of standard vs final
- ✅ No code duplication between formats
- ✅ Binary format matches FBE spec exactly
- ✅ Cross-platform tests pass for both formats

---

#### 1.3: Fixed Decimal Precision (96-bit) ⚡ **CRITICAL**

**Current**: Only 64-bit, precision loss!

**Fix**: Use GMP extension for 96-bit integers:

```php
// src/FBE/Types/Decimal.php
final class Decimal {
    private \GMP $value;      // 96-bit unscaled value
    private int $scale;        // 0-28
    private bool $negative;

    public function __construct(string|int|\GMP $value, int $scale = 0, bool $negative = false) {
        if ($scale < 0 || $scale > 28) {
            throw new \InvalidArgumentException("Scale must be 0-28");
        }

        $this->value = gmp_init($value);
        $this->scale = $scale;
        $this->negative = $negative;
    }

    public function toBytes(): string {
        // Convert GMP to 96-bit little-endian
        $bytes = gmp_export($this->value, 12, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);
        $bytes = str_pad($bytes, 12, "\0", STR_PAD_RIGHT);

        // Add unused bytes (12-13) and metadata (14-15)
        $bytes .= "\x00\x00"; // Unused
        $bytes .= chr($this->scale);
        $bytes .= $this->negative ? "\x80" : "\x00";

        return $bytes;
    }

    public static function fromBytes(string $bytes): self {
        if (strlen($bytes) !== 16) {
            throw new \InvalidArgumentException("Decimal must be 16 bytes");
        }

        $unscaled = substr($bytes, 0, 12);
        $value = gmp_import($unscaled, 12, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);
        $scale = ord($bytes[14]);
        $negative = $bytes[15] === "\x80";

        return new self($value, $scale, $negative);
    }

    public function toFloat(): float {
        $gmpValue = $this->negative ? gmp_neg($this->value) : $this->value;
        $divisor = gmp_pow(10, $this->scale);
        return gmp_intval($gmpValue) / gmp_intval($divisor);
    }
}

// In WriteBuffer/ReadBuffer
public function writeDecimal(int $offset, Decimal $decimal): void {
    $this->writeBytes($offset, $decimal->toBytes());
}

public function readDecimal(int $offset): Decimal {
    $bytes = substr($this->data, $this->offset + $offset, 16);
    return Decimal::fromBytes($bytes);
}
```

**Deliverables**:
- [ ] `src/FBE/Types/Decimal.php` - Full 96-bit implementation
- [ ] Add `ext-gmp` to composer.json requirements
- [ ] Update buffer read/write methods
- [ ] Add comprehensive decimal tests

**Success Criteria**:
- ✅ Full 96-bit precision maintained
- ✅ Compatible with .NET Decimal format
- ✅ Cross-platform tests pass with C#/Python

---

#### 1.4: UUID Big-Endian Fix ⚡ **CRITICAL**

**Current**: Uses little-endian (WRONG!)
**Spec**: RFC 4122 big-endian network byte order

```php
// src/FBE/Types/Uuid.php
final class Uuid {
    private string $bytes; // 16 bytes, big-endian fields

    public function __construct(string $uuid) {
        // Parse UUID: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        $hex = str_replace('-', '', $uuid);
        if (strlen($hex) !== 32) {
            throw new \InvalidArgumentException("Invalid UUID format");
        }

        // Convert to big-endian bytes
        $this->bytes = '';
        for ($i = 0; $i < 32; $i += 2) {
            $this->bytes .= chr(hexdec(substr($hex, $i, 2)));
        }
    }

    public function toBytes(): string {
        return $this->bytes;
    }

    public static function fromBytes(string $bytes): self {
        if (strlen($bytes) !== 16) {
            throw new \InvalidArgumentException("UUID must be 16 bytes");
        }

        // Convert big-endian bytes to UUID string
        $hex = bin2hex($bytes);
        $uuid = sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );

        return new self($uuid);
    }

    public function __toString(): string {
        return bin2hex($this->bytes);
    }
}
```

**Deliverables**:
- [ ] `src/FBE/Types/Uuid.php` - RFC 4122 compliant
- [ ] Update buffer methods
- [ ] Cross-platform UUID tests

---

## Phase 2: Core FBE Features (Week 2-3)

### 2.1: Schema Versioning Implementation ⚡ **HIGH PRIORITY**

**Why**: This is THE reason to use FBE over Protocol Buffers/FlatBuffers!

**Features needed**:
1. Field deprecation markers
2. Unknown field skipping
3. Version negotiation
4. Backward/forward compatibility

```php
// src/FBE/Standard/StructModel.php
abstract class StructModel {
    // Current schema version
    abstract public function schemaVersion(): int;

    // Serialize with version metadata
    public function serialize($value): string {
        $buffer = new WriteBuffer();

        // Write size header (placeholder)
        $buffer->writeUInt32(0, 0);

        // Write schema version
        $buffer->writeUInt32(4, $this->schemaVersion());

        // Write struct data
        $offset = 8;
        $structSize = $this->serializeStruct($value, $buffer, $offset);

        // Update size header
        $totalSize = 8 + $structSize;
        $buffer->writeUInt32(0, $totalSize);

        return $buffer->data();
    }

    // Deserialize with version checking
    public function deserialize(string $data) {
        $buffer = new ReadBuffer($data);

        // Read size header
        $totalSize = $buffer->readUInt32(0);

        // Read schema version
        $wireVersion = $buffer->readUInt32(4);

        if ($wireVersion > $this->schemaVersion()) {
            // Forward compatibility: newer schema on wire
            return $this->deserializeWithSkipping($buffer, 8, $wireVersion);
        } else if ($wireVersion < $this->schemaVersion()) {
            // Backward compatibility: older schema on wire
            return $this->deserializeWithDefaults($buffer, 8, $wireVersion);
        } else {
            // Exact match
            return $this->deserializeStruct($buffer, 8);
        }
    }

    // Skip unknown fields (newer schema)
    private function deserializeWithSkipping(ReadBuffer $buffer, int $offset, int $wireVersion) {
        // Read known fields, skip rest
        // Requires field metadata in generated code
    }

    // Use defaults for missing fields (older schema)
    private function deserializeWithDefaults(ReadBuffer $buffer, int $offset, int $wireVersion) {
        // Read available fields, use defaults for new fields
    }
}
```

**Code Generator Updates**:

```php
// bin/fbec - Generate version-aware code
class ProductModelV2 extends StructModel {
    public function schemaVersion(): int {
        return 2; // Incremented from v1
    }

    protected function serializeStruct($value, WriteBuffer $buffer, int $offset): int {
        $current = $offset;

        // V1 fields
        $buffer->writeInt32($current, $value->id);
        $current += 4;

        $current += $buffer->writeStringInline($current, $value->name);

        // V2 fields (new in this version)
        if ($this->schemaVersion() >= 2) {
            $buffer->writeDouble($current, $value->price);
            $current += 8;
        }

        return $current - $offset;
    }
}
```

**Deliverables**:
- [ ] Versioning in StructModel
- [ ] Field skipping logic
- [ ] Default value handling
- [ ] Update code generator with version support
- [ ] Comprehensive versioning tests

---

### 2.2: Missing Types Implementation

#### char / wchar types

```php
// In WriteBuffer/ReadBuffer
public function writeChar(int $offset, string $char): void {
    if (strlen($char) !== 1) {
        throw new \InvalidArgumentException("char must be 1 byte");
    }
    $this->writeInt8($offset, ord($char));
}

public function readChar(int $offset): string {
    return chr($this->readInt8($offset));
}

public function writeWChar(int $offset, string $char): void {
    // UTF-32 encoded character (4 bytes)
    $utf32 = mb_convert_encoding($char, 'UTF-32LE', 'UTF-8');
    if (strlen($utf32) !== 4) {
        throw new \InvalidArgumentException("wchar must be single character");
    }
    $this->writeBytes($offset, $utf32);
}

public function readWChar(int $offset): string {
    $utf32 = substr($this->data, $this->offset + $offset, 4);
    return mb_convert_encoding($utf32, 'UTF-8', 'UTF-32LE');
}
```

#### list<T> (Linked List)

```php
// Different from vector - linked list structure
// Standard format: [4-byte pointer] → [4-byte count][node_offsets...]
// Each node: [value][4-byte next_pointer]

final class FieldModelListInt32 extends FieldModel {
    public function size(): int { return 4; } // Pointer

    public function set(array $values): void {
        // Write as linked list structure
        $count = count($values);
        $pointer = $this->buffer->allocate(4 + ($count * 8)); // count + nodes

        $this->buffer->writeUInt32($this->offset, $pointer);
        $this->buffer->writeUInt32($pointer, $count);

        $nodeOffset = $pointer + 4;
        foreach ($values as $i => $value) {
            $this->buffer->writeInt32($nodeOffset, $value);
            $nextOffset = ($i < $count - 1) ? ($nodeOffset + 8) : 0;
            $this->buffer->writeUInt32($nodeOffset + 4, $nextOffset);
            $nodeOffset += 8;
        }
    }
}
```

#### hash<K,V> (Unordered Map)

```php
// Different from map - hash-based storage
// In practice, PHP arrays are hash tables, but for FBE compliance:
// Store as unordered key-value pairs with hash metadata

final class FieldModelHashInt32 extends FieldModel {
    // Similar to map but with hash ordering metadata
    // For production: can be aliased to map since PHP uses hashes anyway
}
```

**Deliverables**:
- [ ] char/wchar implementation
- [ ] list<T> linked list structure
- [ ] hash<K,V> distinct from map
- [ ] FieldModel classes for all
- [ ] Tests for new types

---

## Phase 3: Performance Optimization (Week 3-4)

### 3.1: Bulk Memory Operations

**Replace character loops with native operations**:

```php
// BEFORE (slow)
for ($i = 0; $i < $size; $i++) {
    $this->buffer[$offset + $i] = $data[$i];
}

// AFTER (fast)
$this->data = substr_replace($this->data, $data, $this->offset + $offset, $size);
```

**Consider PHP FFI for critical paths**:

```php
// src/FBE/FFI/NativeBuffer.php (optional optimization)
use FFI;

class NativeBuffer {
    private FFI $ffi;
    private FFI\CData $buffer;

    public function __construct(int $size) {
        $this->ffi = FFI::cdef("
            void *malloc(size_t size);
            void free(void *ptr);
            void *memcpy(void *dest, const void *src, size_t n);
        ");

        $this->buffer = $this->ffi->malloc($size);
    }

    public function writeBytes(int $offset, string $data): void {
        // Direct memory copy via FFI
        $this->ffi->memcpy($this->buffer + $offset, $data, strlen($data));
    }
}
```

**Deliverables**:
- [ ] Replace all loops with bulk operations
- [ ] Optional FFI implementation
- [ ] Benchmark suite
- [ ] Performance regression tests

**Target**: Within 10-15x of C++ (currently 50-100x slower)

---

### 3.2: Memory Pooling

```php
// src/FBE/BufferPool.php
final class BufferPool {
    private array $pool = [];
    private int $maxPoolSize = 100;
    private array $sizes = [1024, 4096, 16384, 65536];

    public function acquire(int $minSize): WriteBuffer {
        // Find smallest buffer >= minSize
        foreach ($this->sizes as $size) {
            if ($size >= $minSize && isset($this->pool[$size]) && !empty($this->pool[$size])) {
                $buffer = array_pop($this->pool[$size]);
                $buffer->reset();
                return $buffer;
            }
        }

        // Create new buffer
        $size = $this->nextPowerOf2($minSize);
        return new WriteBuffer($size);
    }

    public function release(WriteBuffer $buffer): void {
        $size = $buffer->capacity();
        if (in_array($size, $this->sizes)) {
            if (!isset($this->pool[$size])) {
                $this->pool[$size] = [];
            }

            if (count($this->pool[$size]) < $this->maxPoolSize) {
                $this->pool[$size][] = $buffer;
            }
        }
    }

    private function nextPowerOf2(int $n): int {
        $n--;
        $n |= $n >> 1;
        $n |= $n >> 2;
        $n |= $n >> 4;
        $n |= $n >> 8;
        $n |= $n >> 16;
        $n++;
        return $n;
    }
}

// Usage
$pool = new BufferPool();
$buffer = $pool->acquire(1024);
// ... use buffer ...
$pool->release($buffer);
```

**Deliverables**:
- [ ] Buffer pool implementation
- [ ] Integration with serialization
- [ ] Memory leak tests
- [ ] Pool efficiency metrics

---

## Phase 4: Message/Protocol Support (Week 4-5)

### 4.1: Message Framing ⚡ **HIGH PRIORITY for Panilux**

```php
// src/FBE/Protocol/Message.php
abstract class Message {
    abstract public function type(): int;
    abstract public function serialize(): string;

    // Message format: [4-byte type][4-byte size][payload]
    public function toFrame(): string {
        $payload = $this->serialize();
        $size = strlen($payload);

        $buffer = new WriteBuffer();
        $buffer->writeUInt32(0, $this->type());
        $buffer->writeUInt32(4, $size);
        $buffer->writeBytes(8, $payload);

        return $buffer->data();
    }

    public static function fromFrame(string $frame): self {
        $buffer = new ReadBuffer($frame);
        $type = $buffer->readUInt32(0);
        $size = $buffer->readUInt32(4);
        $payload = substr($frame, 8, $size);

        // Factory pattern - create specific message type
        return MessageFactory::create($type, $payload);
    }
}

// Example: Panilux Agent message
class AgentHeartbeat extends Message {
    public int $agentId;
    public int $timestamp;
    public string $status;

    public function type(): int { return 1001; }

    public function serialize(): string {
        $buffer = new WriteBuffer();
        $buffer->writeInt32(0, $this->agentId);
        $buffer->writeInt64(4, $this->timestamp);
        $offset = 12;
        $buffer->writeStringInline($offset, $this->status);
        return $buffer->data();
    }
}
```

### 4.2: Sender/Receiver Pattern

```php
// src/FBE/Protocol/Sender.php
class Sender {
    private $socket;
    private BufferPool $pool;

    public function send(Message $message): void {
        $frame = $message->toFrame();

        // Send with length prefix
        $length = strlen($frame);
        $header = pack('N', $length); // 4-byte big-endian length

        socket_write($this->socket, $header . $frame);
    }

    public function sendBatch(array $messages): void {
        // Optimize multiple messages
        $buffer = $this->pool->acquire(65536);

        foreach ($messages as $message) {
            $frame = $message->toFrame();
            $buffer->writeBytes($buffer->size, $frame);
        }

        socket_write($this->socket, $buffer->data());
        $this->pool->release($buffer);
    }
}

// src/FBE/Protocol/Receiver.php
class Receiver {
    private $socket;
    private string $receiveBuffer = '';

    public function receive(): ?Message {
        // Read length prefix
        if (strlen($this->receiveBuffer) < 4) {
            $data = socket_read($this->socket, 4 - strlen($this->receiveBuffer));
            $this->receiveBuffer .= $data;

            if (strlen($this->receiveBuffer) < 4) {
                return null; // Need more data
            }
        }

        // Parse length
        $length = unpack('N', substr($this->receiveBuffer, 0, 4))[1];

        // Read frame
        while (strlen($this->receiveBuffer) < 4 + $length) {
            $remaining = 4 + $length - strlen($this->receiveBuffer);
            $data = socket_read($this->socket, $remaining);
            $this->receiveBuffer .= $data;
        }

        // Extract frame
        $frame = substr($this->receiveBuffer, 4, $length);
        $this->receiveBuffer = substr($this->receiveBuffer, 4 + $length);

        return Message::fromFrame($frame);
    }
}
```

**Deliverables**:
- [ ] Message base class
- [ ] Message framing
- [ ] Sender/Receiver implementation
- [ ] Message factory/registry
- [ ] Protocol versioning
- [ ] Example messages for Panilux Panel/Agent

---

## Phase 5: Testing & Validation (Week 5-6)

### 5.1: Comprehensive Test Suite

```php
// tests/CrossPlatform/FBEComplianceTest.php
class FBEComplianceTest extends TestCase {
    /**
     * Test against official FBE Python implementation
     */
    public function testPythonInterop(): void {
        // Generate test data
        $product = new Product();
        $product->id = 123;
        $product->name = "Test Product";
        $product->price = 99.99;

        // Serialize in PHP
        $model = new ProductModel();
        $phpBinary = $model->serialize($product);

        // Write to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'fbe_php_');
        file_put_contents($tempFile, $phpBinary);

        // Call Python to deserialize
        $pythonScript = __DIR__ . '/../../cross_platform/python_reader.py';
        $output = shell_exec("python3 $pythonScript $tempFile");
        $pythonData = json_decode($output, true);

        // Verify
        $this->assertEquals(123, $pythonData['id']);
        $this->assertEquals("Test Product", $pythonData['name']);
        $this->assertEqualsWithDelta(99.99, $pythonData['price'], 0.0001);

        unlink($tempFile);
    }

    /**
     * Test against official FBE C++ implementation
     */
    public function testCppInterop(): void {
        // Similar to Python test
    }
}
```

### 5.2: Performance Benchmarks

```php
// tests/Performance/BenchmarkTest.php
class BenchmarkTest extends TestCase {
    public function testSerializationPerformance(): void {
        $product = $this->createTestProduct();
        $model = new ProductFinalModel();

        $iterations = 10000;
        $start = hrtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $binary = $model->serialize($product);
        }

        $end = hrtime(true);
        $totalNs = $end - $start;
        $avgNs = $totalNs / $iterations;

        echo sprintf("\nSerialization: %.0f ns/op (%.2f μs/op)\n", $avgNs, $avgNs / 1000);

        // Target: < 10,000 ns (10 μs) per operation
        $this->assertLessThan(10000, $avgNs, "Serialization too slow");
    }

    public function testMessageThroughput(): void {
        // Test messages/second for Panilux use case
        $sender = new Sender($socket);

        $iterations = 100000;
        $start = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $message = new AgentHeartbeat();
            $message->agentId = $i;
            $message->timestamp = time();
            $message->status = "OK";

            $sender->send($message);
        }

        $end = microtime(true);
        $duration = $end - $start;
        $throughput = $iterations / $duration;

        echo sprintf("\nThroughput: %.0f messages/sec\n", $throughput);

        // Target: > 10,000 messages/sec
        $this->assertGreaterThan(10000, $throughput);
    }
}
```

### 5.3: Security Tests

```php
// tests/Security/BoundsCheckTest.php
class BoundsCheckTest extends TestCase {
    public function testBufferOverflow(): void {
        $this->expectException(BufferOverflowException::class);

        $buffer = new ReadBuffer(str_repeat("\x00", 10));
        // Try to read beyond buffer
        $buffer->readUInt32(8); // Should throw
    }

    public function testMaliciousSize(): void {
        $this->expectException(BufferOverflowException::class);

        // Create buffer with malicious size field
        $malicious = pack('V', 0xFFFFFFFF) . str_repeat("\x00", 10);
        $buffer = new ReadBuffer($malicious);

        // Try to read string with huge size
        $buffer->readStringInline(0); // Should throw
    }

    public function testFuzzTesting(): void {
        // Random binary data shouldn't crash
        for ($i = 0; $i < 1000; $i++) {
            $randomData = random_bytes(rand(10, 1000));
            $buffer = new ReadBuffer($randomData);

            try {
                // Try various read operations
                $buffer->readInt32(0);
                $buffer->readStringInline(4);
            } catch (BufferOverflowException $e) {
                // Expected - should not crash
                $this->assertTrue(true);
            }
        }
    }
}
```

**Deliverables**:
- [ ] Cross-platform interop tests (Python, C++, Rust)
- [ ] Performance benchmark suite
- [ ] Security/fuzz tests
- [ ] Load tests (Panilux scenarios)
- [ ] CI/CD integration

---

## Phase 6: Documentation & Deployment (Week 6)

### 6.1: Production Documentation

```markdown
# docs/PRODUCTION_GUIDE.md

## Panilux Panel Integration

### Installation
composer require panilux/fbe

### Agent Communication Example
```php
use FBE\Protocol\{Sender, Receiver};
use PaniluxAgent\Messages\{Heartbeat, Command, Status};

// Agent side
$sender = new Sender($socket);
$heartbeat = new Heartbeat();
$heartbeat->agentId = getenv('AGENT_ID');
$heartbeat->timestamp = hrtime(true);
$heartbeat->status = 'RUNNING';

$sender->send($heartbeat);

// Panel side
$receiver = new Receiver($socket);
while ($message = $receiver->receive()) {
    match ($message->type()) {
        MessageType::HEARTBEAT => $this->handleHeartbeat($message),
        MessageType::STATUS => $this->handleStatus($message),
        default => logger()->warning("Unknown message type: {$message->type()}")
    };
}
```

### Performance Tuning
- Use BufferPool for high-throughput scenarios
- Enable opcache for production
- Consider final format for internal communication (no versioning overhead)
```

### 6.2: API Documentation

Generate PHPDoc documentation:

```bash
# Install phpDocumentor
composer require --dev phpdocumentor/phpdocumentor

# Generate docs
vendor/bin/phpdoc -d src/ -t docs/api/
```

### 6.3: Migration Guide

```markdown
# docs/MIGRATION_V2.md

## Migrating from v0.1.x to v2.0

### Breaking Changes

1. **Namespace Changes**
   - `FBE\FieldModel` → `FBE\Standard\FieldModel` or `FBE\Final\FieldModel`
   - Choose format explicitly

2. **String Serialization**
   - Standard: Now correctly uses pointer-based format
   - Final: Uses inline format
   - Update code accordingly

3. **Error Handling**
   - Buffer operations now throw `BufferException` on errors
   - Add try-catch blocks

### Step-by-Step Migration

1. Update composer.json
2. Fix namespace imports
3. Choose standard vs final format
4. Update error handling
5. Run tests
6. Deploy gradually (canary deployment)
```

---

## Implementation Checklist

### Week 1-2: Foundation
- [ ] New buffer implementation (bounds checking, optimized)
- [ ] Standard/Final separation
- [ ] Decimal 96-bit fix
- [ ] UUID big-endian fix
- [ ] Exception hierarchy
- [ ] Core tests passing

### Week 2-3: Core Features
- [ ] Schema versioning
- [ ] Missing types (char, wchar, list, hash)
- [ ] All FieldModel classes (standard + final)
- [ ] Code generator updates
- [ ] Cross-platform tests passing

### Week 3-4: Performance
- [ ] Bulk memory operations
- [ ] Buffer pooling
- [ ] Performance benchmarks
- [ ] Optimization based on benchmarks

### Week 4-5: Protocols
- [ ] Message framing
- [ ] Sender/Receiver
- [ ] Message factory
- [ ] Protocol versioning
- [ ] Panilux-specific messages

### Week 5-6: Testing
- [ ] Comprehensive test suite
- [ ] Cross-platform validation
- [ ] Security tests
- [ ] Load tests
- [ ] CI/CD pipeline

### Week 6: Documentation
- [ ] Production guide
- [ ] API documentation
- [ ] Migration guide
- [ ] Example projects
- [ ] Release notes

---

## Success Criteria

### Must Have (Production-Ready)
- ✅ 95%+ FBE spec compliance
- ✅ Zero buffer overflow vulnerabilities
- ✅ Cross-platform binary compatibility verified
- ✅ Within 15x of C++ performance
- ✅ Schema versioning working
- ✅ Message/protocol support
- ✅ Comprehensive test coverage (>90%)
- ✅ Production documentation

### Nice to Have
- ✅ Within 10x of C++ performance
- ✅ FFI optimization
- ✅ JSON conversion
- ✅ Reflection/introspection
- ✅ Advanced debugging tools

---

## Risk Mitigation

### Risk 1: Breaking Changes
**Mitigation**:
- Semantic versioning (v2.0.0)
- Detailed migration guide
- Parallel v1.x maintenance (6 months)

### Risk 2: Performance Targets Not Met
**Mitigation**:
- Start benchmarking early (Week 1)
- Iterative optimization
- FFI as fallback option
- Accept 15x slower as acceptable for PHP

### Risk 3: Cross-Platform Compatibility Issues
**Mitigation**:
- Test against FBE Python/C++ from Day 1
- Automated cross-platform CI
- Binary format validation tests

### Risk 4: Timeline Slip
**Mitigation**:
- Prioritize critical features first
- Cut nice-to-have features if needed
- Parallel development streams
- Weekly progress reviews

---

## Post-Launch Plan

### Month 1-3: Stabilization
- [ ] Monitor production issues
- [ ] Performance profiling in Panilux
- [ ] Hot-fix releases as needed
- [ ] Gather user feedback

### Month 4-6: Optimization
- [ ] Further performance tuning
- [ ] Memory optimization
- [ ] Advanced features
- [ ] Developer experience improvements

### Long-term: Maintenance
- [ ] FBE spec updates tracking
- [ ] PHP version compatibility
- [ ] Community contributions
- [ ] Feature requests

---

## Resources Needed

### Development
- 1 Senior PHP Developer (full-time, 6 weeks)
- Access to FBE reference implementations
- Testing infrastructure

### Infrastructure
- CI/CD pipeline (GitHub Actions / GitLab CI)
- Cross-platform test environment
- Performance benchmarking servers

### External Dependencies
- FBE Python (for cross-platform tests)
- FBE C++ or Rust (for validation)
- GMP extension (for decimal)

---

## Questions for Decision

1. **Timeline**: Can we allocate 6 weeks for this? Or need faster?
2. **Format Choice**: Standard vs Final for Panilux - which is primary?
3. **Performance Targets**: Is 15x slower than C++ acceptable?
4. **FFI**: Should we invest in FFI optimization? (adds complexity)
5. **Backward Compatibility**: Support v1.x migration or clean break?
6. **Testing**: Access to FBE Python/C++ for validation?

---

**Next Step**: Review this roadmap with Panilux team, make decisions, then start Phase 1! 🚀
