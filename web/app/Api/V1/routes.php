<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => env('APP_API_VERSION', ''),
    'namespace' => '\App\Api\V1\Controllers'
], function ($router) {
    /**
     * PUBLIC ACCESS
     */
    /**
     * Payments webhooks
     */
    $router->group([
        'prefix' => 'webhooks',
    ], function ($router) {
        $router->post('identify/{type}', 'IdentifyWebhookController');
//        $router->post('identify/events', 'IdentifyWebhookController@webhookEvents');
//        $router->post('identify/notifications', 'IdentifyWebhookController@webhookNotifications');
    });

    /**
     * Products
     */
    $router->group([
        'prefix'=> 'products',
    ], function ($router) {
        $router->get('/', 'ProductController@index');
        $router->get('/{id}', 'ProductController@show');
    });

    /**
     * PRIVATE ACCESS
     */
    $router->group([
        'middleware' => 'checkUser',
    ], function ($router) {
        /**
         * Token Rewards
         */
        $router->group([
            'prefix' => 'token-rewards',
        ], function ($router) {
            $router->get('/', 'TokenRewardController@index');
            $router->post('/', 'TokenRewardController@store');
            $router->put('/', 'TokenRewardController@update');
            $router->delete('/', 'TokenRewardController@destroy');
        });

        /**
         * Contributors
         */
        $router->group([
            'prefix' => 'contributors',
        ], function ($router) {
            $router->get('/', 'ContributorController@show');
            $router->post('/', 'ContributorController@store');
            $router->post('/identify', 'ContributorController@identifyStart');
            $router->put('/identify', 'ContributorController@update');
            $router->patch('/agreement', 'ContributorController@agreement');
        });

        /**
         * Prices
         */
        $router->group([
            'prefix' => 'prices',
        ], function ($router) {
            $router->get('/', 'PriceController');
            $router->get('/{stage}', 'PriceController@getPriceByStage');
        });

        /**
         * Orders
         */
        $router->group([
            'prefix' => 'orders',
        ], function ($router) {
            $router->get('/', 'OrderController@index');
            $router->get('/{id}', 'OrderController@show');
            $router->post('/', 'OrderController@store');
            $router->get('/get-pdf/{transaction_id}', 'OrderController@generatePdfForTransaction');
        });

        /**
         * Deposits
         */
        $router->group([
            'prefix' => 'deposits',
        ], function ($router) {
            $router->get('/', 'DepositController@index');
            $router->get('/{id}', 'DepositController@show');
            $router->post('/', 'DepositController@store');
            $router->get('/get-pdf/{transaction_id}', 'DepositController@generatePdfForTransaction');
        });
    });

    /**
     * ADMIN PANEL ACCESS
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => [
            'checkUser',
            'checkAdmin'
        ]
    ], function ($router) {
        /**
         * Contributors
         */
        $router->group([
            'prefix' => 'contributors',
        ], function ($router) {
            $router->get('/', 'ContributorController@index');
            $router->post('/', 'ContributorController@store');
            $router->get('/{id:[a-fA-F0-9\-]{36}}', 'ContributorController@show');
            $router->put('/{id:[a-fA-F0-9\-]{36}}', 'ContributorController@update');
            $router->delete('/{id:[a-fA-F0-9\-]{36}}', 'ContributorController@destroy');
        });

        /**
         * Products
         */
        $router->group(['prefix' => 'products'], function ($router) {
            $router->get('/', 'ProductController@index');
            $router->post('/', 'ProductController@store');
            $router->get('/{id:[a-fA-F0-9\-]{36}}', 'ProductController@show');
            $router->put('/{id:[a-fA-F0-9\-]{36}}', 'ProductController@update');
            $router->delete('/{id:[a-fA-F0-9\-]{36}}', 'ProductController@destroy');
        });

        /**
         * Transactions
         */
        $router->group([
            'prefix' => 'transactions',
        ], function ($router) {
            $router->get('/', 'TransactionController');
            $router->post('/', 'TransactionController@store');
        });
    });
});
