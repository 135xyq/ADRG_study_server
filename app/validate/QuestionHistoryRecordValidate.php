<?php

namespace app\validate;

use think\Validate;

class QuestionHistoryRecordValidate extends Validate
{
    protected $rule = [
        'id' => 'require',
        'question_record_id' => 'require',
        'question_id' => 'require',
        'is_current' => 'require|in:0,1,2',
        'current_probability' => 'require|max:1',
    ];

    protected $message = [
        'id.require' => 'id不能为空',
        'question_record_id.require'  => '出题记录不能为空',
        'question_id.require' => '题目不能为空',
        'is_current.require' => '题目的正确性不能为空',
        'is_current.in' => '正确性状态不合法',
        'current_probability.require' => '接近答案的几率',
        'is_current.max' => '几率不合法',
    ];

    protected $scene = [
        'add' => ['question_record_id','question_id'],
        'delete' => ['id'],
        'update' => ['id']
    ];
}