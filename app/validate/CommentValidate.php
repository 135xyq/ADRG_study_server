<?php

namespace app\validate;


use think\Validate;

class CommentValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'applet_user_id' => 'require',
        'content' => 'require|max:1000',
        'status' => 'require|in:0,1,2',
    ];

    protected $message = [
        'id.require' => '评论id不能为空！',
        'applet_user_id.require' => '用户名不能为空！',
        'content.require' => '评论内容不能为空',
        'content.max' => '评论内容过长',
        'status.require' => '状态不能为空',
        'status.in' => '状态不合法'
    ];

    protected $scene =[
        'add' => ['applet_user','content','status'],
        'delete' => ['id'],
        'audit' => ['id','status'],
    ];
}