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
}