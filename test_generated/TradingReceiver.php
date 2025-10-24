<?php

declare(strict_types=1);

namespace Com\Example\Trading;

use FBE\Proto\Receiver;
use FBE\Common\ReadBuffer;

/**
 * Protocol Receiver for Trading
 * 
 * Receives and dispatches messages.
 * Override onReceive*() methods to handle messages.
 */
abstract class TradingReceiver extends Receiver
{

    /**
     * Handle received Account message
     * 
     * @param AccountFinalModel $model
     */
    abstract protected function onReceiveAccount(AccountFinalModel $model): void;

    /**
     * Handle received Order message
     * 
     * @param OrderFinalModel $model
     */
    abstract protected function onReceiveOrder(OrderFinalModel $model): void;

    /**
     * Handle received Trade message
     * 
     * @param TradeFinalModel $model
     */
    abstract protected function onReceiveTrade(TradeFinalModel $model): void;

    /**
     * Dispatch received message by type ID
     */
    protected function onReceive(int $typeId, string $data, int $size): void
    {
        $buffer = new ReadBuffer($data);

        switch ($typeId) {
            case 1: // Account
                $model = new AccountFinalModel($buffer, 8); // Skip 8-byte header
                $this->onReceiveAccount($model);
                break;

            case 2: // Order
                $model = new OrderFinalModel($buffer, 8); // Skip 8-byte header
                $this->onReceiveOrder($model);
                break;

            case 3: // Trade
                $model = new TradeFinalModel($buffer, 8); // Skip 8-byte header
                $this->onReceiveTrade($model);
                break;

            default:
                // Unknown message type
                break;
        }
    }
}
