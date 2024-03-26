<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use function PHPUnit\Framework\isNull;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function sendResponse($result = null, $message, $code, $accessToken = null, $refreshToken = null) {
        $response = [
            "success" => true,
            "status_code" => $code,
            "message" => $message,
        ];

        if($result !== null) {
            $response["data"] = $result;
        }


        if($accessToken !== null) {
            $response["access_token"] =  $accessToken;
            $response["token_type"] = "Bearer";
        }

        if( $refreshToken !== null) {
            $response["refreshToken"] =   $refreshToken;

        }


        return response()->json($response, $code);
    }

    protected function sendError($error, $errorMessages = [], $code) {
        $response = [
            "success" => false,
            "status_code" => $code,
            "message" => $error,
        ];

        if(!empty($errorMessages)) {
            $response["error"] = $errorMessages;
        }

        return response()->json($response, $code);
    }

}
