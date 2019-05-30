<?php

namespace App\Util;

use GuzzleHttp\Client;

class TuLingChat
{
    use Errors;

    const API_URL = 'http://openapi.tuling123.com/openapi/api/v2';

    const API_KEY = '0d3f3e1d50f0404da9304ad871336ac0';

    /**
     * 获取数据
     *
     * @param $method  POST/GET
     * @param array $params
     * @return array
     */
    private static function getData($method, $params = [])
    {
        $url = self::API_URL;
        try {
            $client = new Client();
            $request_data = [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ];

            $response = $client->request($method, $url, $request_data);
            $result = json_decode($response->getBody()->getContents(), true);

            //5000	无解析结果
            //6000	暂不支持该功能
            //4000	请求参数格式错误
            //4001	加密方式错误
            //4002	无功能权限
            //4003	该apikey没有可用请求次数
            //4005	无功能权限
            //4007	apikey不合法
            //4100	userid获取失败
            //4200	上传格式错误
            //4300	批量操作超过限制
            //4400	没有上传合法userid
            //4500	userid申请个数超过限制
            //4600	输入内容为空
            //4602	输入文本内容超长(上限150)
            //7002	上传信息失败
            //8008	服务器错误
            //0	    上传成功

            $code = array_get($result, 'intent.code');
            switch ($code) {
                case 4003:
                    $answer = [
                        '本机器人交不起电费了，大侠打赏点给我充电费吧！',
                        '<img width="120" height="120" src="/img/common/zhifubao.jpg" />'
                    ];
                    break;
                case 4400:
                    $answer = ['哪里跑来的非法用户，我要报警了哈'];
                    break;
                case 4500:
                    $answer = [
                        '本机器人的分身术太耗查克拉了，大侠打赏点给我补充查克拉吧',
                        '<img width="120" height="120" src="/img/common/zhifubao.jpg" />'
                    ];
                    break;
                default:
                    $answer = [array_get($result, 'results.0.values.text') ?: '有点无敌'];
                    break;
            }
        } catch (\Exception $exception) {
            $answer = ['有点无敌'];
        }

        return [
            'code' => Code::SUCCESS,
            'message' => '成功',
            'data' => $answer
        ];
    }

    /**
     * 聊天
     *
     * @param $user_id
     * @param $message
     * @return bool|mixed|string
     */
    public static function ask($user_id, $message)
    {
        $rand = mt_rand(0, 36);
        if($rand == 6) {
            $answer = [
                '您好！本代码的源码地址在：<a target="_blank" href="https://github.com/jacky-zeng/web_chat">https://github.com/jacky-zeng/web_chat</a>,支持我的或者觉得对你有帮助，请在我的github上留下你的小星星^_^',
            ];
            return $answer;
        }
        if($rand == 18) {
            $answer = [
                '您好！本代码的源码地址在：<a target="_blank" href="https://github.com/jacky-zeng/web_chat">https://github.com/jacky-zeng/web_chat</a>,支持我的或者觉得对你有帮助，请在我的github上留下你的小星星，如果你想土豪一把的话，可以给我发支付宝红包^_^',
                '<img width="120" height="120" src="/img/common/zhifubao.jpg" />',
                '谢谢'
            ];
            return $answer;
        }

        $message = trim($message); //清除字符串两边的空格
        $message = strip_tags($message,""); //利用php自带的函数清除html格式
        $message = preg_replace("/\t/","",$message); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
        $message = preg_replace("/\r\n/","",$message);
        $message = preg_replace("/\r/","",$message);
        $message = preg_replace("/\n/","",$message);
        $message = preg_replace("/ /","",$message);
        $message = preg_replace("/  /","",$message);  //匹配html中的空格
        $message = trim($message); //返回字符串

        if(empty($message)){
            return ['额'];
        }
        if (mb_strlen($message) >= 150) {
            return ['别说这么多好不，我脑瓜疼!'];
        }
        $ask    = [
            'reqType'    => 0,
            'perception' => [
                'inputText' => [
                    'text' => $message
                ]
            ],
            'userInfo'   => [
                'apiKey' => self::API_KEY,
                'userId' => $user_id
            ]
        ];
        $method = 'POST';
        $rs     = self::getData($method, $ask);

        if ($rs['code'] == Code::SUCCESS) {
            return $rs['data'];
        }

        return ['服务器异常'];
    }
}