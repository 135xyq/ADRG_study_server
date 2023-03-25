<?php

namespace app\admin\controller;

use app\Request;
use think\App;
use app\model\QuestionCategory as QuestionCategoryModel;

class QuestionCategory extends Base
{
    protected $questionCategory;
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->questionCategory = new QuestionCategoryModel();
    }


    /**
     * 分页查询获取题目的分类
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function page(Request $request) {
        $page = $request->param('page',1,'intval');
        $limit = $request->param('limit',20,'intval');
        $status = $request->param('status',''); // 分类状态查询
        $title = $request->param('title',''); // 分类名称查询

        // 分类名查询
        $query = $this->questionCategory->where('title', 'like', '%' . $title . '%');

        // 根据状态查询
        if($status !== '') {
            $query->where('status',$status);
        }

        $total = $query->count();
        $res = $query->page($page,$limit)->select();

        $data = [
            'total'=> $total,
            'data' => $res
        ];

        return $this->success('success',$data);

    }
}