<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;


    public $error;

    public function returnError($errors = false, $message = 'Error', $code = 400)
    {
        return response([
            'success' => false,
            'message' => $message,
            'error' => $errors
        ], $code);
    }
    public function returnSuccess($data, $message = 'Success'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }
}
