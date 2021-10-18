<?php


namespace App\Http\Repositories\IRepositories;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

interface IBaseRepository
{
    /**
     * @return Model
     */
    public function makeModel();

    /**
     * @param bool $paginated
     * @param array $conditions
     * @param array $columns
     * @return mixed
     */
    public function all($paginated = true,$conditions = [], $columns = array('*'));

    /**

     * @param array $conditions
     * @return mixed
     */
    public function allAsQuery( $conditions = []);
    /**
     * @param $model
     * @return mixed
     */
    public function create($model);

    /**
     * @param $key
     * @param $value
     * @param $data
     * @return mixed
     */
    public function updateOrCreate($key, $value, $data);

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $id, $attribute = "id");
    /**
     * @param $column
     * @param $operator
     * @param $value
     */
    public function where($column,$operator,$value);

    /**
     * @param $conditions
     * @return bool
     */
    public function whereArray($conditions);
    /**
     * @param mixed $id
     * 
     * @return void
     */
    public function delete($model);

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'));

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = array('*'));

    /**
     * @return mixed
     * 
     */
    public function latestRecord();

    function model();
}