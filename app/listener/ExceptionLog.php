<?php

namespace app\listener;

use app\model\Log;

class ExceptionLog
{
    // 写入异常日志
    public function handle($event){

        $data['url'] = request()->url(true);
        $data['ip'] = request()->ip();
        $data['useragent'] = request()->server('HTTP_USER_AGENT');
        $data['error_info'] = $event;
        $data['type'] = 3;

        Log::create($data);
    }


}