<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class QuestionCategory extends Model
{
    use SoftDelete;

    // 关联分类表
    public function question() {
        return $this->hasMany(Question::class);
    }
}