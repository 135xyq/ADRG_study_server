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
        // 排序、分页输出
        $res = $query->order('sort','desc')->page($page,$limit)->select();

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

    /**
     * 删除一个分类
     * @param Request $request
     * @return \think\response\Json
     */
    public function delete(Request $request)
    {
        $id = $request->param('id', '');

        try {
            validate(QuestionCategoryValidate::class)->scene('delete')->check([
                'id' => $id
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $res = $this->questionCategory->destroy($id);


        if ($res === true) {
            // 写入日志数据库
            $this->writeDoLog($request->param());

            return $this->success('删除成功！');
        } else {
            return $this->error('删除失败！');
        }
    }

    /**
     * 修改分类
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function update(Request $request)
    {

        try {
            validate(QuestionCategoryValidate::class)->scene('update')->check($request->param());
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $id = $request->param('id');

        $data = [];

        // 判断分类名是否冲突
        if (!empty($request->param('title'))) {
            $data['title'] = $request->param('title');
            $count = $this->questionCategory::where([['title','=',$data['title']],['id','<>',$id]])->count();
            if($count) {
                return $this->error('分类名已存在');
            }
        }

        //判断是否修改描述信息
        if(!empty($request->param('description'))) {
            $data['description'] = $request->param('description');
        }

        //判断是否修改排序信息
        if($request->param('sort','') !== '') {
            $data['sort'] = $request->param('sort');
        }

        // 判断是否修改状态
        if($request->param('status','') !== '') {
            $data['status'] = $request->param('status',1,'intval');
        }

        if(!empty($data)) {
            $data['id'] = $id;
            // 写入日志数据库
            $this->writeDoLog($request->param());

            $this->questionCategory->update($data);
        }

        return $this->success('修改成功！');

    }

}