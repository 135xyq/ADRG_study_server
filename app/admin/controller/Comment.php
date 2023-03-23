<?php

namespace app\admin\controller;

use app\Request;
use app\validate\CommentValidate;
use think\App;
use app\model\Comment as CommentModel;
use think\exception\ValidateException;

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

        // 查询条件
        $where = [];
        if (!empty($article)) {
            $where['article_id'] = $article;
        }

        if (!empty($video)) {
            $where['video_id'] = $video;
        }

        // 筛选基本条件
        $query = $this->comment->where($where)->where('status', '=', $status);

        // 关键词筛选
        if (!empty($keyword)) {
            $query->where('content', 'like', '%' . $keyword . '%');
        }

        $total = $query->count(); // 统计数量
        $res = $query->page($page, $limit)->select();

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
            return $this->error('fail', $e->getError());
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
}