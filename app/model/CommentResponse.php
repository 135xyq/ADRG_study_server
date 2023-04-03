<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class CommentResponse extends Model
{
    use SoftDelete;

    /**
     * 关联用户表
     * @return \think\model\relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(AppletUser::class);
    }

    /**
     * 关联评论表
     * @return \think\model\relation\BelongsTo
     */
    public function comment() {
        return $this->belongsTo(Comment::class);
    }
}