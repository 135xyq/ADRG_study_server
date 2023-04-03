<?php

namespace app\applet\controller;

use app\Request;
use think\App;
use app\model\Like as LikeModel;

class Like extends Base
{
    protected $like;

    protected $userId;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->like = new LikeModel();
        $this->userId = $this->userInfo['id'];
    }

    /**
     * 点赞分页获取
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
        $status = $request->param('status', '');


        if ($status !== '') {
            if ($status == 0) {
                // 筛选出文章的点赞

                $query = $this->like->with(['article' => function ($q) {
                    $q->field('id,title,description,status,thumbnail_url');
                }])->hasWhere('user', ['id' => $this->userId])
                    ->hasWhere('article', ['status' => 1])
                    ->where('article_id', 'not null');
            } else if ($status == 1) {
                // 筛选出视频的点赞
                $where['video_id'] = 'not null';
                $query = $this->like->with(['video' => function ($q) {
                    $q->field('id,title,description,status,thumbnail_url');
                }])->hasWhere('user', ['id' => $this->userId])
                    ->hasWhere('video', ['status' => 1])
                    ->where('video_id', 'not null');
            }

            // 统计数据
            $total = $query->count();
            $res = $query->page($page,$limit)->select();

            $data = [
                'total' => $total,
                'data' => $res
            ];

            return $this->success('success', $data);
        } else {
            return $this->error('请选择点赞的类型！');
        }

    }


    /**
     * 用户点赞
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DbException
     */
    public function like(Request $request) {
        $videoId = $request->param('videoId');
        $articleId = $request->param('articleId');

        // 设置用户信息
        $applet_user_id = $this->userId;

        if (empty($articleId) && empty($videoId)) {
            return $this->error('请选择点赞的对象！');
        }

        // 判断文章是否已经点赞过
        if(!empty($articleId)) {
            $count = $this->like->where('applet_user_id','=',$applet_user_id)->where('article_id','=',$articleId)->count();
            if($count) {
                return $this->success('文章已点赞！');
            }
        }

        // 判断视频是否已经点赞过
        if(!empty($videoId)) {
            $count = $this->like->where('applet_user_id','=',$applet_user_id)->where('video_id','=',$videoId)->count();
            if($count) {
                return $this->success('视频已点赞！');
            }
        }

        LikeModel::create([
            'applet_user_id' => $applet_user_id,
            'video_id' => $videoId,
            'article_id' => $articleId
        ]);

        return $this->success('点赞成功！');
    }

    /**
     * 取消点赞
     * @param Request $request
     * @return \think\response\Json
     */
    public function cancelLike(Request $request) {
        $id = $request->param('id');

        if(empty($id)) {
            return $this->error('请选择取消点赞的对象');
        }

        LikeModel::destroy($id);

        return $this->success('取消点赞成功');
    }

    /**
     * 判断是否点赞
     * @param Request $request
     * @return \think\response\Json
     */
    public function isLike(Request $request) {
        $videoId = $request->param('videoId');
        $articleId = $request->param('articleId');

        // 设置用户信息
        $applet_user_id = $this->userId;

        // 不能同时为空
        if (empty($articleId) && empty($videoId)) {
            return $this->error('出错了');
        }

        // 判断文章是否已经收藏过
        if(!empty($articleId)) {
            $count = $this->like->where('applet_user_id','=',$applet_user_id)->where('article_id','=',$articleId)->count();
            if($count) {
                return $this->success('文章已点赞！',['result'=>true]);
            }
        }

        // 判断视频是否已经点赞过
        if(!empty($videoId)) {
            $count = $this->like->where('applet_user_id','=',$applet_user_id)->where('video_id','=',$videoId)->count();
            if($count) {
                return $this->success('视频已点赞！',['result'=>true]);
            }
        }


        return $this->success('未点赞',['result'=>false]);
    }
}