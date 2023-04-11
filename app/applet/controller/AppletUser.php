<?php

namespace app\applet\controller;

use app\Request;
use app\validate\AppletUserValidate;
use think\App;
use think\exception\ValidateException;
use think\facade\Cache;
use app\model\AppletUser as AppletUserModel;

class AppletUser extends Base
{
    protected $userId;
    protected $appletUser;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->userId = $this->userInfo['id']; // 用户id
        $this->appletUser = new AppletUserModel();
    }

    public function getUserInfo(Request $request)
    {
        $userInfo = Cache::get($this->token);
        return $this->success('success', json_decode($userInfo));
    }

    public function edit(Request $request)
    {
        $data = $request->only(['nick_name', 'avatar', 'gender']);

        if (!empty($data['nick_name'])) {
            try {
                validate(AppletUserValidate::class)->check([
                    'nick_name' => $data['nick_name']
                ]);

                // 验证是否存在同名的用户
                $count = $this->appletUser->where([['nick_name', '=', $data['nick_name']],['id','<>',$this->userId]])->count();
                if ($count) {
                    return $this->error('昵称已存在');
                }

            } catch (ValidateException $e) {
                return $this->error($e->getError());
            }
        }


        $user = $this->appletUser->find($this->userId);
        if (!empty($user)) {
            $user->save($data);

            $field = ['id','nick_name','gender','avatar'];

            // 重新获取用户信息
            $newUserInfo = $this->appletUser->field($field)->find($this->userId);

            // 重置token信息
            Cache::set($this->token, json_encode($newUserInfo, JSON_UNESCAPED_UNICODE));

            return $this->success('修改成功！');
        } else {
            // 用户不存在，删除token
            Cache::delete($this->token);
            return $this->error('用户不存在！');
        }
    }
}