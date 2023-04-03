<?php

namespace app\applet\controller;

use app\Request;
use app\validate\CommentValidate;
use think\App;
use app\model\Comment as CommentModel;
use think\exception\ValidateException;

class Comment extends Base
{

    protected $comment;
    protected $userId;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->userId = $this->userInfo['id'];
        $this->comment = new CommentModel();
    }

    /**
     * 分页获取评论
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
        $videoId = $request->param('videoId', ''); // 视频的id如果要获取视频的评论
        $articleId = $request->param('articleId', ''); // 文章的id，如果要获取文章的评论

        $query = $this->comment->with(['user' => function ($q) {
            $q->field(['id', 'avatar', 'nick_name']);
        }]);

        if ($articleId !== '') {
            $query->hasWhere('article', ['id' => $articleId]);
        }

        // 没有选择文章或视频
        if ($videoId !== '') {
            $query->hasWhere('video', ['id' => $videoId]);
        }

        if (empty($query)) {
            return $this->error('请选择文章或视频！');
        }

        // 筛选出审核通过的评论
        $query->where('comment.status', '=', 1);

        $total = $query->count();
        $res = $query->page($page, $limit)->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success', $data);
    }

    /**
     * 用户评论
     * @param Request $request
     * @return \think\response\Json
     */
    public function publishComment(Request $request)
    {
        $content = $request->param('content');
        $videoId = $request->param('videoId');
        $articleId = $request->param('articleId');

        // 设置用户信息和默认值
        $applet_user_id = $this->userId;
        $status = 0;

        if (empty($articleId) && empty($videoId)) {
            return $this->error('请选择评论的对象！');
        }

        try {
            validate(CommentValidate::class)->scene('add')->check([
                'content' => $content,
                'applet_user_id' => $applet_user_id,
                'status' => $status
            ]);
        } catch (ValidateException $e) {
            return $this->error('评论内容不能为空！');
        }

        $res = CommentModel::create([
            'content' => $content,
            'applet_user_id' => $applet_user_id,
            'video_id' => $videoId,
            'article_id' => $articleId,
            'status' => $status
        ]);

        return $this->success('评论成功');
    }

    /**
     * 用户删除评论
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function deleteComment(Request $request)
    {
        $id = $request->param('id');

        if (empty($id)) {
            return $this->error('请选择要删除的评论');
        }

        $comment = $this->comment->find($id);

        // 评论是否存在
        if (empty($comment)) {
            return $this->error('要删除的评论不存在!');
        }

        // 权限验证
        if ($comment['applet_user_id'] != $this->userId) {
            return $this->error('无权删除别人的评论');
        }

        $bool = $comment->delete();

        if ($bool) {
            return $this->success('删除成功！');
        } else {
            return $this->error('删除失败！');
        }
    }
}