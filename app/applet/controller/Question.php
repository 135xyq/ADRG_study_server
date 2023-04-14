<?php

namespace app\applet\controller;

use app\model\QuestionRecord;
use app\Request;
use think\App;
use app\model\Question as QuestionModel;
use app\model\AppletUser as AppletUserModel;
use app\model\QuestionHistoryRecord as QuestionHistoryRecordModel;
use app\model\AppletUserSet as AppletUserSetModel;

class Question extends Base
{
    protected $question;
    protected $userId;
    protected $appletUser;
    protected $questionHistoryRecord;
    protected $userSet;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->question = new QuestionModel();
        $this->userId = $this->userInfo['id'];
        $this->appletUser = new AppletUserModel();
        $this->questionHistoryRecord = new QuestionHistoryRecordModel();
        $this->userSet = new AppletUserSetModel();
    }

    /**
     * 随机出题组卷
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRandomQuestions(Request $request) {
        // 获取用户详情
        $userSetInfo = $this->userSet->where('applet_user_id','=',$this->userId)->find();

        $count = $userSetInfo->question_count; // 出题数
        $type = $userSetInfo->question_type; // 出题的类型1：只出新题 2：只出错题 3：新题+错题 4：无限制
        $level = $userSetInfo->level; // 题目的难度
        $category = $request->param('category',''); // 题目的分类


        $user = $this->userId;

        if($category === '') {
            return $this->error('请选择题目的分类');
        }


        $data = null;

        // 随机出题 1：只出新题 2：只出错题 3：新题+错题 4：无限制
        if($type === 1) {
            $data = $this->questionHistoryRecord->getNewQuestion($user,$category,$level)->orderRaw('rand()')->limit($count)->select();
        }else if($type === 2){
            $data = $this->questionHistoryRecord->getErrorQuestion($user,$category,$level)->orderRaw('rand()')->limit($count)->select();
        }else if($type === 3) {
            $newData  = $this->questionHistoryRecord->getNewQuestion($user,$category,$level)->select()->toArray();
            $errorData = $this->questionHistoryRecord->getErrorQuestion($user,$category,$level)->select()->toArray();

            // 在新题和错题中随机抽取count个题目
            $totalData = array_merge($newData,$errorData);
            $length = count($totalData);

            // 打乱数组
            shuffle($totalData);
            // 获取前 count 项
            $data = array_slice($totalData, 0, min($length,$count));

        }else{
            $data = $this->questionHistoryRecord->getAllQuestion($category,$level)->orderRaw('rand()')->limit($count)->select();
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

        return $this->success('success',$res);
    }
}