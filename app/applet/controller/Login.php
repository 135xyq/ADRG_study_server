<?php

namespace app\applet\controller;

use app\common\BaseServer;
use app\model\AppletConfig;
use app\model\AppletUser;;
use think\Request;

class Login extends BaseServer
{

    public function index(Request $request)
    {
        // 获取前端传回的code
        $code = $request->param('code');
        $nickName = $request->param('nickName');
        $avatarUrl = $request->param('avatarUrl');
        $gender = $request->param('gender');

        // 获取小程序的配置信息 APPID和secret
        $res = AppletConfig::find(1);
        $info=curl_init();
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';
        $url = sprintf($url,$res->appid,$res->secret,$code);
        curl_setopt($info,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($info,CURLOPT_HEADER,0);
        curl_setopt($info,CURLOPT_NOBODY,0);
        curl_setopt($info,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($info,CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($info,CURLOPT_URL,$url);
        $output= curl_exec($info);
        curl_close($info);
        // 对响应进行处理
        if (property_exists(json_decode($output),'errcode')) {
            // 处理cURL错误
            $this->result = '获取信息错误！';
            return $this->error();
        } else {
            // 处理响应数据
            $info = json_decode($output);//openid和sesion_key
            $isExist = AppletUser::where('openid',$info->openid)->find();
            // openid已存在证明不是新用户
            if($isExist != null){
                $user = AppletUser::where('openid',$info->openid)
                    ->field('id,nick_name as nickName,gender,avatar,create_time,update_time')->find();
                $user->new = 1;//是否为新用户，1 为新 0为老
                $this->result = $user;
                return $this->success();
            }else{
                $user = AppletUser::create([
                    'openid' => $info->openid,
                    'session_key' => $info->session_key,
                    'nick_name' => $nickName,
                    'avatar' => $avatarUrl,
                    'gender' => $gender
                ]);
                $this->result = [
                    'id' => $user->$user->id,
                    'nickName' => $user->nick_name,
                    'gender' => $user->gender,
                    'avatar' => $user->avatar,
                    'create_time' => $user->create_time,
                    'update_time' => $user->update_time
                ];
                return $this->success();
            }
        }

    }
}