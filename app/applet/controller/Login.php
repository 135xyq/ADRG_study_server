<?php

namespace app\applet\controller;

use app\common\BaseServer;
use app\model\AppletConfig;
use app\model\AppletUser;;

use think\facade\Cache;
use think\Request;

class Login extends BaseServer
{

    public function login(Request $request)
    {

        // 获取前端传回的code
        $code = $request->param('code');
        $nickName = $request->param('nickName');
        $avatarUrl = $request->param('avatarUrl');
        $gender = $request->param('gender');

        // 获取小程序的配置信息 APPID和secret
        $config = AppletConfig::select();
        $res = $config[0];

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

            $token = md5(uniqid("applet_user")); // 生成唯一的token

            $isExist = AppletUser::where('openid',$info->openid)->find();
            // openid已存在证明不是新用户
            if($isExist != null){
                $user = AppletUser::where('openid',$info->openid)
                    ->field('id,nick_name as nickName,gender,avatar,create_time,update_time')->find();

                $user->new = 1;//是否为新用户，1 为新 0为老

                Cache::set($token,json_encode($user,JSON_UNESCAPED_UNICODE)); // 将用户登录信息存到缓存中

                return $this->success('登录成功！',$user);
            }else{

                $find = 1;
                $userName = $nickName;
                while(!empty($find)) {
                    // 新用户随机生成用户名
                    $userName = $this->str_shuffle();
                    // 防止有重复的昵称
                    $find = AppletUser::where('nick_name','=',$userName)->count();
                }

                $user = AppletUser::create([
                    'openid' => $info->openid,
                    'session_key' => $info->session_key,
                    'nick_name' => $userName,
                    'avatar' => $avatarUrl,
                    'gender' => $gender
                ]);

                Cache::set($token,json_encode($user,JSON_UNESCAPED_UNICODE)); // 将用户登录信息存到缓存中

                $data = [
                    'id' => $user->id,
                    'nickName' => $user->nick_name,
                    'gender' => $user->gender,
                    'avatar' => $user->avatar,
                    'create_time' => $user->create_time,
                    'update_time' => $user->update_time
                ];
                return $this->success('登陆成功！',$data);
            }
        }
    }


    /**
     * 退出登录
     * @param Request $request
     * @return \think\response\Json
     */
    public function logout(Request $request) {
        $token = $request->param('token');

        if(empty($token)){
            return $this->error("请输入token");
        }
        Cache::delete('token');

        return $this->success("退出登录成功");
    }



    /**
     * 生成随机用户名
     * @return str
     */
    public function str_shuffle()
    {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charsLength = bcsub(strlen($chars), 1);
        $username = "";
        for ( $i = 0; $i < 6; $i++ )
        {
            $username .= $chars[mt_rand(0, $charsLength)];
        }
        // 打乱顺序
        return 'ADRG_Study_'.str_shuffle($username.str_shuffle(time()));
    }

}