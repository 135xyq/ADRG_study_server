<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Question extends Model
{

    use SoftDelete;

    // 确定选项和答案的格式，便于输出
    protected $type = [
        'options' => 'json',
        'answer' => 'json'
    ];

    // 关联分类表
    public function questionCategory()
    {
        return $this->belongsTo(QuestionCategory::class);
    }
    /**
     * 监听题目删除，统计分类的题目数量
     * @param $question
     * @return void
     */
    public static function onAfterDelete($question)
    {
        $questionCategory = $question->questionCategory ;
        // 更新分类的题目数
        if($questionCategory ) {
            $questionCategory ->updateStatistics();
        }
    }

    /**
     * 监听题目新增，统计题目数量
     * @param $question
     * @return void
     */
    public static function onAfterInsert($question )
    {
        $questionCategory = $question->questionCategory ;
        // 更新分类的题目数
        if($questionCategory ) {
            $questionCategory ->updateStatistics();
        }
    }
}