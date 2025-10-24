# FBE-PHP Protocol Usage Guide

This guide demonstrates how to use the FBE Protocol implementation for network communication.

## Overview

FBE-PHP provides two protocol implementations:

1. **FBE\Protocol** - Generic message-based protocol (recommended)
2. **FBE\Proto** - Native FBE StructModel integration

## FBE\Protocol - Generic Message Pattern

### 1. Define Your Message

```php
<?php

use FBE\Protocol\Message;
use FBE\Common\{WriteBuffer, ReadBuffer};

class MyMessage extends Message
{
    public int $id = 0;
    public string $data = '';
    public int $timestamp = 0;

    public function type(): int
    {
        return 2001; // Unique message type ID
    }

    public function serialize(): string
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(256);

        $buffer->writeInt32(0, $this->id);
        $buffer->writeInt64(4, $this->timestamp);
        $buffer->writeStringInline(12, $this->data);

        return $buffer->data();
    }

    public static function deserialize(string $data): static
    {
        $buffer = new ReadBuffer($data);

        $msg = new self();
        $msg->id = $buffer->readInt32(0);
        $msg->timestamp = $buffer->readInt64(4);
        [$msg->data] = $buffer->readStringInline(12);

        return $msg;
    }
}
```

### 2. Register Message Types

```php
<?php

use FBE\Protocol\MessageRegistry;

$registry = new MessageRegistry();
$registry->register(2001, MyMessage::class);
$registry->register(2002, AnotherMessage::class);
```

### 3. Send Messages

```php
<?php

use FBE\Protocol\Sender;

// Create socket connection
$socket = stream_socket_client('tcp://localhost:8080');
$sender = new Sender($socket);

// Create and send message
$message = new MyMessage();
$message->id = 123;
$message->data = 'Hello, FBE!';
$message->timestamp = hrtime(true);

$bytesSent = $sender->send($message);
echo "Sent {$bytesSent} bytes\n";

// Send multiple messages in batch (more efficient)
$messages = [
    $message1,
    $message2,
    $message3
];
$sender->sendBatch($messages);

$sender->close();
```

### 4. Receive Messages

```php
<?php

use FBE\Protocol\Receiver;

// Accept connection
$serverSocket = stream_socket_server('tcp://0.0.0.0:8080');
$clientSocket = stream_socket_accept($serverSocket);

$receiver = new Receiver($clientSocket, $registry);

// Receive messages
while ($message = $receiver->receive()) {
    match ($message->type()) {
        2001 => handleMyMessage($message),
        2002 => handleAnotherMessage($message),
        default => echo "Unknown message type\n"
    };
}

$receiver->close();
```

## FBE\Proto - StructModel Integration

### 1. Use with StructModel

```php
<?php

use FBE\Proto\{Sender, Receiver};
use FBE\Common\StructModel;

class MySender extends Sender
{
    public function __construct(
        private mixed $socket
    ) {
        parent::__construct();
    }

    protected function onSend(string $data, int $size): int
    {
        return socket_send($this->socket, $data, $size, 0);
    }
}

class MyReceiver extends Receiver
{
    public function __construct(
        private mixed $socket
    ) {
        parent::__construct();
    }

    protected function onReceive(string $data, int $size): int
    {
        return socket_recv($this->socket, $data, $size, 0);
    }
}

// Usage
$sender = new MySender($socket);
$sender->send($personModel, typeId: 1001);

$receiver = new MyReceiver($socket);
if ($message = $receiver->receive()) {
    $type = $message['type'];
    $data = $message['data'];
    // Deserialize data based on type
}
```

## Protocol Versioning

```php
<?php

use FBE\Protocol\ProtocolVersion;

// Get current version
$version = ProtocolVersion::current();
echo $version->toString(); // "1.0.0"

// Parse version string
$clientVersion = ProtocolVersion::parse("1.2.3");

// Check compatibility
if ($version->isCompatible($clientVersion)) {
    echo "Compatible!\n";
} else {
    echo "Incompatible protocol version\n";
}

// Compare versions
$result = $version->compare($clientVersion);
// $result: -1 (older), 0 (equal), 1 (newer)
```

## Wire Format

### Protocol\Message Frame Format

```
Wire Frame: [4-byte length (big-endian)][message frame]
Message Frame: [4-byte type][4-byte size][payload]
```

Example:
```
[00 00 00 10]  <- Length: 16 bytes
[00 00 07 D1]  <- Type: 2001
[00 00 00 04]  <- Size: 4 bytes
[00 00 00 7B]  <- Payload: int32(123)
```

### Proto Format

```
Message: [4-byte size][4-byte type_id][FBE payload]
```

## Security Features

- **Max message size**: 10 MB (configurable)
- **Bounds checking**: All read/write operations validated
- **Partial read handling**: Auto-buffering for incomplete messages
- **Type validation**: MessageRegistry ensures type safety

## Performance Tips

1. **Batch sending**: Use `sendBatch()` for multiple messages
2. **Buffer reuse**: Sender/Receiver reuse internal buffers
3. **Chunk size**: Adjust `readChunkSize` for your use case
4. **Non-blocking I/O**: Set streams to non-blocking mode

```php
stream_set_blocking($socket, false);
```

## Error Handling

```php
use FBE\Protocol\{Sender, Receiver};

try {
    $sender->send($message);
} catch (\RuntimeException $e) {
    echo "Send failed: {$e->getMessage()}\n";
}

try {
    $message = $receiver->receive();
} catch (\InvalidArgumentException $e) {
    echo "Invalid message: {$e->getMessage()}\n";
} catch (\RuntimeException $e) {
    echo "Receive failed: {$e->getMessage()}\n";
}
```

## Complete Example: Echo Server

```php
<?php

require 'vendor/autoload.php';

use FBE\Protocol\{Message, Sender, Receiver, MessageRegistry};
use FBE\Common\{WriteBuffer, ReadBuffer};

// Define message
class EchoMessage extends Message
{
    public string $text = '';

    public function type(): int { return 3000; }

    public function serialize(): string
    {
        $buffer = new WriteBuffer();
        $buffer->allocate(256);
        $buffer->writeStringInline(0, $this->text);
        return $buffer->data();
    }

    public static function deserialize(string $data): static
    {
        $buffer = new ReadBuffer($data);
        $msg = new self();
        [$msg->text] = $buffer->readStringInline(0);
        return $msg;
    }
}

// Server
$registry = new MessageRegistry();
$registry->register(3000, EchoMessage::class);

$server = stream_socket_server('tcp://0.0.0.0:8080');
echo "Echo server listening on port 8080...\n";

$client = stream_socket_accept($server);
$receiver = new Receiver($client, $registry);
$sender = new Sender($client);

while ($message = $receiver->receive()) {
    echo "Received: {$message->text}\n";

    // Echo back
    $sender->send($message);
}

$receiver->close();
$sender->close();
```

## Next Steps

- See `src/FBE/Protocol/Messages/` for example message implementations
- Check `tests/Unit/Protocol/` for comprehensive usage examples
- Read `CLAUDE.md` for architecture details
