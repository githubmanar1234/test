<?php


namespace App\Http\Repositories\Eloquent;


use App\Http\Repositories\IRepositories\IBaseRepository;
use App\Helpers\JsonResponse;
use App\Helpers\Mapper;
use App\Helpers\Constants;
use Illuminate\Container\Container as App;
use App\Helpers\ResponseStatus;
use Illuminate\Support\Facades\Log;


abstract class BaseRepository implements IBaseRepository
{

    /**
     * @var App
     */
    private $app;
    /**
     * @var array
     */
    private $requestData;

    /**
     * @var
     */
    protected $model;

    /**
     * BaseRepository constructor.
     * @param App $app
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Exception
     */
    public function __construct(App $app)
    {
        try {
            $this->app = $app;
            $this->makeModel();
            $this->requestData = Mapper::toUnderScore(\Request()->all());
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

    /**
     * @param bool $paginated
     * @param array $conditions
     * @param array $columns
     * @return bool|mixed
     */
    public function all($paginated = true, $conditions = [], $columns = array('*'))
    {
        $order_by = isset($this->requestData[Constants::ORDER_BY]) ? $this->requestData[Constants::ORDER_BY] : null;
        $order_by_direction = isset($this->requestData[Constants::ORDER_By_DIRECTION]) ? $this->requestData[Constants::ORDER_By_DIRECTION] : "desc";
        $filter_operator = isset($this->requestData[Constants::FILTER_OPERATOR]) ? $this->requestData[Constants::FILTER_OPERATOR] : "=";
        $filters = $this->requestData[Constants::FILTERS] ?? [];
        $per_page = isset($this->requestData[Constants::PER_PAGE]) ? $this->requestData[Constants::PER_PAGE] : 10;
        $paginate = isset($this->requestData[Constants::PAGINATE]) ? filter_var($this->requestData[Constants::PAGINATE], FILTER_VALIDATE_BOOLEAN) : $paginated;
        $query = $this->model;
        $allConditions = array_merge($conditions, $filters);
        $query = $query->filter($allConditions, $filter_operator);
        if (isset($order_by)) $query = $query->orderBy(Mapper::camelToSnake($order_by), $order_by_direction);
        else  $query = $query->orderBy('updated_at', $order_by_direction);
        if ($paginate) return $query->paginate($per_page, $columns);
        else {
            return $query->get($columns);
        }
    }

    /**
     * @param bool $paginated
     * @param array $conditions
     * @return bool|mixed
     */
    public function allAsQuery($conditions = [])
    {
        $order_by = isset($this->requestData[Constants::ORDER_BY]) ? $this->requestData[Constants::ORDER_BY] : null;
        $order_by_direction = isset($this->requestData[Constants::ORDER_By_DIRECTION]) ? $this->requestData[Constants::ORDER_By_DIRECTION] : "desc";
        $filter_operator = isset($this->requestData[Constants::FILTER_OPERATOR]) ? $this->requestData[Constants::FILTER_OPERATOR] : "=";
        $filters = $this->requestData[Constants::FILTERS] ?? [];
        $query = $this->model;
        $allConditions = array_merge($conditions, $filters);
        $query = $query->filter($allConditions, $filter_operator);
        if (isset($order_by)) $query = $query->orderBy(Mapper::camelToSnake($order_by), $order_by_direction);
        else  $query = $query->orderBy('updated_at', $order_by_direction);
        return $query;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function create($data)
    {
        try {
            return $this->model->create($data);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     * @return bool
     */
    public function where($column, $operator, $value)
    {
        return $this->model->where($column, $operator, $value)->get();
    }

    /**
     * @param $conditions
     * @return bool
     */
    public function whereArray($conditions)
    {
        try {
            return $this->model->where($conditions)->get();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return false;
        }
    }


    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     * @return mixed
     * @throws \Exception
     */
    public function update(array $data, $id, $attribute = "id")
    {
        try {
            $model_data = array();
            foreach ($this->model->getFillable() as $var) {
                if (isset($data[($var)]))
                    $model_data[$var] = $data[$var];
            }
            $modelFounded = $this->model->where($attribute, $id)->first();
            if (!isset($modelFounded)) {
                Log::error("trying to update resource not found");
                return false;
            }
            return $modelFounded->update($model_data);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

    /**
     * @param $key
     * @param $value
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function updateOrCreate($key, $value, $data)
    {
        try {
            $object = $this->findBy($key, $value);

            if (!$object)
                return $this->create($data);
            else
                return $this->update($data, $value, $key);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

    /**
     * @param $model
     * @return bool|void
     */
    public function delete($model)
    {
        try {
            $this->model->where("id", $model->id)->firstOrFail();
            return $this->model->destroy($model->id);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*'))
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     * @throws \Exception
     */
    public function findBy($attribute, $value, $columns = array('*'))
    {
        try {
            return $this->model->where($attribute, '=', $value)->first($columns);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    public function latestRecord()
    {
        return $this->model->orderBy('created_at', 'desc')->first();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract function model();

    /**
     * @return mixed
     */
    public function makeModel()
    {
        try {
            $model = $this->app->make($this->model());
            return $this->model = $model;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }

}
