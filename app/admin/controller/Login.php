<?php

namespace app\admin\controller;

use app\common\BaseServer;
use app\model\AdminUser;
use app\Request;
use app\validate\AdminUserValidate;
use think\Exception;
use think\exception\ValidateException;
use think\facade\Session;
use think\Validate;

class Login extends BaseServer
{
    // 登录
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
                Session::set('token',md5($data['account'].$data['password']));
                $this->result = ['token'=>md5($data['account'].$data['password'])];
                // 要保存的用户信息
                $userInfo = [
                    'title' => $res['title'],
                    'avatar' => $res['avatar'],
                    'account' => $res['account']
                ];
                // 将用户信息存到session中，用于获取管理员信息
                Session::set('admin_info',$userInfo);
                return $this->success('登录成功！');
            }else{
                return $this->error('密码错误！');
            }
        }else{
            return $this->error('账号不存在！');
        }
    }

    // 获取登录用户的账号信息
    public function getInfo() {
        $res = Session::get('admin_info');
        $this->result = $res;
        return $this->success();
    }


    // 退出登录
    public function logout(Request $request) {
        $token = $request->param('token');
        if(empty($token)){
            return $this->error('请输入token！(请确保当前在登录状态)');
        }
        if($token !== Session::get('token')){
            return $this->error('token输入错误！');
        }
        Session::delete('token');
        Session::delete('admin_info');
        return $this->success('退出成功!');
    }


    // 修改密码
    public function updatePassword(Request $request) {
        $oldPassword = $request->param('oldPassword');
        $newPassword = $request->param('newPassword');
        $token = $request->param('token');
        if(Session::has('token')){
            // token输入错误，无法修改
            if($token !== Session::get('token')){
                return $this->error('权限不够！');
            }
        }else{
            // 未登录
            return $this->error('无法修改！');
        }
        $user = AdminUser::where('account',Session::get('admin_info')['account'])->find();
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
                // 密码修改成功，删除token,前端手动退出登录
                // Session::delete('token');
                // Session::delete('admin_info');
                return $this->success('密码修改成功！');
            }else {
                return $this->error('密码修改失败！');
            }
        }
    }

}
