<?php
namespace App\Helper;

use Illuminate\Http\Response;

class Wrapper {

    public function response($data, $message, $code=Response::HTTP_OK)
    {
        return response()->json([
            "data" => $data,
            "message" => $message,
        ], $code);
    }

    public function responsePage($data, $meta, $message, $code=Response::HTTP_OK)
    {
        return response()->json([
            "data" => $data,
            "message" => $message,
            "meta" => $meta,
        ], $code);
    }

    public function data($data)
    {
        return [
            "err" => null,
            "data" => $data,
        ];
    }
    public function error($err)
    {
        return [
            "err" => $err,
            "data" => null,
        ];
    }
}
