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
                Session::set('Token',md5($data['account'].$data['password']));
                $this->result = ['token'=>md5($data['account'].$data['password'])];
                return $this->success('登录成功！');
            }else{
                return $this->error('密码错误！');
            }
        }else{
            return $this->error('账号不存在！');
        }
    }

}
