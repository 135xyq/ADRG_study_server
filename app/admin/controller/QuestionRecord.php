<?php

namespace app\admin\controller;

use app\Request;
use think\App;
use app\model\QuestionRecord as QuestionRecordModel;

class QuestionRecord extends Base
{
    protected $questionRecord;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->questionRecord = new QuestionRecordModel();
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


        $query = $this->questionRecord->with(['user' => function($q){
            $q->field(['id','nick_name']);
        },'questionCategory' => function($q){
            $q->field(['id','title']);
        },'questionHistoryRecord' => function($q){
            $q->field(['id','question_record_id']);
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
            $query->whereBetweenTime('create_time', $time[0],$time[1]);
        }


        $total = $query->count();
        $res = $query->page($page, $limit)->select();


        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success', $data);
    }
}