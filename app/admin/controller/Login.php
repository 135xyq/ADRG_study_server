<?php

// 管理员登录

namespace app\admin\controller;

use app\common\BaseServer;
use app\model\AdminSession;
use app\model\AdminUser;
use app\Request;
use app\validate\AdminUserValidate;
use think\App;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Session;
use think\Validate;
use app\admin\controller\Base;

class Login extends Base
{

    public function login(Request $request){
        $data = [];
        if(!empty($request->param('account'))){
            $data['account'] = $request->param('account');
        }
        if(!empty($request->param('password'))){
            $data['password'] = $request->param('password');
        }
        try {
            Validate(AdminUserValidate::class)->scene('login')->check($data);
        }catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        // 在数据库中查找账号和密码
        $res = AdminUser::where('account',$data['account'])->find();
        if($res){
            // 验证密码是否正确
            if($res['password'] === md5($data['password'])){
                // Session::set('token',md5($data['account'].$data['password']));
                // $this->result = ['token'=>md5($data['account'].$data['password'])];
                // // 要保存的用户信息
                // $userInfo = [
                //     'title' => $res['title'],
                //     'avatar' => $res['avatar'],
                //     'account' => $res['account']
                // ];
                // // 将用户信息存到session中，用于获取管理员信息
                // Session::set('admin_info',$userInfo);
                //

                // 写入登录日志
                event('LoginLog', ['user' => $res['title']]);

                $token = $this->setToken($res); // 获取登录的token
                $result = ['token'=>$token];
                return $this->success('登录成功！',$result);
            }else{
                return $this->error('密码错误！');
            }
        }else{
            return $this->error('账号不存在！');
        }
    }


    // 获取登录用户的账号信息
    public function getInfo() {
        // 获取token
        $token = $this->getToken();

        // 获取到登录用户的id,通过id管理员详情
        $tokenInfo = AdminSession::where('token', $token)->find();
        $id = json_decode($tokenInfo['data'])->id;
        $user = new AdminUser();
        $res = $user->withoutField('password,id')->find($id);

        return $this->success('success',$res);
    }


    // 退出登录
    public function logout(Request $request) {
        // 获取token
        $token = $this->getToken();
        // 将登录token的状态status设置为0
        $token = $this->getToken();
        AdminSession::where('token', $token)->update([
            'status' => 0
        ]);

        return $this->success('退出成功!');
    }


    // 修改密码
    public function updatePassword(Request $request) {
        $oldPassword = $request->param('oldPassword');
        $newPassword = $request->param('newPassword');
        $token = $this->getToken(); // 获取token

        $tokenInfo = AdminSession::where('token','=', $token)->find();

        if(empty($tokenInfo)){
            return $this->error('token有误！');
        }

        $id = json_decode($tokenInfo['data'])->id; // 获取管理员id
        $user = AdminUser::find($id);

        if(md5($oldPassword) !== $user['password']) {
            return $this->error('原密码输入错误！');
        } else{
            try{
                Validate(AdminUserValidate::class)->scene('updatePassword')->check([
                    'password' => $newPassword
                ]);
            }catch(ValidateException $e){
                return $this->error($e->getError());
            }
            $user->password = md5($newPassword);
            $bool = $user->save();
            if($bool) {
                // 将登录token的状态status设置为0
                $token = $this->getToken();
                AdminSession::where('token', $token)->update([
                    'status' => 0
                ]);

                // 写入操作日志
                $this->writeDoLog($request->param());

                return $this->success('密码修改成功！');
            }else {
                return $this->error('密码修改失败！');
            }
        }
    }

}
