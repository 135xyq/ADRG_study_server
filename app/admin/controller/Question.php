<?php

namespace app\admin\controller;

use app\Request;
use think\App;
use app\model\Question as QuestionModel;

class Question extends Base
{
    protected $question;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->question = new QuestionModel();
    }

    public function page(Request $request) {
        $page = $request->param('page',1,'intval');
        $limit = $request->param('limit',20,'intval');
        $status = $request->param('status',''); // 题目状态查询
        $title = $request->param('title',''); // 题目关键词查询
        $sort = $request->param('sort',''); // 排序方式
        $level = $request->param('level',''); // 问题的等级
        $type = $request->param('type',''); // 问题的种类
        $category = $request->param('category',''); // 分类查询
        $parse = $request->param('parse',''); // 有无解析

        $query = $this->question->with(['questionCategory' => function($q) {
            $q->field('id,title');
        }]);

        // 关键词查询
        if($title!==''){
            $query->where('title','like','%'.$title.'%');
        }

        // 状态查询
        if($status !== '') {
            $query->where('status','=',$status);
        }

        // 等级查询
        if($level!== '') {
            $query->where('level','=',$level);
        }

        // 种类查询
        if($type !== '') {
            $query->where('type','=',$type);
        }

        // 分类查询
        if($category !== '') {
            $query->where('question_category_id','=',$category);
        }

        // 有无解析查询
        if($parse == 1) {
            $query->where('parse','not null');
        }else if($parse == 0){
            $query->where('parse','null');
        }

        // 排序
        if($sort !== '') {
            $query->order($sort,'desc');
        }

        $total = $query->count();
        $res = $query->page($page,$limit)->select();
        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success',$data);
    }

}