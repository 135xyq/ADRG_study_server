<?php

namespace app\admin\controller;

use app\Request;
use think\App;
use app\model\Comment as CommentModel;

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
        $res = $query->page($page,$limit)->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success',$data);
    }
}