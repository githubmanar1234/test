<?php


namespace App\Exceptions;


use Illuminate\Support\Facades\Log;

class GeneralException extends \Exception
{
    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
        Log::debug($this->getMessage());
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->view(
            'errors.custom',
            array(
                'exception' => $this
            )
        );
    }
}