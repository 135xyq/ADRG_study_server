<?php
// 学习视频、文章的分类控制器
namespace app\admin\controller;

use app\common\BaseServer;
use app\Request;
use app\validate\StudyCategoryValidate;
use app\model\StudyCategory as StudyCategoryModel;
use think\exception\ValidateException;
use think\facade\Session;

class StudyCategory extends BaseServer
{
    private $studyCategory;
    public function __construct()
    {
        $this->studyCategory = new StudyCategoryModel();
    }

    /**
     * 分页获取数据
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function page(Request $request)
    {
        // 获取分页和查询的请求参数
        $page = $request->param('page', 1, 'intval');
        $limit = $request->param('limit', 20, 'intval');
        $name = $request->param('name', ''); // 根据分类名查询数据

        $field = 'id,name,description,create_time,update_time';

        $query = StudyCategoryModel::where('name', 'like', '%' . $name . '%');


        // 统计数量
        $count = $query->count();

        $res = $query->field($field)->page($page, $limit)->select();

        $this->result = [
            'total' => $count,
            'data' => $res
        ];
        return $this->success();
    }

    /**
     * 新增一个分类
     * @param Request $request
     * @return \think\response\Json
     */
    public function add(Request $request)
    {

        $data = [];
        $data['name'] = $request->param('name', '');
        $data['description'] = $request->param('description', '');

        try {
            validate(StudyCategoryValidate::class)->scene('add')->check([
                'name' => $data['name'],
                'description' => $data['description']
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $res = StudyCategoryModel::create($data);
        if ($res !== false) {
            // 记录日志
            $this->writeDoLog($data);

            // 响应信息
            $this->result = $res;
            return $this->success('新增成功！');
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
            validate(StudyCategoryValidate::class)->scene('delete')->check([
                'id' => $id
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $res = StudyCategoryModel::destroy($id);
        if ($res === true) {
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
            validate(StudyCategoryValidate::class)->scene('update')->check($request->param());
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $id = $request->param('id');

        $data = [];

        // 判断分类名是否冲突
        if (!empty($request->param('name'))) {
            $data['name'] = $request->param('name');
            $count = $this->studyCategory::where('name','=',$data['name'])->count();
            if($count) {
                return $this->error('分类名已存在');
            }
        }
        if(!empty($request->param('description'))) {
            $data['description'] = $request->param('description');
        }
        if(!empty($data)) {
            $data['id'] = $id;
            // 写入日志数据库
            $this->writeDoLog($request->param());

            $this->studyCategory->update($data);
        }

        return $this->success('修改成功！');

    }

}