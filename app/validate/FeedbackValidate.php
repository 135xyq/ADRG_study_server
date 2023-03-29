<?php

namespace app\validate;

use think\Validate;

class FeedbackValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'applet_user_id' => 'require',
        'content' => 'require'
    ];

    protected $message = [
        'id.require' => '用户反馈id不能为空！',
        'applet_user_id.require' => '用户名不能为空！',
        'content.require' => '反馈内容不能为空'
    ];

    protected $scene =[
        'add' => ['applet_user_id','content'],
        'delete' => ['id'],
        'response' => ['id']
    ];
}