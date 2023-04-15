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
    // 关联题目记录表
    public function questionHistoryRecord()
    {
        return $this->hasMany(QuestionHistoryRecord::class);
    }


    public function validateQuestion($question,$answer) {
        // 单选题
        if($question['type'] === 0) {
            dump($question['answer'],$answer);

            // 判断两个数组是否相等，答案是否正确（比较两个数组的差异）
            $result = array_diff_assoc($question['answer'],$answer);

            dump($result);
            // 答案正确时，$result为空数组
            if(count($result) === 0) {
                dump('答案正确');
            }else{
                dump('答案错误');
            }

        } else if($question['type'] === 1) {
            // 多选题

            dump($question['answer'],$answer);

            // 判断两个数组是否相等，答案是否正确（比较两个数组的差异）
            $result = array_diff_assoc($question['answer'],$answer);

            dump($result);

            // 答案正确时，$result为空数组
            if(count($result) === 0) {
                dump('答案正确');
            }else{
                dump('答案错误');
            }
        }else if($question['type'] === 2) {
            // 填空题
            dump('填空题');

            dump($question['answer'],$answer);

            // 判断两个数组是否相等，答案是否正确（比较两个数组的差异）
            $result = array_diff_assoc($question['answer'],$answer);

            dump($result);

            // 答案正确时，$result为空数组
            if(count($result) === 0) {
                dump('答案正确');
            }else{
                dump('答案错误');
            }
        }else if($question['type'] === 3) {
            // 问答题
            dump('简答题');
        }
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