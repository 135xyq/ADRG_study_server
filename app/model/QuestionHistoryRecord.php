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
        return $this->belongsTo(QuestionRecord::class);
    }

    // 关联题目表
    public function question() {
        return $this->belongsTo(Question::class);
    }

    /**
     * 获取错题列表
     * @param $user
     * @return array|\think\Collection|\think\db\BaseQuery[]
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getErrorQuestion($user) {
        // 获取记录表中的那些已经提交的错题
        $where = QuestionRecord::where('applet_user_id','=',$user)->where('is_submit','=','1');

        $data = $this->hasWhere('questionRecord',$where)->where('is_current','=',0)->select();

        return $data;
    }


    /**
     * 获取没有做过的新题
     * @param $user
     * @return Question[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getNewQuestion($user) {
        // 获取记录表中的那些已经提交
        $where = QuestionRecord::where('applet_user_id','=',$user)->where('is_submit','=','1');

        // 获取用户已经做过的题目列表
        $doneIds = $this->hasWhere('questionRecord',$where)->column('question_id');

        // 获取没有做过的题目
        $data = Question::whereNotIn('id',$doneIds)->select();

        return $data;
    }

}