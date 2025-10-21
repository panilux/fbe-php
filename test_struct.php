<?php

require_once __DIR__ . '/src/FBE/WriteBuffer.php';
require_once __DIR__ . '/src/FBE/ReadBuffer.php';
require_once __DIR__ . '/src/FBE/FieldModel.php';
require_once __DIR__ . '/src/FBE/FieldModelInt32.php';
require_once __DIR__ . '/src/FBE/FieldModelString.php';
require_once __DIR__ . '/test/Side.php';
require_once __DIR__ . '/test/UserData.php';
require_once __DIR__ . '/test/UserModel.php';

use FBE\WriteBuffer;
use FBE\ReadBuffer;
use FBE\Test\User;
use FBE\Test\UserModel;
use FBE\Test\Side;

echo "Testing FBE struct-based serialization...\n\n";

// Test 1: Serialize User
echo "1. Testing User serialization...\n";
$user = new User(42, "Panilux", Side::Buy);

$buffer = new WriteBuffer();
$model = new UserModel($buffer);
$size = $model->serialize($user, $buffer);

echo "   Serialized size: {$size} bytes\n";
echo "   Binary: " . bin2hex($buffer->data()) . "\n";

// Test 2: Deserialize User
echo "\n2. Testing User deserialization...\n";
$reader = new ReadBuffer($buffer->data());
$model2 = new UserModel($reader);
$user2 = $model2->deserialize($reader);

echo "   Deserialized:\n";
echo "     id: {$user2->id}\n";
echo "     name: {$user2->name}\n";
echo "     side: {$user2->side->name}\n";

// Verify
assert($user2->id === $user->id, "ID mismatch!");
assert($user2->name === $user->name, "Name mismatch!");
assert($user2->side === $user->side, "Side mismatch!");

echo "\n✅ Struct-based serialization test passed!\n";

