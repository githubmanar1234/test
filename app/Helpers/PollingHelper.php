<?php


use App\Helpers\Constants;

if (!function_exists("getActivePolling")) {
    /**
     * @param string $type
     * @return mixed
     */
    function getActivePolling($type = App\Helpers\Constants::POLLING_TYPE_NORMAL)
    {
        $query = \App\Models\Polling::where("status", App\Helpers\Constants::POLLING_STATUS_ACTIVE)->where("type", $type);
        return $query->first();
    }

}
if (!function_exists("getClosedPolling")) {
    /**
     * @param string $type
     * @return mixed
     */
    function getClosedPolling($type = App\Helpers\Constants::POLLING_TYPE_NORMAL)
    {
        $query = \App\Models\Polling::where("status", App\Helpers\Constants::POLLING_STATUS_CLOSED)->where("type", $type);
        return $query->orderBy("start_date", "DESC")->first();
    }

}

if (!function_exists("getActivePollingId")) {
    /**
     * @param string $type
     * @return mixed
     */
    function getActivePollingId($type = App\Helpers\Constants::POLLING_TYPE_NORMAL)
    {
        if (getActivePolling($type))
            return getActivePolling($type)->id;
        return null;
    }
}
if (!function_exists("getActivePollingsCount")) {
    /**
     * @param string $type
     * @return mixed
     */
    function getActivePollingsCount($type = App\Helpers\Constants::POLLING_TYPE_NORMAL)
    {
        $query = \App\Models\Polling::where("status", App\Helpers\Constants::POLLING_STATUS_ACTIVE)->where("type", $type)->get();
        return $query->count();
    }
}


