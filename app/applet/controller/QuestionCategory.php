<?php

namespace app\applet\controller;

use app\Request;
use think\App;
use app\model\QuestionCategory as QuestionCategoryModel;
use app\model\QuestionHistoryRecord as QuestionHistoryRecordModel;

class QuestionCategory extends Base
{
    protected $questionCategory;
    protected $questionHistoryRecord;
    protected $userId;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->questionCategory = new QuestionCategoryModel();
        $this->questionHistoryRecord = new QuestionHistoryRecordModel();
        $this->userId = $this->userInfo['id'];
    }

    /**
     * 获取分类列表和每个分类用户的刷题数据
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getQuestionCategoryList(Request $request)
    {
        $filed = ['id','title','description','question_count'];
        // 筛选出状态正常的题目分类
        $query =  $this->questionCategory
            ->where('status','=',1);


        $total = $query->count();
        $res = $query->order('sort','desc')->field($filed)->select();

        // 统计每个分类下用户的做题个数
        foreach ($res as $t) {
            $count = $this->questionHistoryRecord->getUserDoneQuestionCount($this->userId,$t['id']);
            $t['reslove_count'] = $count;
        }

        $data = [
            'total' => $total,
            'data'=> $res
        ];

        return $this->success('success',$data);
    }
}