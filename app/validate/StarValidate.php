<?php

namespace app\validate;


use think\Validate;

class StarValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'applet_user_id' => 'require',
    ];

    protected $message = [
        'id.require' => '收藏id不能为空！',
        'applet_user_id.require' => '用户名不能为空！',
    ];

    protected $scene =[
        'add' => ['applet_user_id'],
        'delete' => ['id'],
    ];
}