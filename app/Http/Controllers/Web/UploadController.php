<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Util\Code;
use App\Util\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
    //获取上传图片的token
    public function getToken()
    {
        $token = md5((Auth::guard('user-auth')->user()->id).time().mt_rand(10000, 99999));
        return $this->successResponse('成功', [
            'token' => $token
        ]);
    }

    //上传文件
    public function upload(Request $request)
    {
        $params = $request->all();

        $file             = $request->file('file');
        $tmp_name         = $file->getRealPath();
        $blob_num         = $params['blob_num'];
        $total_blob_num   = $params['total_blob_num'];
        $file_name        = $params['file_name'];
        $server_file_name = $params['server_file_name'] . '.' . pathinfo($file_name, PATHINFO_EXTENSION);

        if (! in_array(strtolower(pathinfo($file_name, PATHINFO_EXTENSION)), ['bmp', 'jpeg', 'jpg', 'png'])) {
            return $this->errorResponse(Code::PARAMS_ERROR, '仅支持bmp,jpeg,jpg,png四种图片格式');
        }

        if($total_blob_num > 5) {
            return $this->errorResponse(Code::OPERATE_FAIL, '请上传小于10M的图片');
        }

        try {
            $upload = new Upload($tmp_name, $blob_num, $total_blob_num, $server_file_name, $file_name);
            return $upload->apiReturn();
        } catch (\Exception $ex) {
            return $this->errorResponse(Code::OPERATE_FAIL, $ex->getMessage());
        }
    }
}