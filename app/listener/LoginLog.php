<?php

namespace app\listener;

use app\model\Log;

class LoginLog
{

    public function handle($params){

        $data['username'] = $params['user'];
        $data['url'] = request()->url(true);
        $data['ip'] = request()->ip();
        $data['useragent'] = request()->server('HTTP_USER_AGENT');
        $data['type'] = 1;

        Log::create($data);
    }
}