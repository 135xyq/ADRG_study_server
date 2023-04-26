<?php

namespace app\validate;

use think\Validate;

class AppletConfigValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'appid' => 'require|max:255',
        'secret' => 'require|max:255',
        'is_auto_check_comment' => 'in:0,1',
        'is_auto_check_user_name' => 'in:0,1',
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'appid.require' => 'appid不能为空',
        'secret.require' => 'secret不能为空',
        'is_auto_check_comment.in' => '是否自动审核评论不合法',
        'is_auto_check_user_name.in' => '是否自动审核用户名不合法'
    ];
    protected $scene =[
        'update' => ['id','is_auto_check_comment','is_auto_check_user_name'],
        'add' => ['appid','secret','is_auto_check_comment','is_auto_check_user_name']
    ];
}