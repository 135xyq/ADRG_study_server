<?php
declare (strict_types = 1);

namespace app\listener;

use app\model\Log;

class DoLog
{
    /** 记录删除、更新的操作
     * 事件监听处理
     *
     */
    public function handle($event)
    {
            $data['username'] = $event['user'];
            $data['url'] = request()->url(true);
            $data['ip'] = request()->ip();
            $data['useragent'] = request()->server('HTTP_USER_AGENT');
            $data['content'] = json_encode($event['content'],JSON_UNESCAPED_UNICODE);
            $data['type'] = 2;

            Log::create($data);
    }
}
