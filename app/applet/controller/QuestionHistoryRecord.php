<?php

namespace app\applet\controller;

use app\model\QuestionHistoryRecord as QuestionHistoryRecordModel;
use app\model\QuestionRecord as QuestionRecordModel;
use app\model\QuestionCategory as QuestionCategoryModel;
use app\model\Question as QuestionModel;
use app\Request;
use think\App;

class QuestionHistoryRecord extends Base
{
    protected $userId;
    protected $questionHistoryRecord;
    protected $questionRecord;
    protected $questionCategory;
    protected $question;


    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->userId = $this->userInfo['id'];
        $this->questionHistoryRecord = new QuestionHistoryRecordModel();
        $this->questionRecord = new QuestionRecordModel();
        $this->questionCategory = new QuestionCategoryModel();
        $this->question = new QuestionModel();
    }

    /**
     * 根据试卷获取题目
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecordInfo(Request $request)
    {
        $recordId = $request->param('record', '');

        if ($recordId === '') {
            return $this->error('出错了');
        }

        // 记录不存在
        $record = $this->questionRecord->find($recordId);
        if (empty($record)) {
            return $this->error('出错了！');
        }

        // 已经提交的试卷继续答题
        if ($record->is_submit !== 0) {
            return $this->error('试卷已提交！');
        }

        // 题目字段
        $field = ['id', 'type', 'title', 'level', 'options', 'question_category_id', 'status'];

        $res = $this->questionHistoryRecord->with(['question' => function ($q) use ($field) {
            $q->field($field);
        }])->where('question_record_id', '=', $recordId)->select()->toArray();

        $questionList = [];

        // 只需要获取题目信息
        foreach ($res as $question) {
            $questionList[] = $question['question'];
        }

        $data = [
            'total' => count($questionList),
            'record_id' => $recordId,
            'data' => $questionList
        ];

        return $this->success('success', $data);
    }

    /**
     * 获取指定分类下的所有错题id
     * @param Request $request
     * @return \think\response\Json
     */
    public function getAllErrorQuestionId(Request $request)
    {
        $category = $request->param('category', '');

        // 分类不能为空
        if ($category === '') {
            return $this->error('获取题目列表失败');
        }

        $categoryInfo = $this->questionCategory->find($category);


        // 获取错题的列表id
        $errorQuestionList = $this->questionHistoryRecord->getAllErrorQuestionId($this->userId, $category);

        $data = [
            'category' => $categoryInfo,
            'ids' => $errorQuestionList
        ];

        return $this->success('success', $data);
    }

    /**
     * 获取题目详情
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getQuestionDetail(Request $request)
    {
        $id = $request->param('question', '');

        if ($id == '') {
            return $this->error('获取题目详情失败');
        }

        $res = $this->question->find($id);

        return $this->success('success', $res);
    }

    /**
     * 获取用户每一种分类错题的数量
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getQuestionCategoryCount(Request $request)
    {

        // 获取用户试卷出现的分类列表
        $categoryList = $this->questionRecord->where('applet_user_id', '=', $this->userId)->where('is_submit', '<>', 0)->column('question_category_id');

        // 得到=去重后的用户出题分类id
        $categoryIdList = array_values(array_unique($categoryList));

        $data = [];
        foreach ($categoryIdList as $category) {
            // 获取指定分类的错题数量
            $errorQuestionList = $this->questionHistoryRecord->getAllErrorQuestionId($this->userId, $category);
            $errorQuestionCount = count($errorQuestionList);

            // 没有错题的分类舍弃
            if ($errorQuestionCount > 0) {
                // 获取分类数据
                $categoryInfo = $this->questionCategory->find($category);

                // 数据组合
                $categoryInfo['errorQuestionCount'] = $errorQuestionCount;
                $data[] = $categoryInfo;
            }
        }

        return $this->success('success', $data);
    }
}