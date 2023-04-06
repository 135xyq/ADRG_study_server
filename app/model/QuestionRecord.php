<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class QuestionRecord extends Model
{
    use SoftDelete;

    // 关联题目记录表
    public function questionHistoryRecord() {
        $this->hasMany(QuestionHistoryRecord::class);
    }

    // 关联用户表
    public function user() {
        $this->belongsTo(AppletUser::class);
    }

    // 关联题目分类表
    public function questionCategory() {
        return $this->belongsTo(QuestionCategory::class);
    }
}