<?php

namespace app\admin\controller;

use app\model\StudyHistory as StudyHistoryModel;
use app\Request;
use app\validate\StudyHistoryValidate;
use think\App;
use app\model\AppletUser;
use think\exception\ValidateException;

class StudyHistory extends Base
{

    protected $studyHistory;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->studyHistory = new StudyHistoryModel();
    }

    /**
     * 分页查询用户的观看列表
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
        $article = $request->param('article', ''); // 文章id
        $video = $request->param('video', ''); // 视频id
        $userName = $request->param('userName', ''); // 用户名
        $type = $request->param('type', ''); // 种类
        $sort = $request->param('sort',''); // 排序方式

        // 查询条件
        $where = [];
        if (!empty($article)) {
            $where['article_id'] = $article;
        }

        if (!empty($video)) {
            $where['video_id'] = $video;
        }

        if (!empty($type)) {
            $where['type'] = $type;
        }

        // 用户名检索
        $where1 = AppletUser::where('nick_name', 'like', '%' . $userName . '%');

        // 筛选基本条件
        $query = $this->studyHistory->with(['video' => function ($query) {
            $query->field('id,title');
        }, 'article' => function ($query) {
            $query->field('id,title');
        }, 'user' => function ($query) {
            $query->field('id,nick_name');
        }])->hasWhere('user', $where1)->where($where);


        $total = $query->count();
        if($sort !== '') {
            // 排序方式
            $res = $query->order($sort,'desc')->page($page, $limit)->select();
        }else{
            $res = $query->order('create_time','desc')->page($page, $limit)->select();
        }

        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success', $data);
    }

    /**
     * 删除学习记录，支持批量删除
     * @param Request $request
     * @return \think\response\Json
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');

        try {
            validate(StudyHistoryValidate::class)->scene('delete')->check(
                ['id' => $id]
            );
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $ids = ['id' => explode(',', $id)];
        $bool = StudyHistoryModel::destroy($ids);
        if ($bool) {

            // 写入操作日志
            $this->writeDoLog($request->param());


            return $this->success('删除成功！');
        } else {
            return $this->error('删除失败');
        }
    }
}