<?php

namespace app\common;

use think\facade\Log;
use think\facade\Session;

class BaseServer
{

    /**
     * 请求成功
     * @param $msg string 请求信息
     * @param $code int 状态，成功为0
     * @return \think\response\Json 请求响应
     */
    public function success($msg = "success", $data = [], $code = 0)
    {
        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['data'] = empty($data) ? new \stdClass() : $data;
        return json($result);
    }

    /**
     * 返回错误
     * @return mixed|null
     */
    public function getEorror($error)
    {
        return $error;
    }

    /**
     * 请求失败
     * @param $msg string 请求信息
     * @param $code int 状态，成功为1
     * @return \think\response\Json 请求响应
     */
    public function error($msg = "fail",$data = [],  $code = 1)
    {
        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['data'] = empty($data) ? new \stdClass() : $data;
        $jsonResult = json_encode($result);
        // 写入日志
        Log::write($jsonResult);
        return json($result);
    }

    /** 操作日志写入
     * @param $data {array} 请求内容
     * @return void
     */
    public function writeDoLog($data)
    {
        if (Session::has('admin_info')) {
            event('DoLog', ['user' => Session::get('admin_info')['title'], 'content' => $data]);
        }
    }
}