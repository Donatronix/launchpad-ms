<?php

namespace App\Listeners\PaymentUpdate;

use Sumra\SDK\Facades\PubSub;

/**
 * Class DepositUpdateHandler
 *
 * @package App\Listeners
 */
class DepositUpdateHandler
{
    /**
     * @param $document
     */
    public static function exec($document){
        // Send request to wallet for update balance
        PubSub::publish('UpdateBalanceRequest', [
            'type' => 'charge',
            'amount' => $document->amount,
            'currency' => $document->currency_code,
            'user_id' => $document->user_id,
            'document_id' => $document->id,
            'document_object' => class_basename(get_class($document)),
            'document_service' => env('RABBITMQ_EXCHANGE_NAME')
        ], config('pubsub.queue.crypto_wallets'));
    }
}
