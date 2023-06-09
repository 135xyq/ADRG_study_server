<?php

namespace app\applet\controller;
use app\model\QuestionRecord as QuestionRecordModel;
use app\Request;
use think\App;

class QuestionRecord extends Base
{
    protected $userId;
    protected $questionRecord;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->userId = $this->userInfo['id'];
        $this->questionRecord = new QuestionRecordModel();
    }

    /**
     * 分页获取刷题记录
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRecordPage(Request $request) {
        // 要筛选试卷的类型，-1：不筛选,0：未完成，1：已完后
        $type = $request->param('type',-1);
        $page = $request->param('page',1,'intval');
        $limit = $request->param('limit',10,'intval');

        // 统计有多少题目
        $query =  $this->questionRecord->with('questionCategory')->withCount('questionHistoryRecord');

        // 筛选数据类型
        if($type != -1) {
            $query->where('is_submit','=',$type);
        }

       $query->where('applet_user_id','=',$this->userId)->order('create_time','desc');

        $total = $query->count();
        $res = $query->page($page,$limit)->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success',$data);
    }
}