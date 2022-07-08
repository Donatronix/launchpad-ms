<?php

namespace App\Services;

abstract class BaseService
{
    public $model;

    public function create(array $data)
    {
        # code...
    }

    public function read($id)
    {
        # code...
    }

    public function paginate($limit = 10)
    {
        # code...
    }

    public function update(array $data, $id)
    {
        # code...
    }

    public function delete($id)
    {
        # code...
    }
}

