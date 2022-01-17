<?php

/**
 * @var Laravel\Lumen\Routing\Router $router
 */
$router->group([
    'prefix' => env('APP_API_VERSION', ''),
    'namespace' => '\App\Api\V1\Controllers',
    'middleware' => 'checkUser'
], function ($router) {
    /**
     * Contributors (
     */
    $router->group([
        'prefix' => 'contributors',
    ], function ($router) {
        $router->get('/', 'ContributorController@show');
        $router->post('/', 'ContributorController@store');
        $router->put('/identify', 'ContributorController@update');
        $router->patch('/agreement', 'ContributorController@agreement');
    });

    $router->get('identity-start', 'ContributorController@identity');

    /**
     * Transactions
     */
    $router->group([
        'prefix' => 'transactions',
    ], function ($router) {
        $router->get('/', 'TransactionsController');
    });

    /**
     * ADMIN PANEL
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => 'checkAdmin'
    ], function ($router) {
        /**
         * Contributors (
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
    });
});
