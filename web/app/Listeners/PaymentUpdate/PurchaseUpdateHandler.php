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
    public static function exec($document) {
        // Get product
        $product = $document->product;

        /**
         * Increase total Purchased
         */
        $product->sold += $document->total_token;
        $product->save();

        // Send request to wallet for add token to user
        PubSub::publish('PurchaseTokenRequest', [
            'posting' => 'increase',
            'amount' => $document->total_token,
            'currency' => $product->ticker,
            'type' => 'main',
            'user_id' => $document->user_id,
            'document_id' => $document->id,
            'document_object' => class_basename(get_class($document)),
            'document_service' => env('RABBITMQ_EXCHANGE_NAME')
        ], config('pubsub.queue.crypto_wallets'));
    }
}
