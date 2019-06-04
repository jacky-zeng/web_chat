<?php

/**
 * 分片上传功能
 */

namespace App\Util;

use Illuminate\Support\Facades\App;

class Upload
{
    private $file_path;              //上传目录
    private $tmp_path;               //PHP文件临时目录
    private $blob_num;               //第几个文件块
    private $total_blob_num;         //文件块总数
    private $file_name;              //文件名
    private $server_file_name;       //服务端文件名

    public function __construct($tmp_path, $blob_num, $total_blob_num, $file_name, $server_file_name) {
        $this->file_path         = App::basePath().'/public/upload';
        $this->tmp_path         = $tmp_path;
        $this->blob_num         = $blob_num;
        $this->total_blob_num   = $total_blob_num;
        $this->file_name        = $file_name;
        $this->server_file_name = $server_file_name;

        $this->moveFile();
        $this->fileMerge();
    }

    //判断是否是最后一块，如果是则进行文件合成并且删除文件块
    private function fileMerge()
    {
        if ($this->blob_num == $this->total_blob_num) {
            $blob = '';
            try {
                for ($i = 1; $i <= $this->total_blob_num; $i++) {
                    $blob .= file_get_contents($this->file_path . '/' . $this->file_name . '__' . $i);
                }
                file_put_contents($this->file_path . '/' . $this->file_name, $blob);
                $this->deleteFileBlob();
            } catch (\Exception $ex) {

            }
        }
    }

    //删除文件块
    private function deleteFileBlob(){
        for($i=1; $i<= $this->total_blob_num; $i++){
            @unlink($this->file_path.'/'. $this->file_name.'__'.$i);
        }
    }

    //移动文件
    private function moveFile(){
        $this->touchDir();
        $file_name = $this->file_path.'/'. $this->file_name.'__'.$this->blob_num;
        move_uploaded_file($this->tmp_path,$file_name);
    }

    //API返回数据
    public function apiReturn(){
        if($this->blob_num == $this->total_blob_num){
            if(file_exists($this->file_path.'/'. $this->file_name)){
                return response()->json([
                    'code'    => 200,
                    'message' => 'success',
                    'data'    => [
                        'file_name' => $this->file_name
                    ]
                ]);
            }
        }
        if(file_exists($this->file_path.'/'. $this->file_name.'__'.$this->blob_num)){
            return response()->json([
                'code'    => 203,
                'message' => 'waiting for all',
                'data'    => ''
            ]);
        }
    }

    //建立上传文件夹
    private function touchDir(){
        if(!file_exists($this->file_path)){
            return mkdir($this->file_path);
        }
    }
}