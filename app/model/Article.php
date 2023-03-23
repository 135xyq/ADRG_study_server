<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Article extends Model
{
    use SoftDelete;

    // 关联分类表
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