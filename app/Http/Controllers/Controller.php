<?php

namespace App\Http\Controllers;

use App\Util\Code;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 返回逻辑错误
     *
     * @param $message
     * @param $errorCode
     * @param array $data
     * @return mixed
     */
    public function errorResponse($errorCode, $message, $data = [])
    {
        return response()->json([
            'code'    => $errorCode,
            'message' => $message,
            'data'    => $data
        ]);
    }

    /**
     * 返回成功信息及内容
     *
     * @param array $data
     * @param string $message
     * @return mixed
     */
    public function successResponse($message = '成功', $data = [])
    {
        return response()->json([
            'code'    => Code::SUCCESS,
            'message' => $message,
            'data'    => $data
        ]);
    }
}
