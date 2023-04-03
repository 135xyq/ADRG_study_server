<?php

namespace app\applet\controller;

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

    public function page(Request $request) {
        $page = $request->param('page', 1, 'intval');
        $limit = $request->param('limit', 20, 'intval');
        $videoId = $request->param('videoId',''); // 视频的id如果要获取视频的评论
        $articleId = $request->param('articleId',''); // 文章的id，如果要获取文章的评论

        $query = null;

        if($articleId !== '') {
            $query = $this->comment->hasWhere('article',['id'=>$articleId]);
        }

        // 没有选择文章或视频
        if($videoId !== '') {
            $query = $this->comment->hasWhere('video',['id'=>$videoId]);
        }

        if(empty($query)) {
            return $this->error('请选择文章或视频！');
        }

        // 筛选出审核通过且不是子评论的评论
        $query->where('comment.status','=',1);

        $total = $query->count();
        $res = $query->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success',$data);
    }
}