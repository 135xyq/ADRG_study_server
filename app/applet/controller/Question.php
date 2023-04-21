<?php

namespace app\applet\controller;

use app\model\QuestionRecord;
use app\Request;
use think\App;
use app\model\Question as QuestionModel;
use app\model\AppletUser as AppletUserModel;
use app\model\QuestionHistoryRecord as QuestionHistoryRecordModel;
use app\model\AppletUserSet as AppletUserSetModel;
use app\model\QuestionRecord as QuestionRecordModel;
use app\model\QuestionCategory as QuestionCategoryModel;

class Question extends Base
{
    protected $question;
    protected $userId;
    protected $appletUser;
    protected $questionHistoryRecord;
    protected $userSet;
    protected $questionRecord;
    protected $questionCategory;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->question = new QuestionModel();
        $this->userId = $this->userInfo['id'];
        $this->appletUser = new AppletUserModel();
        $this->questionHistoryRecord = new QuestionHistoryRecordModel();
        $this->userSet = new AppletUserSetModel();
        $this->questionRecord = new QuestionRecordModel();
        $this->questionCategory = new QuestionCategoryModel();
    }

    /**
     * 随机出题组卷
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRandomQuestions(Request $request)
    {
        // 获取用户详情
        $userSetInfo = $this->userSet->where('applet_user_id', '=', $this->userId)->find();

        $count = $userSetInfo->question_count; // 出题数
        $type = $userSetInfo->question_type; // 出题的类型1：只出新题 2：只出错题 3：新题+错题 4：无限制
        $level = $userSetInfo->level; // 题目的难度
        $category = $request->param('category', ''); // 题目的分类


        $user = $this->userId;

        if ($category === '') {
            return $this->error('请选择题目的分类');
        }


        $data = null;

        // 随机出题 1：只出新题 2：只出错题 3：新题+错题 4：无限制
        if ($type === 1) {
            $data = $this->questionHistoryRecord->getNewQuestion($user, $category, $level)->orderRaw('rand()')->limit($count)->select();
        } else if ($type === 2) {
            $data = $this->questionHistoryRecord->getErrorQuestion($user, $category, $level)->orderRaw('rand()')->limit($count)->select();
        } else if ($type === 3) {
            $newData = $this->questionHistoryRecord->getNewQuestion($user, $category, $level)->select()->toArray();
            $errorData = $this->questionHistoryRecord->getErrorQuestion($user, $category, $level)->select()->toArray();

            // 在新题和错题中随机抽取count个题目
            $totalData = array_merge($newData, $errorData);
            $length = count($totalData);

            // 打乱数组
            shuffle($totalData);
            // 获取前 count 项
            $data = array_slice($totalData, 0, min($length, $count));

        } else {
            $data = $this->questionHistoryRecord->getAllQuestion($category, $level)->orderRaw('rand()')->limit($count)->select();
        }


        // 将记录存储在出题记录表中
        $record = QuestionRecord::create([
            'applet_user_id' => $this->userId,
            'question_category_id' => $category,
            'is_submit' => 0
        ]);


        $arr = [];
        // 将记录存储到历史记录中
        foreach ($data as $question) {
            $arr[] = [
                'question_record_id' => $record->id,
                'question_id' => $question['id']
            ];
        }
        $this->questionHistoryRecord->saveAll($arr);

        // 统计数量
        $total = count($data);
        $res = [
            'total' => $total,
            'record_id' => $record->id,
            'data' => $data
        ];

        return $this->success('success', $res);
    }

    /**
     * 判断是否还能组卷，能出得题目数量不为0
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function isHasQuestion(Request $request)
    {
        $userSetInfo = $this->userSet->where('applet_user_id', '=', $this->userId)->find();

        $type = $userSetInfo->question_type; // 出题的类型1：只出新题 2：只出错题 3：新题+错题 4：无限制
        $level = $userSetInfo->level; // 题目的难度
        $category = $request->param('category', ''); // 题目的分类


        $user = $this->userId;

        if ($category === '') {
            return $this->error('请选择题目的分类');
        }


        $data = null;

        // 随机出题 1：只出新题 2：只出错题 3：新题+错题 4：无限制
        if ($type === 1) {
            $data = $this->questionHistoryRecord->getNewQuestion($user, $category, $level)->count();
        } else if ($type === 2) {
            $data = $this->questionHistoryRecord->getErrorQuestion($user, $category, $level)->count();
        } else if ($type === 3) {
            $newData = $this->questionHistoryRecord->getNewQuestion($user, $category, $level)->select()->toArray();
            $errorData = $this->questionHistoryRecord->getErrorQuestion($user, $category, $level)->select()->toArray();

            $totalData = array_merge($newData, $errorData);
            $data = count($totalData);

        } else {
            $data = $this->questionHistoryRecord->getAllQuestion($category, $level)->count();
        }


        $res = null;

        if ($data === 0) {
            $res = [
                'flag' => false,
                'msg' => '题目数量为0，请尝试修改组卷方式'
            ];
        } else {
            $res = [
                'flag' => true,
                'msg' => ''
            ];
        }

        return $this->success('success', $res);
    }


    /**
     * 判题
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function validateQuestionAnswer(Request $request)
    {
        $record = $request->param('record',''); // 记录id
        $answers = $request->param('answers'); // 用户的答案
        $time = $request->param('time',0);// 用户答题时长

        if($record == ''){
            return $this->error('获取记录出错！');
        }

        $questionRecord = $this->questionRecord->find($record);

        // 做题记录不存在
        if(empty($questionRecord)) {
            return $this->error('获取记录出错！');
        }

        // 已经提交过
        if($questionRecord->is_submit == 1) {
            return $this->error('不能重复提交试卷！');
        }

        // 记录所有的判题结果信息
        $recordsArray = [];

        // 判题
        foreach ($answers as $answer) {

            $question_id = $answer['id'];
            $question_answer = $answer['answer'];

            $question = $this->question->find($question_id);
            // 判卷
            $res = $this->question->validateQuestion($question,$question_answer);
            // dump($res);

            // 查询出指定记录的id
            $question_history_record = $this->questionHistoryRecord->where('question_record_id','=',$record)->where('question_id','=',$question_id)->find();

            // 在数组中加入记录的id和用户答案,便于批量更新
            $res['id'] = $question_history_record->id;
            $res['answer'] = $question_answer;
            $res['question_id'] = $question_id;

            $recordsArray[] = ($res);
        }

        //批量更新做题历史记录表
        $this->questionHistoryRecord->saveAll($recordsArray);

        // 更新做题记录表
        $questionRecord->is_submit = 1;
        $questionRecord->total_time = $time;
        $questionRecord->save();

        return $this->success('提交成功');
    }


    /**
     * 获取提交的判题结果
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getValidateResult(Request $request)
    {
        $record = $request->param('record','');

        if($record == ''){
            return $this->error('获取记录出错！');
        }

        $questionRecord = $this->questionRecord->find($record);

        // 做题记录不存在
        if(empty($questionRecord)) {
            return $this->error('获取记录出错！');
        }

        if($questionRecord->is_submit == 0) {
            return $this->error('未提交过，没有做题结果');
        }

        // 筛选出指定的答题历史记录
        $query= $this->questionHistoryRecord->with(['question'=> function ($q){
            $q->field(['id','title','parse','answer','options','level','type']);
        }])->hasWhere('question',['status'=>1])->where('question_record_id','=',$record);
        // 获取题目分类信息
        $recordInfo = $this->questionRecord->with('questionCategory')->find($record);
        $recordInfo = $recordInfo->hidden(['applet_user_id','is_submit']);

        $total = $query->count();
        $data = $query->select();

        $res = [
            'total' => $total,
            'record' => $recordInfo,
            'data' => $data
        ];

        return $this->success('success',$res);
    }
}