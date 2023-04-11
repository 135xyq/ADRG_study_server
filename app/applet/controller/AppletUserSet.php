<?php

namespace app\applet\controller;

use app\model\AppletUserSet as AppletUserSetModel;
use app\Request;
use app\validate\AppletUserSetValidate;
use app\validate\AppletUserValidate;
use think\App;
use think\exception\ValidateException;

class AppletUserSet extends Base
{
    protected $userId;
    protected $userSet;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->userSet = new AppletUserSetModel();
        $this->userId = $this->userInfo['id'];
    }

    /**
     * 获取用户的配置信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUserSetInfo()
    {
        $set = $this->userSet->where('applet_user_id', '=', $this->userId)->find();
        return $this->success('success', $set);
    }

    /**
     * 修改用户的配置信息
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editUserSet(Request $request)
    {
        $question_type = $request->param('type', '', 'intval');
        $level = $request->param('level', '', 'intval');
        $question_count = $request->param('count', '', 'intval');

        $data = [];

        if ($question_count !== '') {
            $data['question_count'] = $question_count;
        }
        if ($question_type !== '') {
            $data['question_type'] = $question_type;
        }
        if ($level !== '') {
            $data['level'] = $level;
        }

        try {
            validate(AppletUserSetValidate::class)->scene('edit')->check([
                'question_type' => $question_type,
                'question_count' => $question_count,
                'level' => $level
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        $user = $this->userSet->where('applet_user_id', '=', $this->userId)->find();
        if (empty($user)) {
            return $this->error('出错了!');
        } else {
            $user->save($data);
            return $this->success('修改成功!');
        }
    }
}