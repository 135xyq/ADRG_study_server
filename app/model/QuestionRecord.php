<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class QuestionRecord extends Model
{
    use SoftDelete;

    // 关联题目记录表
    public function questionHistoryRecord() {
        return $this->hasMany(QuestionHistoryRecord::class);
    }

    // 关联用户表
    public function user() {
        return $this->belongsTo(AppletUser::class);
    }

    // 关联题目分类表
    public function questionCategory() {
        return $this->belongsTo(QuestionCategory::class);
    }

    /**
     * 在删除试卷之前先删除记录
     * @param $questionRecord
     * @return mixed|void
     */
    public static function onBeforeDelete($questionRecord)
    {
        QuestionHistoryRecord::destroy(function($query) use($questionRecord){
            $query->where('question_record_id','=',$questionRecord->id);
        });
    }

}