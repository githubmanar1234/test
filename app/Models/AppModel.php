<?php


namespace App\Models;

use App\Helpers\Constants;
use Illuminate\Database\Eloquent\Model;

class AppModel extends Model
{

    public $translatable = [];

    public function scopeFilter($builder, $filters = null,$filterOperator = "=")
    {
        if (isset($filters) && is_array($filters)) {
            foreach ($filters as $field => $value) {
                if ($value == Constants::NULL)
                    $builder->whereNull($field);
                elseif ($value == Constants::NOT_NULL)
                    $builder->whereNotNull($field);
                elseif (is_array($value))
                    $builder->whereIn($field, $value);
                elseif ($filterOperator == "like")
                    $builder->where($field,$filterOperator, '%'.$value.'%');
                else
                    $builder->where($field, $value);
            }
        }
        return $builder;
    }

    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date) : string
    {
        return $date->toDateTimeString();
    }
}
