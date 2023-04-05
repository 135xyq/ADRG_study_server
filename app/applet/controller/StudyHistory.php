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
        }catch (ValidateException $e) {
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
            $total = $query->count();
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
}