<?php

namespace app\applet\controller;
use app\model\QuestionHistoryRecord as QuestionHistoryRecordModel;
use app\model\QuestionRecord as QuestionRecordModel;
use app\Request;
use think\App;

class QuestionHistoryRecord extends Base
{
    protected $userId;
    protected $questionHistoryRecord;
    protected $questionRecord;


    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->userId = $this->userInfo['id'];
        $this->questionHistoryRecord = new QuestionHistoryRecordModel();
        $this->questionRecord = new QuestionRecordModel();
    }

    /**
     * 根据试卷获取题目
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecordInfo(Request $request) {
        $recordId = $request->param('record','');

        if($recordId === '') {
            return $this->error('出错了');
        }

        // 记录不存在
        $record = $this->questionRecord->find($recordId);
        if(empty($record)) {
            return $this->error('出错了！');
        }

        // 已经提交的试卷继续答题
        if($record->is_submit === 1) {
            return $this->error('试卷已提交！');
        }

        // 题目字段
        $field = ['id', 'type', 'title', 'level', 'options', 'question_category_id', 'status'];

        $res = $this->questionHistoryRecord->with(['question'=>function($q) use ($field){
            $q->field($field);
        }])->where('question_record_id','=',$recordId)->select()->toArray();

        $questionList = [];

        // 只需要获取题目信息
        foreach ($res as $question){
            $questionList[] = $question['question'];
        }

        $data = [
            'total' => count($questionList),
            'record_id' => $recordId,
            'data' => $questionList
        ];

        return $this->success('success',$data);
    }
}