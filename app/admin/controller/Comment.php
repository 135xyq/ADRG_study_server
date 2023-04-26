<?php

namespace app\admin\controller;

use app\Request;
use app\validate\CommentValidate;
use think\App;
use app\model\Comment as CommentModel;
use think\exception\ValidateException;
use app\model\AppletUser;

class Comment extends Base
{
    protected $comment;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->comment = new CommentModel();
    }

    /**
     * 分页查询和获取数据
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function page(Request $request)
    {
        $page = $request->param('page', 1, 'intval');
        $limit = $request->param('limit', 20, 'intval');
        $status = $request->param('status', ''); // 评论的状态
        $keyword = $request->param('keyword', '');// 内容关键词
        $article = $request->param('article', ''); // 文章id
        $video = $request->param('video', ''); // 视频id
        $userName = $request->param('userName',''); // 用户名

        // 查询条件
        $where = [];
        if (!empty($article)) {
            $where['article_id'] = $article;
        }

        if (!empty($video)) {
            $where['video_id'] = $video;
        }

        // 用户名检索
        $where1 = AppletUser::where('nick_name','like','%'.$userName.'%');

        // 筛选基本条件
        $query = $this->comment->with(['video' => function ($query) {
            $query->field('id,title');
        }, 'article' => function ($query) {
            $query->field('id,title');
        },'user' => function($query) {
            $query->field('id,nick_name');
        }])->hasWhere('user',$where1)->where($where);


        // 状态筛选
        if ($status !== '') {
            $query->where('status', '=', $status);
        }

        // 关键词筛选
        if (!empty($keyword)) {
            $query->where('content', 'like', '%' . $keyword . '%');
        }

        $total = $query->count(); // 统计数量
        $res = $query->order('create_time','desc')->page($page, $limit)->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success', $data);
    }


    /**
     * 评论审核
     * @param Request $request
     * @return \think\response\Json
     */
    public function audit(Request $request)
    {
        // 评论审核需要的数据
        $id = $request->param('id');
        $status = $request->param('status');
        $err_msg = $request->param('err_msg'); // 审核不通过的错误信息

        // 数据验证
        try {
            validate(CommentValidate::class)->scene('audit')->check([
                'id' => $id,
                'status' => $status
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $comment = $this->comment->find($id);
        if (empty($comment)) {
            return $this->error('评论不存在');
        }

        // 无法从审核成功到未审核
        if ($status == 0 && $comment['status'] !== 0) {
            return $this->error('状态修改不合理');
        }

        $comment->status = $status;
        $comment->err_msg = $err_msg;
        $comment->save();

        return $this->success('审核成功！');

    }

    /**
     * 删除评论，支持批量删除
     * @param Request $request
     * @return \think\response\Json
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');

        try {
            validate(CommentValidate::class)->scene('delete')->check(
                ['id' => $id]
            );
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $ids = ['id' => explode(',', $id)];
        $bool = CommentModel::destroy($ids);
        if ($bool) {

            // 写入操作日志
            $this->writeDoLog($request->param());


            return $this->success('删除成功！');
        } else {
            return $this->error('删除失败');
        }
    }
}
