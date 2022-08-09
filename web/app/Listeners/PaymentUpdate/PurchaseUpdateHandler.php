<?php

namespace App\Listeners\PaymentUpdate;

use Sumra\SDK\Facades\PubSub;

/**
 * Class PurchaseUpdateHandler
 *
 * @package App\Listeners
 */
class PurchaseUpdateHandler
{
    /**
     * @param $document
     */
    public static function exec($document){
        // Get product
        $product = $document->product;

        // Send request to wallet for add token to user
        PubSub::publish('PurchaseToken', [
            'type' => 'charge',
            'amount' => $document->total_token,
            'token' => $product->ticker,
            'user_id' => $document->user_id,
            'document_id' => $document->id,
            'document_object' => class_basename(get_class($document)),
            'document_service' => env('RABBITMQ_EXCHANGE_NAME')
        ], config('pubsub.queue.crypto_wallets'));
    }
}
