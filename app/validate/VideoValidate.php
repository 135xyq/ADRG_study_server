<?php

namespace app\validate;

use think\Validate;

class VideoValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'title' => 'require',
        'url' => 'require',
        'show_cover' => 'require|in:0,1',
        'status' => 'require|in:0,1',
        'study_category_id' => 'require'
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'title.require' => '标题不能为空',
        'url.require' => '视频地址不能为空',
        'status.require' => '状态不能为空',
        'status.in' => '状态不合法',
        'show_cover.require' => '是否优先展示状态不能为空',
        'show_cover.in' => '是否优先展示状态不合法',
        'study_category_id.require' => '所属分类不能为空'
    ];

    protected $scene = [
        'add' => ['title','url','show_cover','status','show_cover'],
        'delete' =>['id'],
        'update' => ['id','title','url','show_cover','status','show_cover'],
    ];
}