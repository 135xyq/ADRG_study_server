<?php

namespace app\validate;

use think\Validate;

class ArticleValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'title' => 'require',
        'content' => 'require',
        'show_cover' => 'require|in:0,1',
        'status' => 'require|in:0,1',
        'study_category_id' => 'require',
        'thumbnail_url' => 'require',
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'title.require' => '文章标题不能为空',
        'content.require' => '文章内容不能为空',
        'status.require' => '状态不能为空',
        'status.in' => '状态不合法',
        'show_cover.require' => '是否优先展示状态不能为空',
        'show_cover.in' => '是否优先展示状态不合法',
        'study_category_id.require' => '所属分类不能为空',
        'thumbnail_url.require' => '封面不能为空'
    ];

    protected $scene = [
        'add' => ['title','show_cover','content','status','thumbnail_url','content'],
        'delete' =>['id'],
        // 'update' => ['id']
        'update' => ['id','title','show_cover','content','status','thumbnail_url','content'],
    ];
}