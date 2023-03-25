<?php

namespace app\validate;

use think\Validate;

class QuestionCategoryValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'title' => 'require|max:50|min:1',
        'status' => 'require|in:0,1'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'title.require' => '分类名不能为空',
        'title.max' => '分类名长度不能超过50',
        'title.min' => '分类名不能为空',
        'status.require' => '状态不能为空',
        'status.in' => '状态设置错误'
    ];

    protected $scene = [
        'update' => ['id'],
        'delete' => ['id'],
        'add' => ['title','status']
    ];

}