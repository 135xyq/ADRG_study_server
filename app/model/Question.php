<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Question extends Model
{

    use SoftDelete;

    // 关联分类表
    public function questionCategory() {
        return $this->belongsTo(QuestionCategory::class);
    }
}