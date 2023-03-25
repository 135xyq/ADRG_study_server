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

    /**
     * 监听分类的删除事件,在删除前先删除分类下的题目
     *
     * @param $questionCategory
     * @return mixed|void
     */
    public static function onBeforeDelete($questionCategory)
    {
        Question::where('question_category_id',$questionCategory->id)->select()->delete();
    }

    public function updateStatistics()
    {
        // 统计问题的数量
        $questionCount = Question::where('question_category_id', $this->id)->count();

        // 更新文章表中评论数量
        $this->question_count = $questionCount;

        $this->save();
    }
}