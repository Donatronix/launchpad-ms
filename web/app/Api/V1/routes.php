<?php

    /**
     * @var Laravel\Lumen\Routing\Router $router
     */
    $router->group([
        'prefix' => env('APP_API_VERSION', ''),
        'namespace' => '\App\Api\V1\Controllers',
    ], function ($router) {
        /**
         * PUBLIC ACCESS
         *
         * level with free access to the endpoint
         */
        $router->group([
            'namespace' => 'Public',
        ], function ($router) {
            /**
             * Products for public access
             */
            $router->group([
                'prefix' => 'products',
            ], function ($router) {
                $router->get('/', 'ProductController@index');
                $router->get('/{id}', 'ProductController@show');
            });

            /**
             * Token Rewards
             */
            $router->get('/token-rewards', 'TokenRewardController');

            /**
             * Prices
             */
            $router->group([
                'prefix' => 'prices',
            ], function ($router) {
                $router->get('/', 'PriceController@index');
                $router->get('/{stage}', 'PriceController@getPriceByStage');
            });
        });

    /**
     * USER APPLICATION PRIVATE ACCESS
     *
     * Application level for users
     */
    $router->group([
        'prefix' => 'app',
        'namespace' => 'Application',
        'middleware' => 'checkUser',
    ], function ($router) {
        /**
         * Init first Investment (registration)
         */
        $router->group([
            'prefix' => 'investment',
        ], function ($router) {
            $router->post('/', 'InvestmentController');
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
        });

        /**
         * Deposits
         */
        $router->group([
            'prefix' => 'deposits',
        ], function ($router) {
            $router->get('/', 'DepositController@index');
            $router->post('/', 'DepositController@store');
            $router->get('/{id:[a-fA-F0-9\-]{36}}', 'DepositController@show');
            $router->get('/paid', 'DepositController@getPaidDeposits');
        });

        /**
         * Token Purchase - shopping List
         */
        $router->group([
            'prefix' => 'purchases',
        ], function ($router) {
            $router->get('/', 'PurchaseController@index');
            $router->post('/', 'PurchaseController@store');
            $router->post('/token-worth', 'PurchaseController@tokenWorth');
        });

        /**
         * Dashboard
         */
        $router->group([
            'prefix' => 'dashboard',
        ], function ($router) {
            /**
             * User Statistics
             */
            $router->get('/user-validate', 'DashboardController@getUserValidate');

            /**
             * Token Investors
             */
            $router->get('/token-investors', 'DashboardController@tokenInvestors');

            /**
             * Token Sales Progress
             */
            $router->get('/token-sales-progress', 'DashboardController@tokenSalesProgress');
        });

        /**
         * Calculation of the purchased token
         */
        $router->post('/token-calculate', 'TokenCalculateController');
    });

    /**
     * ADMIN PANEL ACCESS
     *
     * Admin / super admin access level (E.g CEO company)
     */
    $router->group([
        'prefix' => 'admin',
        'namespace' => 'Admin',
        'middleware' => [
            'checkUser',
            'checkAdmin',
        ],
    ], function ($router) {
        /**
         * Dashboard
         */
        $router->get('dashboard', 'DashboardController');

        /**
         * Products
         */
        $router->group([
            'prefix' => 'products',
        ], function ($router) {
            $router->get('/', 'ProductController@index');
            $router->post('/', 'ProductController@store');
            $router->get('/{id:[a-fA-F0-9\-]{36}}', 'ProductController@show');
            $router->put('/{id:[a-fA-F0-9\-]{36}}', 'ProductController@update');
            $router->delete('/{id:[a-fA-F0-9\-]{36}}', 'ProductController@destroy');
        });

        /**
         * Price
         */
        $router->group([
            'prefix' => 'price',
        ], function ($router) {
            $router->get('/', 'PriceController@index');
            $router->post('/', 'PriceController@store');
            $router->get('/{id}', 'PriceController@show');
            $router->put('/{id}', 'PriceController@update');
            $router->delete('/{id}', 'PriceController@destroy');
        });

        /**
         * Token Rewards
         */
        $router->group([
            'prefix' => 'token-rewards',
        ], function ($router) {
            $router->get('/', 'TokenRewardController@index');
            $router->post('/', 'TokenRewardController@store');
            $router->get('/{id}', 'TokenRewardController@show');
            $router->put('/{id}', 'TokenRewardController@update');
            $router->delete('/{id}', 'TokenRewardController@destroy');
        });

        /**
         * Transactions
         */
        $router->group([
            'prefix' => 'transactions',
        ], function ($router) {
            $router->get('/', 'TransactionController@index');
            $router->post('/', 'TransactionController@store');
            $router->get('/{transaction_id}', 'TransactionController@show');
            $router->put('/{transaction_id}', 'TransactionController@update');
            $router->delete('/{transaction_id}', 'TransactionController@destroy');
        });

        /**
         * Deposits
         */
        $router->group([
            'prefix' => 'deposits',
        ], function ($router) {
            $router->get('/', 'DepositController@index');
            $router->post('/', 'DepositController@store');
            $router->get('/{id}', 'DepositController@show');
            $router->put('/{id}', 'DepositController@update');
            $router->delete('/{id}', 'DepositController@destroy');
        });

        /**
         * Orders
         */
        $router->group([
            'prefix' => 'orders',
        ], function ($router) {
            $router->get('/', 'OrderController@index');
            $router->post('/', 'OrderController@store');
            $router->get('/{id}', 'OrderController@show');
            $router->put('/{id}', 'OrderController@update');
            $router->delete('/{id}', 'OrderController@destroy');
            $router->get('/{id}/approve', 'OrderController@approve');
            $router->get('/{id}/reject', 'OrderController@reject');
        });
