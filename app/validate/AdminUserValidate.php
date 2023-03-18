<?php

namespace app\validate;

use think\Validate;

class AdminUserValidate extends Validate
{
    protected $rule = [
        'id' => 'require|number',
        'account' => 'require|max:10',
        'password' => 'require',
        'title' => 'max:10'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'account.require' => '账号不能为空',
        'account.max' => '账号长度不能超过10',
        'password.require' => '密码不能为空',
    ];

    protected $scene = [
        'login' => ['account','password'],
        'edit' => ['id'],
        'updatePassword' => ['password']
    ];
}
