<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Video extends Model
{
    use SoftDelete;

    // 关联评论表
    public function studyCategory()
    {
        return $this->belongsTo(StudyCategory::class);
    }

    // 关联评论表
    public function comment()
    {
        return $this->hasMany(Comment::class);
    }
}