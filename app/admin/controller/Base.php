<?php

namespace app\admin\controller;

use app\BaseController;
use app\common\BaseServer;
use app\model\AdminSession;
use think\App;
use think\facade\Request;

class Base extends BaseController
{
    protected $baseServer;

    public function __construct(App $app)
    {
        parent::__construct($app);
        // 将BaseServer的响应数据挂载到类
        $this->baseServer = new BaseServer();
    }

    protected function success($msg = "success",$data = [], $code = 0){
       return $this->baseServer->success($msg ,$data, $code);
    }
    protected function error($msg = "fail",$data =[], $code = 1){
        return $this->baseServer->error($msg,$data, $code);
    }
    protected function getError($error){
        return $this->baseServer->getEorror($error);
    }
    protected function writeDoLog($data){
        $this->baseServer->writeDoLog($data);
    }


    /**
     * 重写基础控制器的初始化方法
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function initialize(){
        $controller = $this->request->controller();
        $action = $this->request->action();
        $app = app('http')->getName();
        $url = "{$app}/{$controller}/{$action}";
        if (!in_array($url, config('my.nocheck'))) {
            $this->checkTokenAuth();
        }
    }


    /**
     * 设置管理员登录的token
     * @param $data
     * @return string
     */
    public function setToken($data)
    {

        $token = md5(uniqid());//根据时间和md5生成唯一的token

        //登录的时候把token写入数据表
        $tokenInfo = AdminSession::where('token', $token)->find();
        if (empty($tokenInfo)) {
            AdminSession::create([
                'token' => $token,
                'expire_time' => time(),
                'data' => json_encode($data),
                'status' => 1
            ]);
        } else {
            AdminSession::where('token', $token)->update([
                'token' => $token,
                'expire_time' => time(),
                'data' => json_encode($data),
                'status' => 1
            ]);
        }

        return $token;
    }

    /**
     * 获取token
     * @return array|string
     */
    protected function getToken() {
        return  Request::header('Authorization');
    }


    /**
     * 判断token是否存在、判断登录状态是否应该存在
     * @return \think\response\Json|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function checkTokenAuth() {

        $token = Request::header('Authorization');

        if(empty($token)) {
            abort(403,'token不能为空，请确保处于登录状态！');
        }

        // 获取到指定的token信息
        $tokenInfo = AdminSession::where('token','=', $token)->find();

        if(empty($tokenInfo)) {
            abort(403,'token不存在！登录信息错误！');
        }

        if($tokenInfo['status'] === 0) {
            abort(403,'登录信息失效！请重新登录！');
        }


        // dump(config('my.token_expire_time'));
        // dump(($tokenInfo['expire_time'] + config('my.token_expire_time')));
        // dump(time());

        if (($tokenInfo['expire_time'] + config('my.token_expire_time')) < time()) {
            abort(403, '登录状态已过期，请重新登录');
        }
    }
}