<?php

namespace app\validate;

use think\Validate;

class StudyHistoryValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'type' => 'require|in:1,2,3'
    ];

    protected $message = [
        'id.require' => '学习记录id必须存在',
        'type.require' => '学习类型不能为空',
        'type.in' => '类型不合法'
    ];

    protected $scene = [
        'add' => ['type'],
        'page' => ['type'],
        'delete' => ['id'],
    ];
}