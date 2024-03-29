<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

///Applications/XAMPP7.1/xamppfiles/htdocs/web_chat/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php
/// 被我改了 return str_replace('127.0.0.1:1215', 'chat.zengyanqi.com', app('url')->route($name, $parameters, $absolute));

Route::get('/', function () {
    return \Illuminate\Support\Facades\Redirect::route('web_chat_chat');  //自动跳首页
});

//未登录
Route::group( [
    'middleware' => [],
    'prefix' => '',
    'namespace'  => 'Web'
], function() {
    Route::match(['get', 'post'], 'touristLogin', 'AuthController@touristLogin')->name('tourist_login');                //游客登录
    Route::get('getNickName', 'AuthController@getNickName')->name('get_nick_name');                                     //生成随机游客名称
    Route::post('register', 'AuthController@register')->name('user_register');                                          //注册
    Route::match(['get', 'post'], 'login', 'AuthController@login')->name('user_login');                                 //登录
    Route::get('logout', 'AuthController@logout')->name('user_logout');                                                 //退出登录

    Route::get('faceIndex', 'FaceController@index')->name('face_index');
    Route::post('exec', 'FaceController@exec')->name('face_exec');
    Route::post('uploadFile', 'FaceController@uploadFile')->name('face_upload_file');
});

Route::group([
    'middleware' => ['user-auth'],
    'prefix'     => '',
    'namespace'  => 'Web',
], function () {
    Route::get('index', 'ChatController@index')->name('web_chat_index');
    Route::get('chat', 'ChatController@chat')->name('web_chat_chat');
    Route::get('chat_log', 'ChatController@chatLog')->name('web_chat_chat_log');

    /*文件上传*/
    Route::get('upload_get_token', 'UploadController@getToken');
    Route::post('upload_upload', 'UploadController@upload');
});
