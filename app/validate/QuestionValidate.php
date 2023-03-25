<?php

namespace app\validate;

class QuestionValidate
{
    protected $rule = [
        'id' => 'require',
        'type' => 'require|in:0,1,2,3',
        'title' => 'require',
        'level' => 'require|in:0,1,2',
        'answer' => 'require',
        'status' => 'require|in:0,1',
    ];

    protected $message = [
        'id.require' => '问题id不能为空',
        'type.require' => '问题的种类不能为空',
        'type.in' => '问题的种类不合法',
        'title.require' => '题目不能为空',
        'level.require'=>'问题的级别不能为空',
        'level.in' => '问题的级别不合法',
        'answer.require' => '答案不能为空',
        'status.require' => '问题的状态不能为空',
        'status.in' => '问题的状态不合法'
    ];

    protected $scene = [
        'add' => ['title','type','level','answer','status'],
        'delete' => ['id'],
        'update' => ['id'],
    ];
}