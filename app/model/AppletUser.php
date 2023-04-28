<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class AppletUser extends Model
{
    use SoftDelete;

    // 关联用户反馈表
    public function feedback() {
        return $this->hasMany(Feedback::class);
    }

    // 关联评论回复表
    public function response()
    {
        return $this->hasMany(CommentResponse::class);
    }

    // 关联用户设置表
    public function userSet()
    {
        return $this->hasOne(AppletUserSet::class);
    }

    // 关联评论表
    public function comment()
    {
        return $this->hasMany(Comment::class);
    }

    // 关联收藏表
    public function star()
    {
        return $this->hasMany(Star::class);
    }

    // 关联点赞表
    public function like()
    {
        return $this->hasMany(Like::class);
    }

    // 关联做题记录表
    public function questionRecord()
    {
        return $this->hasMany(QuestionRecord::class);
    }
}