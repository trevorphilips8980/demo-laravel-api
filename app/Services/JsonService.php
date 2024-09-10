<?php

namespace App\Services;

class JsonService
{
    // send JSON request
    public function sendResponse($status, $data, $message, $code)
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $code);
    }
}
