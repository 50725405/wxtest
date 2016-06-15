<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use EasyWeChat\Message\Text;
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
                        return $this->handleEvent($message);
                        break;

                    default:
                        # code...
                        break;
                }
            }elseif($message->MsgType == 'voice') {
                $str="不好意思，小探不是AlphaGo，听不懂呢！";
                return  new Text(['content'=>$str]);
            }

        });

        Log::info('return response.');

        return $wechat->server->serve();
    }

    public function menus(){
        $wechat = app('wechat');
        dd($wechat->menu->current());
    }


    public function handleEvent($obj)
    {
        $wxId = $obj->FromUserName;
        $content = '';
        switch ($obj->Event) {
            case "subscribe":   //关注事件
                $content = $wxId."请注册，注册后即可快速查询水电费\n<a href='".action('WeChatController@menus')."'>点击这里，立即注册</a>\n";
                break;
            case "unsubscribe": //取消关注事件
                $content = "";
                break;
            case 'CLICK': {
                switch ($obj->EventKey) {
                    case '天然气咨询':
                        $content = [
                            "title" => "管道天然气来啦！",
                            "description" => "关于安装管道天然气的意见征询的通知。",
                            "image" => "http://www.hcfsz.com/cf/images/trq.jpg",
                            "url" => "http://www.hcfsz.com/cf/trq/trqyj.php?wxId=$wxId"
                        ];
                        break;
                    case '水电费查询':
                        if($this->isSignUp($wxId)){
                            $content = '直接输入时间即可查询您该月的水电详情，如（201509）。';
                        }else{
                            $content = "请注册，注册后即可快速查询水电费\n<a href='".action('WechatController@signUp', ['wxId'=>$wxId])."'>点击这里，立即注册</a>\n";
                        }
                        break;
                    case '天气预报':
                        $content = "请输入城市名+天气，如: 深圳天气";
                        break;
                    case '意见反馈':
                        if($this->isSignUp($wxId)){
                            $content = [
                                'title'=>'反馈村夫',
                                'description'=>'您的意见是我们前进的动力。',
                                'image'=>asset('img/feedback/shakehand.jpg'),
                                'url'=>action('WechatController@feedback', ['wxId'=>$wxId])//url('/feedback',['wxId'=>$wxId]),
                            ];
                        }else{
                            $content = "请注册，注册后即可快速查询水电费\n<a href='".action('WechatController@signUp', ['wxId'=>$wxId])."'>点击这里，立即注册</a>\n";
                        }
                        break;
                    case '利生活':
                        $content = "更多精彩，敬请期待。";
                        break;
                    case '停车缴费':
                        $content = '敬请期待';
                        break;
                    default:
                        $content = "敬请期待";
                        break;
                }
                break;
            }
            default:
                break;
        }
        if(is_array($content)){
            $ret = new News($content);
        }else{
            $ret = new Text(['content'=>$content]);
        }
        return $ret;
    }
}
