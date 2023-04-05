<?php

namespace app\applet\controller;

use app\model\StudyHistory as StudyHistoryModel;
use app\Request;
use app\validate\StudyHistoryValidate;
use think\App;
use think\exception\ValidateException;

class StudyHistory extends Base
{
    protected $studyHistory;
    protected $userId;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->studyHistory = new StudyHistoryModel();
        $this->userId = $this->userInfo['id'];
    }

    /**
     * 分页获取
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
        $type = $request->param('type', '');

        try {
            validate(StudyHistoryValidate::class)->scene('page')->check([
                'type' => $type
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        if ($type !== '') {
            if ($type == 2) {
                // 筛选出文章的学习记录
                $query = $this->studyHistory->with(['article' => function ($q) {
                    $q->field('id,title,description,status,thumbnail_url');
                }])->hasWhere('user', ['id' => $this->userId])
                    ->hasWhere('article', ['status' => 1])
                    ->where('article_id', 'not null');
            } else if ($type == 1) {
                // 筛选出视频的学习记录
                $where['video_id'] = 'not null';
                $query = $this->studyHistory->with(['video' => function ($q) {
                    $q->field('id,title,description,status,thumbnail_url');
                }])->hasWhere('user', ['id' => $this->userId])
                    ->hasWhere('video', ['status' => 1])
                    ->where('video_id', 'not null');
            }

            // 统计数据
            $total = $query->count(); // 统计数量
            $res = $query->page($page, $limit)->select();

            $data = [
                'total' => $total,
                'data' => $res
            ];

            return $this->success('success', $data);
        } else {
            return $this->error('请选择学习记录的类型！');
        }
    }

    public function newStudyHistory(Request $request)
    {
        $type = $request->param('type', 0, 'intval');
        $id = $request->param('id', '');
        $time = $request->param('time',0,'intval');

        try {
            validate(StudyHistoryValidate::class)->scene('add')->check([
                'type' => $type
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        if ($id === '') {
            return $this->error('出错了！');
        }

        $data = null; // 存放是哪一种资源

        if ($type == 1) {
            $data = 'video_id';
        } else if ($type == 2) {
            $data = 'article_id';
        } else if ($type == 3) {
            $data = 'blog_id';
        }

        $find = $this->studyHistory->hasWhere('user', ['id' => $this->userId])->where($data, '=', $id)->find();

        //  记录不存在则创建
        if (empty($find)) {
            $newData = [
                'applet_user_id' => $this->userId,
                'type' => $type,
                $data => $id,
                'total_time' => $time,
                'total_count' => 1
            ];

            StudyHistoryModel::create($newData);
            return $this->success();
        }else{
            //  记录存在则更新
            $find->total_count += 1;
            $find->total_time += $time;
            $find->save();
            return $this->success();
        }
    }
}