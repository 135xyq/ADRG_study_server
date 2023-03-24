<?php

namespace app\admin\controller;

use app\Request;
use think\App;
use app\model\AppletUser as AppletUserModel;

class AppletUser extends Base
{

    protected $appletUser;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->appletUser = new AppletUserModel();
    }

    /**
     * 分页查询获取用户列表
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
        $keyword = $request->param('keyword', '');// 昵称关键词

        $query = $this->appletUser->where('nick_name', 'like', '%'.$keyword.'%');

        $total = $query->count();
        $res = $query->page($page, $limit)->select();
        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success', $data);

    }
}