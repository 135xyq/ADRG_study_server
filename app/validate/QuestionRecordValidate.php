<?php

namespace app\validate;

use think\Validate;

class QuestionRecordValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'question_category_id' => 'require',
        'applet_user_id' => 'require',
        'is_submit' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'question_category_id.require'  => '题目分类不能为空',
        'applet_user_id.require' => '所属用户不能为空',
        'is_submit.require' => '请选择状态',
        'is_submit.in' => '状态不合法',
    ];

    protected $scene = [
        'add' => ['question_category_id','applet_user_id'],
        'delete' => ['id'],
        'update' => ['id']
    ];
}