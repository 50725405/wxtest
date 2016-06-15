<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Log;
class WeChatController extends Controller
{
    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve()
    {
        Log::info('request arrived.'); # 注意：Log 为 Laravel 组件，所以它记的日志去 Laravel 日志看，而不是 EasyWeChat 日志

        $wechat = app('wechat');
        $wechat->server->setMessageHandler(function($message){
            if ($message->MsgType == 'event') {
                switch ($message->Event) {
                    case 'subscribe':
                        return '欢迎关注!';
                        break;

                    default:
                        # code...
                        break;
                }
            }elseif($message->MsgType == 'voice') {
                return "不好意思，小探不是AlphaGo，听不懂呢！";
            }
            return "欢迎来到最有逼格的民宿文化社群！\n\n";
        });

        Log::info('return response.');

        return $wechat->server->serve();
    }

    public function menus(){
        $wechat = app('wechat');
        dd($wechat->menu->current());
    }
}
