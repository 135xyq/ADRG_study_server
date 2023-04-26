<?php

namespace app\applet\controller;

use app\common\BaseServer;
use app\Request;
use think\App;
use app\model\StudyCategory as StudyCategoryModel;

class StudyCategory extends Base
{
    private $category;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->category = new StudyCategoryModel();
    }

    /**
     * 获取分类列表和展示在封面的资源
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list(Request $request)
    {
        // 获取分类列表和展示的文章、视频
        $query = $this->category->field('id,name')->with(['video' => function ($query) {
            $query->where([['show_cover', '=', 1], ['status', '=', 1]]);
        }, 'article' => function ($query) {
            $query->where([['show_cover', '=', 1], ['status', '=', 1]]);
        }])->where('status', 1);

        // 查询的数据总数
        $total = $query->count();
        $res = $query->order('sort', 'desc')->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];
        return $this->success('success', $data);
    }

    /**
     * 获取学习资源分类列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getStudyCategoryList(Request $request) {
        $query = $this->category->field('id,name')->where('status', 1);

        // 查询的数据总数
        $total = $query->count();
        $res = $query->order('sort', 'desc')->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];
        return $this->success('success', $data);

    }

    /**
     * 根据资源分类获取封面资源
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sources(Request $request) {
        $category = $request->param('category','');

        if($category === '') {
            return $this->error('请选择分类');
        }

        $data = $this->category->field('id,name')->with(['video' => function ($query) {
            $query->where([['show_cover', '=', 1], ['status', '=', 1]]);
        }, 'article' => function ($query) {
            $query->withoutField(['content'])->where([['show_cover', '=', 1], ['status', '=', 1]]);
        }])->find($category);

        return $this->success('success',$data);
    }
}