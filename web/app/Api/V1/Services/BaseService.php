<?php

namespace App\Services;

abstract class BaseService
{
    /**
     * @property Model $model
     *
     *
     */
    protected $model;

    /**
     * Create new Model
     *
     * @param array $data
     * @return Model
     *
     */
    public function create(array $data)
    {
        return $this->model::create($data);
    }


    /**
     * Read a Model
     *
     * @param string $id
     * @param array $relations
     *
     * @return Model
     *
     */
    public function read($id, $relations = [])
    {
        return $this->model::with($relations)->find($id);
    }


    /**
     * Update Model
     *
     * @param array $data
     * @param string $id
     *
     * @return Model
     * @throws Exception
     */
    public function update(array $data, $id)
    {
        $model = $this->read($id);
        if ($model) {
            $model->fill($data);
            $model->save();
            return $model;
        }

        throw new \Exception("Model ID not recognized", 400);
    }

    /**
     * Delete Model
     *
     * @param string $id
     *
     * @return Model
     * @throws Exception
     */
    public function delete($id)
    {
        $model = $this->read($id);
        if ($model) {
            $model->delete();
            return $model;
        }

        throw new \Exception("Model ID not recognized", 400);
    }

    /**
     * Read Models
     *
     * @param int $limit
     *
     * @return Paginator
     */
    public function paginate($limit = 10)
    {
        return $this->model::latest()->paginate($limit);
    }
}
