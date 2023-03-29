<?php

namespace app\applet\controller;

use app\common\BaseServer;
use app\Request;
use app\model\Article as ArticleModel;
use think\App;

class Article extends Base
{
    private $article;
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->article = new ArticleModel();
    }

    /**
     * 根据分类获取文章列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list(Request $request) {
        // 获取分类id
        $id = $request->param('category_id');

        if(empty($id)) {
            return $this->error('请选择分类!');
        }

        $query = $this->article->where([['status','=',1],['study_category_id','=',$id]]);

        $total = $query->count();
        $res = $query->select();
        $data = ['total'=>$total,'data'=>$res];
        return $this->success('success',$data);
    }

    /**
     * 根据分类获取文章分页列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function page(Request $request) {
        // 获取分类id
        $id = $request->param('category_id');
        $page = $request->param('page',1,'intval');
        $limit = $request->param('limit',10,'intval');

        if(empty($id)) {
            return $this->error('请选择分类!');
        }

        $query = $this->article->where([['status','=',1],['study_category_id','=',$id]]);

        $total = $query->count();
        $res = $query->page($page,$limit)->select();
        $data = ['total'=>$total,'data'=>$res];
        return $this->success('success',$data);
    }
}