<?php

namespace app\validate;

use think\Validate;

class AppletConfigValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'appid' => 'require|max:255',
        'secret' => 'require|max:255'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'appid.require' => 'appid不能为空',
        'secret.require' => 'secret不能为空'
    ];
    protected $scene =[
        'update' => ['id'],
        'add' => ['appid','secret']
    ];
}