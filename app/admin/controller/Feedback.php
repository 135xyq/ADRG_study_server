<?php

namespace app\admin\controller;

use app\Request;
use app\validate\CommentValidate;
use app\validate\FeedbackValidate;
use think\App;
use app\model\Feedback as FeedbackModel;
use think\exception\ValidateException;
use app\model\AppletUser;

class Feedback extends Base
{
    protected $feedback;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->feedback = new FeedbackModel();
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
        $status = $request->param('status', ''); // 是否已回复
        $keyword = $request->param('keyword', '');// 内容关键词
        $userName = $request->param('userName',''); // 用户名


        // 关联预载入
        $query = $this->feedback->with(['appletUser' => function($query) {
            $query->field('id,nick_name');
        }]);

        $where = AppletUser::where('nick_name','like','%'.$userName.'%');

        if($userName !== '') {
            $query->hasWhere('appletUser',$where);
        }

        // 状态筛选
        if ($status !== '') {
            $query->where('is_response', '=', $status);
        }

        // 内容关键词筛选
        if (!empty($keyword)) {
            $query->where('content', 'like', '%' . $keyword . '%');
        }

        $total = $query->count(); // 统计数量
        $res = $query->order('create_time','desc')->page($page, $limit)->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success', $data);
    }


    /**
     * 反馈回复
     * @param Request $request
     * @return \think\response\Json
     */
    public function response(Request $request)
    {
        // 回复需要的数据,id和回复内容
        $id = $request->param('id');
        $response_content = $request->param('response_content');

        // 数据验证
        try {
            validate(FeedbackValidate::class)->scene('response')->check([
                'id' => $id,
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $feedback= $this->feedback->find($id);
        if (empty($feedback)) {
            return $this->error('用户反馈不存在');
        }

        if($feedback['is_response'] === 1) {
            return $this->error('用户反馈已回复');
        }

        $feedback->is_response = 1;
        $feedback->response_content = $response_content;
        $feedback->save();

        return $this->success('回复成功！');

    }

    /**
     * 删除用户反馈，支持批量删除
     * @param Request $request
     * @return \think\response\Json
     */
    public function delete(Request $request)
    {
        $id = $request->param('id');

        try {
            validate(FeedbackValidate::class)->scene('delete')->check(
                ['id' => $id]
            );
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $ids = ['id' => explode(',', $id)];
        $bool = FeedbackModel::destroy($ids);
        if ($bool) {

            // 写入操作日志
            $this->writeDoLog($request->param());


            return $this->success('删除成功！');
        } else {
            return $this->error('删除失败');
        }
    }
}
