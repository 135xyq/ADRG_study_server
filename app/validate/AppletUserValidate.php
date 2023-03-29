<?php

namespace app\validate;

use think\Validate;

class AppletUserValidate extends Validate
{
    protected $rule =[
        'nick_name' => 'length:2,30',
    ];

    protected $message = [
      'nick_name.length' => '昵称长度为2到30'
    ];
}