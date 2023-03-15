<?php
// 学习视频、文章的分类控制器
namespace app\admin\controller;

use app\common\BaseServer;
use app\Request;
use app\validate\StudyCategoryValidate;
use app\model\StudyCategory as StudyCategoryModel;
use think\exception\ValidateException;

class StudyCategory extends BaseServer
{

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
        $data['name'] = $request->param('name','');
        $data['description'] = $request->param('description','');

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
            $this->result = $res;
            return $this->success('新增成功！');
        } else {
            return $this->error('新增失败！');
        }
    }
}