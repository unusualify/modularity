<?php

namespace Unusual\CRM\Base\Repositories;

use Nwidart\Modules\Facades\Module;
use Unusual\CRM\Base\Repositories\Behaviors\HandleDates;

abstract class BaseRepository
{

    use DatesTrait;

    /**
     * Name of model to be shown
     */
    public $name = "Base";

    /**
     * Model::class
     */
    public $model;

    /**
     * @var boolean
     */
    public $searchable = true;

    /**
     * @var integer
     */
    public $itemsPerPage = 10;

    /**
     * @var array
     */
    public $filters = [];

    /**
     * @return mixed
     */
    public function findAll()
    {
        return $this->model::all();
    }

    /**
     * @param string $column
     * @param $value
     * @return mixed
     */
    public function findBy(string $column, $value)
    {
        return $this->model::where($column, $value);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        if( method_exists($this, 'rectifyInputs') ){
            $data = $this->rectifyInputs($data);
        }
        return $this->model::create($data)->fresh();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        if( method_exists($this, 'rectifyInputs') ){
            $data = $this->rectifyInputs($data);
        }
        return $this->model::insert($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function update(int $id, array $data)
    {
        if( method_exists($this, 'rectifyInputs') ){
            $data = $this->rectifyInputs($data, 'update');
        }
        $item = $this->findById($id);
        $item->fill($data);
        $item->save();
        return $item->fresh();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return $this->model::find($id);
    }

    /**
     * @param int $id
     * @return mixed|void
     */
    public function delete(int $id)
    {
        $this->model::destroy($id);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id)
    {
        $object = $this->findByIdWithTrashed($id);
        if ($object && method_exists($this->model, 'isSoftDelete')) {
            $object->restore($id);
            return $object;
        }
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findByIdWithTrashed(int $id)
    {
        if (method_exists($this->model, 'isSoftDelete')) {
            return $this->model::withTrashed()->find($id);
        }
    }


}
