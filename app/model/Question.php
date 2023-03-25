<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Question extends Model
{

    use SoftDelete;

    // 确定选项和答案的格式，便于输出
    protected $type = [
        'options' => 'object',
        'answer' => 'array'
    ];

    // 关联分类表
    public function questionCategory()
    {
        return $this->belongsTo(QuestionCategory::class);
    }
}