<?php


namespace App\Helpers;


use Exception;

class JsonResponse
{
    const MSG_ADDED_SUCCESSFULLY = 'msg_added_successfully';
    const MSG_CREATION_ERROR = 'msg_creation_error';
    const MSG_UPDATE_ERROR = 'msg_update_error';
    const MSG_ACCEPTED_SUCCESSFULLY = "msg_accepted_successfully";
    const MSG_REJECTED_SUCCESSFULLY = "msg_rejected_successfully";
    const MSG_UPDATED_SUCCESSFULLY = "msg_updated_successfully";
    const MSG_DELETED_SUCCESSFULLY = "msg_deleted_successfully";
    const MSG_DELETE_REJECTED_BECAUSE_OF_RELATED_OBJECTS = "msg_delete_rejected";
    const MSG_NOT_ALLOWED = "msg_not_allowed";
    const MSG_NOT_AUTHORIZED = "msg_not_authorized";
    const MSG_NOT_AUTHENTICATED = "msg_not_authenticated";
    const MSG_NOT_FOUND = "msg_not_found";
    const MSG_USER_NOT_FOUND = "msg_user_not_found";
    const MSG_SUCCESS = "msg_success";
    const MSG_FAILED = "msg_failed";
    const MSG_LOGIN_SUCCESSFULLY = "msg_login_successfully";
    const MSG_REGISTERED_SUCCESSFULLY = "msg_registered_successfully";
    const MSG_LOGIN_FAILED = "msg_login_failed";
    const MSG_INVALID_INPUTS = "msg_invalid_input";
    const MSG_REQUIRED = "msg_required";
    const MSG_CODE_INVALID = "msg_invalid_code";
    const MSG_PASSWORD_RESET = "msg_reset_successfully";
    const MSG_GENERAL_ERROR = "msg_general_error";
    const MSG_BAD_REQUEST = "msg_bad_request";
    const MSG_RESOURCE_REPORTED_BEFORE = "msg_resource_reported_before";
    const MSG_REPORTED_SUCCESSFULLY = "msg_reported_successfully";
    const MSG_SUBSCRIBED_SUCCESSFULLY = "msg_subscribed_successfully";
    const SERVICE_STATUS_INVALID = "service_status_invalid";


    /**
     * @param $message
     * @param null $content
     * @param int $status
     * @param string $conventionType
     * @return \Illuminate\Http\JsonResponse
     */
    public static function respondSuccess($message, $content = null, $status = 200, $conventionType = Constants::CONV_CAMEL)
    {
        $contentData = null;
        if (!is_null($content)) {
            switch ($conventionType) {
                case Constants::CONV_CAMEL:
                    $contentData = Mapper::toCamel($content);
                    break;
                case Constants:: CONV_UNDERSCORE:
                    $contentData = $content;
                    break;
                default:
                    $contentData = $content;
            }
        }
        return response()->json([
            'content' => $contentData,
            'message' => $message,
            'status' => $status
        ],$status);
    }

    /**
     * @param $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function respondError($message, $status = 400)
    {
        return response()->json([
            'content' => null,
            'message' => $message,
            'status' => $status
        ],$status);
    }

    public static function downloadFile($url)
    {
        return response()->download(public_path('storage/' . $url));
    }

    public static function downloadProject($zipName)
    {
        $headers = ['Content-Type: application/zip'];
        return response()->download($zipName, '', $headers);
    }

    public static function uploadFile($url)
    {

    }

    /**
     * @param Exception $exception
     * @return mixed|void
     * @author karam mustafa
     * @desc this function used if you have any large validation process and you append
     * errors message to any array , this will determine if error message on json array
     */
    public static function formatExceptionMessage(Exception $exception)
    {
        return gettype(json_decode($exception->getMessage())) == 'array'
            ? json_decode($exception->getMessage())
            : $exception->getMessage();
    }
}
