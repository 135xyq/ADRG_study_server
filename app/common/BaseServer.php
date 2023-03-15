<?php

namespace app\common;

use think\facade\Log;
use think\facade\Session;

class BaseServer
{
    protected $result = null;
    protected $error = null;

    /**
     * 请求成功
     * @param $msg string 请求信息
     * @param $code int 状态，成功为0
     * @return \think\response\Json 请求响应
     */
    protected function success($msg = "success",$code = 0){
        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['data'] = empty($this->result) ? new \stdClass() : $this->result;
        return json($result);
    }

    /**
     * 返回错误
     * @return mixed|null
     */
    public function getEorror(){
        return $this->error;
    }

    /**
     * 请求失败
     * @param $msg string 请求信息
     * @param $code int 状态，成功为1
     * @return \think\response\Json 请求响应
     */
    protected function error($msg = "fail",$code = 1){
        $result['code'] = $code;
        $result['msg'] = $msg;
        $result['data'] = empty($this->result) ? new \stdClass() : $this->result;
        $jsonResult = json_encode($result);
        Log::write($jsonResult);
        return json($result);
    }

    /** 操作日志写入
     * @param $data {array} 请求内容
     * @return void
     */
    protected function writeDoLog($data){
        event('DoLog',['user'=>Session::get('admin_info')['account'],'content'=>$data]);
    }
}