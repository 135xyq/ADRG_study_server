<?php

namespace app\applet\controller;

use app\Request;
use think\App;
use app\model\Star as StarModel;

class Star extends Base
{
    protected $star;

    protected $userId;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->star = new StarModel();
        $this->userId = $this->userInfo['id'];
    }

    public function page(Request $request)
    {
        $page = $request->param('page', 1, 'intval');
        $limit = $request->param('limit', 20, 'intval');
        $status = $request->param('status', '');


        if ($status !== '') {
            if ($status == 0) {
                // 筛选出文章的收藏

                $query = $this->star->with(['article' => function ($q) {
                    $q->field('id,title,description,status,thumbnail_url');
                }])->hasWhere('user', ['id' => $this->userId])
                    ->hasWhere('article', ['status' => 1])
                    ->where('article_id', 'not null');
            } else if ($status == 1) {
                // 筛选出视频的收藏
                $where['video_id'] = 'not null';
                $query = $this->star->with(['video' => function ($q) {
                    $q->field('id,title,description,status,thumbnail_url');
                }])->hasWhere('user', ['id' => $this->userId])
                    ->hasWhere('video', ['status' => 1])
                    ->where('video_id', 'not null');
            }

            // 统计数据
            $total = $query->count();
            $res = $query->select();

            $data = [
                'total' => $total,
                'data' => $res
            ];

            return $this->success('success', $data);
        } else {
            return $this->error('请选择收藏的类型！');
        }

    }
}