<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group([
    'prefix' => env('APP_API_PREFIX', '')
], function ($router) {
    include base_path('app/Api/V1/routes.php');
});

/*-------------------------
   T E S T S  Routes
-------------------------- */
$router->group([
    'prefix' => env('APP_API_PREFIX', '') . '/tests'
], function ($router) {
    $router->get('db-test', function () {
        if (DB::connection()->getDatabaseName()) {
            echo "Connected successfully to database: " . DB::connection()->getDatabaseName();
        }


        \PubSub::publish('PaymentUpdateRequest', [
            'status' => 'succeeded',
            'payment_order_id' => '00000000-0000-0000-0000-000000000000',
            'document_id' => "97014c52-027c-4eff-abb3-3a6349d26aa6",
            'document_object' => 'Purchase',
            'document_meta' => ''
        ], 'Local.CryptoLaunchpadMS');


    });
});
