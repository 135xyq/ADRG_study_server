<?php

namespace app\admin\controller;

use app\Request;
use app\validate\ArticleValidate;
use think\App;
use think\exception\ValidateException;
use app\model\Article as ArticleModel;


class Article extends Base
{
    protected $article;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->article = new ArticleModel();
    }

    /**
     * 分页获取和查询数据
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function page(Request $request)
    {

        // 接受分页和查询数据
        $page = $request->param('page', 1, 'intval');
        $limit = $request->param('limit', 20, 'intval');
        $title = $request->param('title', '');
        $status = $request->param('status', '');
        $show_cover = $request->param('show_cover', '');
        $category = $request->param('category','');
        $order = $request->param('order',''); // 排序方式

        $where =[];

        // 判断状态和显示是否合法 video.status 查找防止和studyCategory的status冲突
        if($status !== '' && in_array($status,[0,1])){
            $where['article.status'] = $status;
        }
        if($show_cover !== '' && in_array($show_cover,[0,1])){
            $where['show_cover'] = $show_cover;
        }


        // 预载入
        $query = $this->article->with(['studyCategory' => function ($query) {
            $query->field('id,name,status');
        }]);

        // 文章标题查询
        if(!empty($title)) {
            $query->where('title','like','%'.$title.'%');
        }

        // 分类查询
        if(!empty($category)) {
            $query->hasWhere('studyCategory',['id' => $category]);
        }

        $total = $query->where($where)->count();
        // 排序
        $query->order($order,'desc');
        $data = $query->page($page,$limit)->select();

        $res = [
            'total' => $total,
            'data' => $data
        ];
        return $this->success('success', $res);
    }


    /**
     * 删除文章，支持删除多个
     * @param Request $request
     * @return \think\response\Json
     */
    public function delete(Request $request) {
        $id = $request->param('id');

        if(empty($id)){
            return $this->error('参数错误！');
        }

        $ids = ['id'=>explode(',',$id)];
        $bool = $this->article->destroy($ids);
        if($bool){

            // 写入操作日志
            $this->writeDoLog($request->param());

            return $this->success('删除成功！');
        }else{
            return $this->error('删除失败');
        }
    }

    /**
     * 新增一篇文章
     * @param Request $request
     * @return \think\response\Json
     */
    public function add(Request $request)
    {
        $data = $request->param();

        try {
            validate(ArticleValidate::class)->scene('add')->check($data);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        // 验证是否存在同名的文章
        // if (!empty($data['title'])) {
        //     $count = $this->article->where('title','=',$data['title'])->count();
        //     if($count) {
        //         return $this->error('文章名已存在');
        //     }
        // }


        $res = $this->article->create($data);
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
     * 更新一篇文章
     * @param Request $request
     * @return \think\response\Json
     */
    public function update(Request $request) {
        $data = $request->param();

        try {
            validate(ArticleValidate::class)->scene('update')->check($data);
        }catch (ValidateException $e){
            return $this->error($e->getError());
        }

        ArticleModel::update($data);
        // 记录日志
        $this->writeDoLog($data);

        return $this->success('修改成功！');
    }

}
