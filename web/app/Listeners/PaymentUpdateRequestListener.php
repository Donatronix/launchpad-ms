<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class PaymentUpdateRequestListener
 *
 * @package App\Listeners
 */
class PaymentUpdateRequestListener
{
    /**
     * Handle the event.
     *
     * @param array $inputData
     *
     * @return void
     */
    public function handle(array $inputData)
    {
        // Logging income data
        if(env('APP_DEBUG')){
            Log::info($inputData);
        }

        // Do validate input data
        $validation = Validator::make($inputData, [
            'status' => 'string|required',
            'payment_order_id' => 'string|required',
            'document_id' => 'string|required',
            'document_object' => 'string|required',
            'document_meta' => 'sometimes',
        ]);

        // If validation error, the stop
        if ($validation->fails()) {
            Log::error('Validation error: ' . $validation->errors());
            exit();
        }

        // Try update document
        try {
            // Creating document reflection
            $reflection = new \ReflectionClass('App\Models\\' . $inputData['document_object']);

            // Try read document
            $document = $reflection->newInstance()->findOrFail($inputData['document_id']);

            // Update document data
            $document->fill([
                'status' => $reflection->getStaticPropertyValue('statuses')[$inputData['status']],
                'payment_order_id' => $inputData['payment_order_id']
            ]);
            $document->save();
        } catch (\Exception $e) {
            Log::info('Handler data error: ' . $e->getMessage());
            exit();
        }

        // If success operation, then handle specify document
        if($inputData['status'] === 'succeeded'){
            $documentHandler = app()->make(sprintf("App\Listeners\PaymentUpdate\%sUpdateHandler", $inputData['document_object']));
            $documentHandler::exec($document);

            //send notification
            $notificationHandler = app()->make(sprintf("App\Listeners\PaymentUpdate\SendNotificationListener"));
            $notificationHandler::exec($document);
        }
    }
}
