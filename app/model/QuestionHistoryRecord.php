<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class QuestionHistoryRecord extends Model
{
    use SoftDelete;

    protected $filed = ['id', 'type', 'title', 'level', 'options', 'question_category_id', 'status'];

    // 确定答案的格式，便于输出
    protected $type = [
        'answer' => 'json'
    ];

    // 关联出题记录表
    public function questionRecord()
    {
        return $this->belongsTo(QuestionRecord::class);
    }

    // 关联题目表
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * 获取用户已做题的个数
     * @param $user
     * @param $category
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function getUserDoneQuestionCount($user, $category)
    {
        // 获取记录表中的那些已经提交的错题
        $where = (new QuestionRecord)->where('applet_user_id', '=', $user)
            ->where('is_submit', '=', 1)
            ->where('question_category_id','=',$category);


        // 要去重
        $data =  $this->hasWhere('questionRecord', $where)->column('question_id');

        // 去重后的刷题数量
        return count(array_unique($data));
    }

    /**
     * 获取错题列表
     * @param $user
     * @param $category
     * @param $level
     * @return Question
     */
    public function getErrorQuestion($user, $category, $level)
    {
        // 获取记录表中的那些已经提交的错题
        $where = QuestionRecord::where('applet_user_id', '=', $user)->where('is_submit', '=', '1');

        // 筛选题目的难度个分类
        if ($level !== '') {
            $questionWhere = Question::where('question_category_id', '=', $category)->where('level', '=', $level);
        } else {
            $questionWhere = Question::where('question_category_id', '=', $category);
        }

        $ids = $this->hasWhere('questionRecord', $where)
            ->hasWhere('question', $questionWhere)
            ->where('is_current', '=', 0)
            ->column('question_id');

        $data = (new Question)->whereIn('id', $ids)->where('status', '=', 1)->field($this->filed);
        return $data;
    }


    /**
     * 获取没有做过的新题
     * @param $user
     * @param $category
     * @param $level
     * @return Question
     */
    public function getNewQuestion($user, $category, $level)
    {
        // 获取记录表中的那些已经提交
        $where = QuestionRecord::where('applet_user_id', '=', $user)->where('is_submit', '=', '1');

        // 获取用户已经做过的题目列表
        $doneIds = $this->hasWhere('questionRecord', $where)->column('question_id');

        // 筛选题目的难度个分类
        if ($level !== '') {
            $questionWhere = Question::where('question_category_id', '=', $category)->where('level', '=', $level);
        } else {
            $questionWhere = Question::where('question_category_id', '=', $category);
        }

        // 获取没有做过的题目
        $data = (new Question)->whereNotIn('id', $doneIds)->where($questionWhere)->where('status', '=', 1)->field($this->filed);

        return $data;
    }

    /**
     * 获取所有的题目
     * @param $category
     * @param $level
     * @return Question
     */
    public function getAllQuestion($category, $level)
    {

        // 筛选题目的难度个分类
        if ($level !== '') {
            $questionWhere = Question::where('question_category_id', '=', $category)->where('level', '=', $level);
        } else {
            $questionWhere = Question::where('question_category_id', '=', $category);
        }

        // 获取所有的题目
        $data = (new Question)->where($questionWhere)->where('status', '=', 1)->field($this->filed);

        return $data;
    }

}