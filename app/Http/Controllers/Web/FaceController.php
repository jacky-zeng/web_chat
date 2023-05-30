<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\ChatRepository;
use App\Repositories\FaceRepository;
use App\Util\CacheKey;
use App\Util\EnDecryption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Validator;

class FaceController extends Controller
{
    //换脸首页
    public function index(Request $request)
    {
        return view('web.face.index');
    }

    //替换人脸
    public function exec(Request $request, FaceRepository $faceRepository)
    {
        $params = $request->all();

        if ($request->isMethod('post')) {
            $validator = Validator::make($params, [
                'file1' => 'required',
                'file2' => 'required',
            ], [
                'file1.required' => '请上传你的头像',
                'file2.required' => '请上传要替换的',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('500', $validator->errors()->first());
            }

            $rs = $faceRepository->merge($params['file1'], $params['file2']);

            if ($rs['result']) {
                $uploadFace = App::basePath() . '/public/uploadFace';
                $realDest   = $uploadFace . '/' . time() . rand(1, 1000) . '.jpg';

                $r = file_put_contents($realDest, base64_decode($rs['result']));
                if ($r) {
                    return $this->successResponse('成功', ['url' => 'http://chat.zengyanqi.com/' . explode('/public/', $realDest)[1]]);
                }
            }

            return $this->errorResponse('500', json_encode($rs));
        } else {
            return $this->errorResponse('500', '仅支持post');
        }
    }

    public function uploadFile(Request $request)
    {
        $file             = $request->file('file');
        $tmp_name         = $file->getRealPath();

        if ($file == null) {
            return $this->errorResponse('-1', '上传文件不存在');
        }

        $fileExt = $this->checkFileType($tmp_name);

        $uploadFace = App::basePath() . '/public/uploadFace';

        $realDest = $uploadFace . '/' . time() . rand(1, 1000) . '.' . $fileExt;

        //进程结束，临时文件消失,move_uploaded_file 函数可使临时文件提前消失
        if (!@move_uploaded_file($tmp_name, $realDest)) {
            return $this->errorResponse('0019', '文件保存失败');
        } else {
            return $this->successResponse('成功', ['url' => 'http://chat.zengyanqi.com/' . explode('/public/', $realDest)[1]]);
        }
    }

    private function checkFileType($filename)
    {
        $file = fopen($filename, 'rb');
        $bin  = fread($file, 2);
        fclose($file);
        $strInfo  = @unpack("c2chars", $bin);
        $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
        $fileType = '';
        switch ($typeCode) {
            case 7790   :
                $fileType = 'exe';
                break;
            case 7784   :
                $fileType = 'midi';
                break;
            case 8297   :
                $fileType = 'rar';
                break;
            case 255216 :
                $fileType = 'jpg';
                break;
            case 7173   :
                $fileType = 'gif';
                break;
            case 6677   :
                $fileType = 'bmp';
                break;
            case 13780  :
                $fileType = 'png';
                break;
            case 3780  :
                $fileType = 'pdf';
                break;
            case 7368  :
                $fileType = 'mp3';
                break;
            case 00  :
                $fileType = 'mp4';
                break;
            default     :
                $fileType = 'unknown' . $typeCode;
                break;
        }

        if ($strInfo['chars1'] == '-1' && $strInfo['chars2'] == '-40') {
            return 'jpg';
        }
        if ($strInfo['chars1'] == '-1' && $strInfo['chars2'] == '-5') {
            return 'mp3';
        }
        if ($strInfo['chars1'] == '0' && $strInfo['chars2'] == '0') {
            return 'mp4';
        }
        if ($strInfo['chars1'] == '-119' && $strInfo['chars2'] == '80') {
            return 'png';
        }
        if ($strInfo['chars1'] == '-48' && $strInfo['chars2'] == '-49') {
            return 'xls';
        }
        if ($strInfo['chars1'] == '80' && $strInfo['chars2'] == '75') {
            return 'xlsx';
        }
        return $fileType;
    }

}
