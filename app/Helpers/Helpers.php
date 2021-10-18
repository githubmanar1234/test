<?php


namespace App\Helpers;


use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Array_;
use function PHPSTORM_META\map;

class Helpers
{
    function toCamel($o)
    {
        if (is_array($o)) {
            return array_map(function ($value) {
                if (is_object($value)) {
                    $value = $this->toCamel($value);
                }
                return $value;
            }, $o);
        } else {
            $newO = [];
            foreach ($o as $origKey) {
                if (array_key_exists($origKey, $o)) {
                    $newKey = Str::camel($origKey);
                    $value = $o[$origKey];
                    if (is_array($value) || ($value !== null && is_object($value))) {
                        $value = $this->toCamel($value);
                    }
                    $newO[$newKey] = $value;
                }
            }
        }
        return $newO;
    }

    /**
     * @param mixed $tableName
     * @param mixed $col
     *
     * @return mixed
     * @auth yasser kanj
     */
    public static function generateUniqueCode($tableName, $col)
    {
        $code = hexdec(uniqid(true));
        $result = \DB::table($tableName)->where($col, $code)->exists();
        if ($result) {
            return self::generateUniqueCode($tableName, $col);
        }
        return $code;
    }

    /**
     * @param $key
     * @return mixed
     */
    static function settingsGet($key)
    {
        return Cache::rememberForever("setting-$key", function () use ($key) {
            return Setting::where('key', $key)->first()->value;
        });
    }

    /**
     * @param $key
     * @return mixed
     */
    static function settingsUpdate($key)
    {
        return Cache::put("setting-$key", "value");
    }

    static function generateRandomString($length = 6)
    {
        $characters = '0123456789abcdefghjkmnopqrstuvwxyzABCDEFGHJKMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    static function underScoreToWords($sentence)
    {
        return ucfirst(str_replace("_", " ", $sentence));
    }
}