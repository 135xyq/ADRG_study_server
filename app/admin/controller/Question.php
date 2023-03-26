<?php

namespace app\admin\controller;

use app\Request;
use app\validate\QuestionValidate;
use think\App;
use app\model\Question as QuestionModel;
use think\exception\ValidateException;

class Question extends Base
{
    protected $question;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->question = new QuestionModel();
    }

    /**
     * 分页查询获取数据
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
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
        if($parse === 1) {
            $query->where('parse','not null');
        }else if($parse === 0){
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


    /**
     * 新增一个分类
     * @param Request $request
     * @return \think\response\Json
     */
    public function add(Request $request)
    {

        $data = [];
        $data['title'] = $request->param('title', '');
        $data['status'] = $request->param('status', '');
        $data['type'] = $request->param('type', '');
        $data['options'] = $request->param('options' );
        $data['answer'] = $request->param('answer');
        $data['parse'] = $request->param('parse');
        $data['level'] = $request->param('level');
        $data['question_category_id'] = $request->param('question_category_id');

        // 数据验证
        try {
            validate(QuestionValidate::class)->scene('add')->check([
                'title' => $data['title'],
                'type'=> $data['type'],
                'level'=>$data['level'],
                'answer'=>$data['answer'],
                'status'=>$data['status'],
                'question_category_id'=>$data['question_category_id']
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }


        // 验证是否存在同名的题目
        if (!empty($data['title'])) {
            $count = $this->question->where('title','=',$data['title'])->count();
            if($count) {
                return $this->error('题目名已存在');
            }
        }


        // dump($data);
        $res = $this->question->create($data);
        if ($res !== false) {

            // 响应信息
            return $this->success('新增成功！',$res);
        } else {
            return $this->error('新增失败！');
        }
    }

    /**
     * 批量删除问题
     * @param Request $request
     * @return \think\response\Json
     */
    public function delete(Request $request) {
        $id = $request->param('id');
        if(!$id){
            return $this->error('请选择删除的问题！');
        }

        $ids = ['id'=>explode(',',$id)];
        $bool = QuestionModel::destroy($ids);
        if($bool){

            // 写入操作日志
            $this->writeDoLog($request->param());

            return $this->success('删除成功！');
        }else{
            return $this->error('删除失败');
        }
    }

    /**
     * 更新问题
     * @param Request $request
     * @return \think\response\Json
     */
    public function update(Request $request) {
        $data = $request->param();

        try {
            validate(QuestionValidate::class)->scene('update')->check($data);
        }catch (ValidateException $e){
            return $this->error($e->getError());
        }

        // 判断分类名是否冲突
        if (!empty($data['title'])) {
            $count = $this->question->where([['title','=',$data['title']],['id','<>',$data['id']]])->count();
            if($count) {
                return $this->error('题目已存在');
            }
        }

        QuestionModel::update($data);
        // 记录日志
        $this->writeDoLog($data);

        return $this->success('修改成功！');
    }
}
