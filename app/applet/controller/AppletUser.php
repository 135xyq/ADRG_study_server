<?php

namespace app\applet\controller;

use app\Request;
use think\facade\Cache;

class AppletUser extends Base
{
    public function getUserInfo(Request $request) {
        $userInfo = Cache::get($this->token);
        return $this->success('success',json_decode($userInfo));
    }
}