<?php

namespace app\applet\controller;

use app\Request;
use app\validate\FeedbackValidate;
use think\App;
use app\model\Feedback as FeedbackModel;
use think\exception\ValidateException;

class Feedback extends Base
{
    protected $feedback;
    protected $userId;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->feedback = new FeedbackModel();
        $this->userId = $this->userInfo['id'];
    }

    /**
     * 获取用户反馈列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list(Request $request)
    {
        $page = $request->param('page', 1, 'intval');
        $limit = $request->param('limit', 20, 'intval');
        // 反馈信息的状态
        $status = $request->param('status', '');

        // 查询用户的反馈
        $query = $this->feedback->hasWhere('appletUser', ['id' => $this->userId]);

        // 筛选
        if ($status !== '') {
            $query->where('is_response', '=', $status);
        }

        $total = $query->count();
        $res = $query->page($page, $limit)->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success', $data);

    }


    /**
     * 发布反馈信息
     * @param Request $request
     * @return \think\response\Json
     */
    public function publish(Request $request)
    {
        $content = $request->param('content');
        $image = $request->param('image', '');
        $applet_user_id = $this->userId;

        try {
            validate(FeedbackValidate::class)->scene('add')->check([
                'applet_user_id' => $applet_user_id,
                'content' => $content
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        // 新增数据，其他使用默认属性
        $res = FeedbackModel::create([
            'image' => $image,
            'content' => $content,
            'is_response' => 0,
            'applet_user_id' => $applet_user_id
        ]);

        if(!empty($res)) {
            return $this->success('反馈成功！');
        }else{
            return $this->error('反馈失败！');
        }


    }
}