<?php

declare(strict_types=1);

namespace Com\Example\Trading;

use FBE\Proto\Sender;

/**
 * Protocol Sender for Trading
 * 
 * Sends messages over network/transport.
 * Override onSend() to implement actual transmission.
 */
abstract class TradingSender extends Sender
{

    /**
     * Send Account message
     * 
     * @param AccountFinalModel $model
     * @return int Bytes sent
     */
    public function sendAccount(AccountFinalModel $model): int
    {
        return $this->send($model, 1);
    }

    /**
     * Send Order message
     * 
     * @param OrderFinalModel $model
     * @return int Bytes sent
     */
    public function sendOrder(OrderFinalModel $model): int
    {
        return $this->send($model, 2);
    }

    /**
     * Send Trade message
     * 
     * @param TradeFinalModel $model
     * @return int Bytes sent
     */
    public function sendTrade(TradeFinalModel $model): int
    {
        return $this->send($model, 3);
    }
}
