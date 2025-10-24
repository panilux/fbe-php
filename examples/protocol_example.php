<?php

declare(strict_types=1);

/**
 * FBE Protocol Example: Trading System
 *
 * Demonstrates Sender/Receiver pattern for network communication
 *
 * Generated with: ./bin/fbec-v2 test_schema.fbe test_generated/ --proto
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load generated files
foreach (glob(__DIR__ . '/../test_generated/*.php') as $file) {
    require_once $file;
}

use FBE\Common\{WriteBuffer, ReadBuffer};
use Com\Example\Trading\{TradingSender, TradingReceiver, AccountFinalModel, OrderFinalModel, TradeFinalModel, OrderSide};

// =============================================================================
// STEP 1: Implement Custom Sender
// =============================================================================

class MyTradingSender extends TradingSender
{
    private array $sentMessages = [];

    protected function onSend(string $data, int $size): int
    {
        // In real application: send over socket, pipe, etc.
        // For demo: store in memory
        $this->sentMessages[] = $data;
        echo "📤 Sender: Sent $size bytes\n";
        return $size;
    }

    public function getLastSent(): ?string
    {
        return end($this->sentMessages) ?: null;
    }
}

// =============================================================================
// STEP 2: Implement Custom Receiver
// =============================================================================

class MyTradingReceiver extends TradingReceiver
{
    private array $receivedAccounts = [];
    private array $receivedOrders = [];
    private array $receivedTrades = [];

    protected function onReceiveAccount(AccountFinalModel $model): void
    {
        $username = $model->username()->get();
        $balance = $model->balance()->get();

        $this->receivedAccounts[] = [
            'username' => $username,
            'balance' => $balance,
        ];

        echo "📥 Receiver: Got Account - Username: $username, Balance: \$$balance\n";
    }

    protected function onReceiveOrder(OrderFinalModel $model): void
    {
        $symbol = $model->symbol()->get();
        $price = $model->price()->get();
        $quantity = $model->quantity()->get();

        $this->receivedOrders[] = [
            'symbol' => $symbol,
            'price' => $price,
            'quantity' => $quantity,
        ];

        echo "📥 Receiver: Got Order - $symbol @ \$$price x $quantity\n";
    }

    protected function onReceiveTrade(TradeFinalModel $model): void
    {
        $symbol = $model->symbol()->get();
        $price = $model->price()->get();
        $quantity = $model->quantity()->get();

        $this->receivedTrades[] = [
            'symbol' => $symbol,
            'price' => $price,
            'quantity' => $quantity,
        ];

        echo "📥 Receiver: Got Trade - $symbol @ \$$price x $quantity\n";
    }

    public function getReceivedAccounts(): array { return $this->receivedAccounts; }
    public function getReceivedOrders(): array { return $this->receivedOrders; }
    public function getReceivedTrades(): array { return $this->receivedTrades; }
}

// =============================================================================
// STEP 3: Create and Send Messages
// =============================================================================

echo "=== FBE Protocol Example: Trading System ===\n\n";

// Create sender and receiver
$sender = new MyTradingSender();
$receiver = new MyTradingReceiver();

// --- Send Account Message ---
echo "--- Sending Account Message ---\n";
$buffer1 = new WriteBuffer();
$buffer1->allocate(200);

$account = new AccountFinalModel($buffer1, 0);
$account->id()->set(1001);
$account->username()->set('alice123');
$account->balance()->set(10000.50);
$account->createdAt()->set(time() * 1000000000); // nanoseconds

// Important: Sender will copy model data starting from model offset
// Model contains: [id][username size + data][balance][timestamp]
$sender->sendAccount($account);

// Feed to receiver
$receiver->receive($sender->getLastSent());
echo "\n";

// --- Send Order Message ---
echo "--- Sending Order Message ---\n";
$buffer2 = new WriteBuffer();
$buffer2->allocate(200);

$order = new OrderFinalModel($buffer2, 0);
$order->orderId()->set(5001);
$order->accountId()->set(1001);
$order->symbol()->set('AAPL');
$order->side()->set(OrderSide::Buy->value);
$order->price()->set(150.25);
$order->quantity()->set(100.0);

$sender->sendOrder($order);
$receiver->receive($sender->getLastSent());
echo "\n";

// --- Send Trade Message ---
echo "--- Sending Trade Message ---\n";
$buffer3 = new WriteBuffer();
$buffer3->allocate(200);

$trade = new TradeFinalModel($buffer3, 0);
$trade->tradeId()->set(9001);
$trade->orderId()->set(5001);
$trade->symbol()->set('AAPL');
$trade->price()->set(150.25);
$trade->quantity()->set(100.0);
$trade->executedAt()->set(time() * 1000000000);

$sender->sendTrade($trade);
$receiver->receive($sender->getLastSent());
echo "\n";

// =============================================================================
// STEP 4: Verify Results
// =============================================================================

echo "--- Verification ---\n";
echo "Received Accounts: " . count($receiver->getReceivedAccounts()) . "\n";
echo "Received Orders: " . count($receiver->getReceivedOrders()) . "\n";
echo "Received Trades: " . count($receiver->getReceivedTrades()) . "\n";
echo "\n";

assert(count($receiver->getReceivedAccounts()) === 1, "Should receive 1 account");
assert(count($receiver->getReceivedOrders()) === 1, "Should receive 1 order");
assert(count($receiver->getReceivedTrades()) === 1, "Should receive 1 trade");

echo "✅ All protocol tests passed!\n\n";

echo "=== Protocol Pattern Benefits ===\n";
echo "✓ Type-safe message sending (sendAccount, sendOrder, sendTrade)\n";
echo "✓ Automatic message dispatching by type ID\n";
echo "✓ Clean separation: transport (onSend) vs business logic (onReceive*)\n";
echo "✓ Handles fragmented/multiple messages automatically\n";
echo "✓ Easy to extend: add new message types in .fbe schema\n";
