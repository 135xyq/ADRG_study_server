<?php

namespace app\admin\controller;

use app\model\Question as QuestionModel;
use app\Request;
use think\App;
use app\model\QuestionRecord as QuestionRecordModel;
use app\model\QuestionHistoryRecord as QuestionHistoryRecordModel;

class QuestionRecord extends Base
{
    protected $questionRecord;
    protected $questionHistoryRecord;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->questionRecord = new QuestionRecordModel();
        $this->questionHistoryRecord = new QuestionHistoryRecordModel();
    }

    /**
     * 分页查询获取记录列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getQuestionRecordPage(Request $request)
    {
        $category = $request->param('category', ''); // 分类
        $user = $request->param('user', ''); // 用户
        $type = $request->param('type', -1); // 是否已经完成，-1：不筛选，0:未完成，1：已完成
        $time = $request->param('time', ''); // 时间
        $page = $request->param('page', 1, 'intval');
        $limit = $request->param('limit', 20, 'intval');


        $query = $this->questionRecord->with(['user' => function ($q) {
            $q->field(['id', 'nick_name']);
        }, 'questionCategory' => function ($q) {
            $q->field(['id', 'title']);
        }, 'questionHistoryRecord' => function ($q) {
            $q->field(['id', 'question_record_id']);
        }]);


        if ($category !== '') {
            $query = $query->hasWhere('questionCategory', ['id' => $category]);
        }

        if ($user !== '') {
            $query = $query->hasWhere('user', ['id' => $user]);
        }

        // 筛选类型
        if ($type !== -1) {
            $query->where('is_submit', $type);
        }

        // 筛选时间段
        if (!empty($time)) {
            $query->whereBetweenTime('create_time', $time[0], $time[1]);
        }


        $total = $query->count();
        $res = $query->page($page, $limit)->select();


        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success', $data);
    }

    /**
     * 获取刷题记录
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserSubmitRecordQuestionDetail(Request $request)
    {
        $record = $request->param('record', '');

        if ($record == '') {
            return $this->error('请选择记录！');
        }

        $questionRecord = $this->questionRecord->find($record);

        // 做题记录不存在
        if (empty($questionRecord)) {
            return $this->error('获取记录出错！');
        }

        // 筛选出指定的答题历史记录
        $query = $this->questionHistoryRecord->with(['question' => function ($q) {
            $q->field(['id', 'title', 'parse', 'answer', 'options', 'level', 'type']);
        }])->hasWhere('question', ['status' => 1])->where('question_record_id', '=', $record);
        // 获取题目分类信息
        $recordInfo = $this->questionRecord->with(['questionCategory', 'user' => function ($q) {
            $q->field(['id', 'nick_name']);
        }])->find($record);

        $total = $query->count();
        $data = $query->select();

        $res = [
            'total' => $total,
            'record' => $recordInfo,
            'data' => $data
        ];

        return $this->success('success', $res);

    }


    /**
     * 批量删除刷题记录
     * @param Request $request
     * @return \think\response\Json
     */
    public function deleteQuestionRecord(Request $request) {
        $id = $request->param('id');

        if(!$id){
            return $this->error('请选择删除的记录！');
        }

        $ids = ['id'=>explode(',',$id)];
        $bool = QuestionRecordModel::destroy($ids);
        if($bool){

            // 写入操作日志
            $this->writeDoLog($request->param());

            return $this->success('删除成功！');
        }else{
            return $this->error('删除失败');
        }
    }
}