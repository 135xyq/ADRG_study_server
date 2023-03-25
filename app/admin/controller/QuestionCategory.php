<?php

namespace app\admin\controller;

use app\Request;
use app\validate\QuestionCategoryValidate;
use think\App;
use app\model\QuestionCategory as QuestionCategoryModel;
use think\exception\ValidateException;

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

    /**
     * 新增一个分类
     * @param Request $request
     * @return \think\response\Json
     */
    public function add(Request $request)
    {

        $data = [];
        $data['title'] = $request->param('title', '');
        $data['status'] = $request->param('status', 1);
        $data['description'] = $request->param('description', '');
        $data['sort'] = $request->param('sort', 1,'intval');

        try {
            validate(QuestionCategoryValidate::class)->scene('add')->check([
                'title' => $data['title'],
                'status' => $data['status']
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        // 验证是否存在同名的分类
        if (!empty($data['title'])) {
            $count = $this->questionCategory->where('title','=',$data['title'])->count();
            if($count) {
                return $this->error('分类名已存在');
            }
        }


        $res = $this->questionCategory->create($data);
        if ($res !== false) {

            // 记录日志
            $this->writeDoLog($data);

            // 响应信息
            return $this->success('新增成功！',$res);
        } else {
            return $this->error('新增失败！');
        }
    }
}