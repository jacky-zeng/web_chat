<?php

namespace App\Repositories;

use App\Models\ChatLog;
use App\Util\EnDecryption;
use App\Util\Errors;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use DB;
use Validator;

class FaceRepository
{
    use Errors;

    private $key = null;
    private $secret = null;

    public function __construct()
    {
        $this->key    = 'uMONEZeW3obAgklu7shh9aP9G1khgRcB';
        $this->secret = 'vNVGz6cHMcrcnC9XGmnagaoFELQvJgJm';
    }

    public function detect($imageBase64)
    {
        $apiUrl = 'https://api-cn.faceplusplus.com/facepp/v3/detect';

        $body = [
            'api_key'         => $this->key,
            'api_secret'      => $this->secret,
            'image_base64'    => $imageBase64,
            'return_landmark' => 1,
        ];

        return self::curlRequest($apiUrl, $body, 'POST', false, null, 'Content-type:multipart/form-data');
    }

    //稿件提交
    public function merge($file1, $file2)
    {
        $apiUrl     = 'https://api-cn.faceplusplus.com/imagepp/v1/mergeface';
        $uploadFace = App::basePath() . '/public/uploadFace';

        $file1Base64 = base64_encode(file_get_contents($uploadFace . '/' . $file1));
        $detect1     = $this->detect($file1Base64);
        $rectangle1  = $detect1['faces'][0]['face_rectangle'];

        $file2Base64 = base64_encode(file_get_contents($uploadFace . '/' . $file2));
        $detect2     = $this->detect($file2Base64);
        $rectangle2  = $detect2['faces'][0]['face_rectangle'];

        $body = [
            'api_key'            => $this->key,
            'api_secret'         => $this->secret,
            'merge_base64'       => $file1Base64,
            'merge_rectangle'    => $rectangle1['top'] . ',' . $rectangle1['left'] . ',' . $rectangle1['width'] . ',' . $rectangle1['height'],
            'template_base64'    => $file2Base64,
            'template_rectangle' => $rectangle2['top'] . ',' . $rectangle2['left'] . ',' . $rectangle2['width'] . ',' . $rectangle2['height'],
            'merge_rate'         => 80, //融合比例，范围 [0,100]。数字越大融合结果包含越多融合图 (merge_url, merge_file, merge_base64 代表图片) 特征。
        ];

        return self::curlRequest($apiUrl, $body, 'POST', false, null, 'Content-type:multipart/form-data');
    }

    private static $apiHost = '';
    private static $clientObj;
    private static $ch;
    public static $timeOut = 10;

    const GET_METHOD = 'GET';
    const POST_METHOD = 'POST';
    const DEL_METHOD = 'DELETE';
    const PUT_METHOD = 'PUT';
    const PATCH_METHOD = 'PATCH';

    public static function curlRequest($url = '', $params = '', $type = self::GET_METHOD, $upload = false, $file = null, $header = null, $isBinary = false)
    {
        self::$ch = curl_init(); //不要判断 self::$ch 为真，因为一个进程多次上传，curl_setopt 是要清空才行的
        $fp       = null;
        curl_setopt(self::$ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt(self::$ch, CURLOPT_URL, $url);
        curl_setopt(self::$ch, CURLOPT_HEADER, 0);
        curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(self::$ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt(self::$ch, CURLOPT_TIMEOUT, self::$timeOut);
        if (strpos($url, 'https') === 0) {
            curl_setopt(self::$ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        if ($header) {
            curl_setopt(self::$ch, CURLOPT_HTTPHEADER, is_array($header) ? $header : [$header]);
        }

        if ($file && $type == 'POST') {
            if ($isBinary) {
                $params = file_get_contents(realpath($file['tmp_name']));
            } else {
                $params['file'] = new \CURLFile(realpath($file['tmp_name']));
            }
        }

        if ($upload && is_array($params) && "@" == substr(current($params), 0, 1)) {
            if ((version_compare(PHP_VERSION, '5.5.0') >= 0) && class_exists('CurlFile')) {
                //经测试，如果文件不存在，就会报 10600  连接失败，所以必须判断文件是否存在
                if (isset($params['asName'], $params['file_type']) && ($params['asName'] && $params['file_type'])) {
                    $params[key($params)] = new \CURLFile(substr(current($params), 1), $params['file_type'], $params['asName']);
                } else {
                    $params[key($params)] = new \CURLFile(substr(current($params), 1));
                }
            }
        }

        switch (strtoupper($type)) {
            case "GET"    :
                curl_setopt(self::$ch, CURLOPT_HTTPGET, true);
                break;
            case "POST"   :
                curl_setopt(self::$ch, CURLOPT_POST, true);
                curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $params);
                break;
            case "PUT"    :
                curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, self::PUT_METHOD);
                curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $params);
                break;
            case "DELETE" :
                curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, self::DEL_METHOD);
                curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $params);
                break;
            case "PATCH"    :
                curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, self::PATCH_METHOD);
                curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $params);
                break;
            case 'DOWNLOAD':
                //创建目录

                $path = dirname($file);
                if (!is_dir($path)) {
                    if (mkdir($path, 0777, true) === false) {
                        return ['status' => false, 'data' => '0010', 'message' => '目录创建失败'];
                    }
                }

                if (!$fp = @fopen($file, 'w+')) {
                    return ['status' => false, 'data' => '0009', 'message' => '文件无法写入'];
                }
                curl_setopt(self::$ch, CURLOPT_FILE, $fp);
                break;

        }
        $result = @curl_exec(self::$ch);
        //echo $result;
        if ($type == 'download') @fclose($fp);


        if (empty($result)) {
            @logfile(__METHOD__, $result, 'ApiClient 连接失败:' . $url, 3);
            return ['status' => false, 'data' => 10600, 'message' => '连接失败'];
        }

        $resultArray = json_decode($result, true);
        if (empty($resultArray)) {
            //you may add log here to the data before json_decode, which may help
            //Log($result, 'filePath.log');
            @logfile(__METHOD__, $result, 'ApiClient 数据获取有误:' . $url, 3);
            return ['status' => false, 'data' => 10700, 'message' => '数据获取有误'];
        }
        return $resultArray;
    }

}
