<?php

namespace App\Services;

use App\Models\Investor;

class InvestorService extends BaseService
{
    public function __construct()
    {
        $this->model = new Investor;
    }
}

