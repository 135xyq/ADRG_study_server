<?php

namespace app\validate;

use think\Validate;

class AppletUserSetValidate extends Validate
{
    protected $rule = [
        'question_type' => 'in:1,2,3,4',
        'question_count' => 'in:10,15,20,25',
        'level' => 'in:1,2,3,4'
    ];

    protected $message = [
        'question_type.in' => '题目出题方式不合法',
        'question_count.in' => '题目出题个数不合法',
        'level.in' => '题目难度不合法'
    ];

    protected $scene = [
        'edit' => ['question_type','question_count','level']
    ];
}