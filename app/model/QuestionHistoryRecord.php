<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class QuestionHistoryRecord extends Model
{
    use SoftDelete;

    // 确定答案的格式，便于输出
    protected $type = [
        'answer' => 'json'
    ];

    // 关联出题记录表
    public function questionRecord() {
        $this->belongsTo(QuestionRecord::class);
    }

    // 关联题目表
    public function question() {
        $this->belongsTo(Question::class);
    }

}