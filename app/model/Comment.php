<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Comment extends Model
{
    use SoftDelete;

    // 获取评论用户
    public function applet_user() {
        return $this->belongsTo(AppletUser::class);
    }

    // 关联视频表
    public function video()
    {
        return $this->belongsTo(Comment::class);
    }

    // 关联文章表
    public function article()
    {
        return $this->belongsTo(Comment::class);
    }

    // 获取评论的父评论信息
    public function parentComment()
    {
        return $this->belongsTo('Comment', 'parent_id');
    }

    // 获取评论的子评论信息
    public function childComments()
    {
        return $this->hasMany('Comment', 'parent_id');
    }
}