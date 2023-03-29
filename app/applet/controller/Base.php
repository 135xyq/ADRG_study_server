<?php

namespace app\applet\controller;

use app\BaseController;
use app\common\BaseServer;
use think\App;
use think\facade\Cache;
use think\facade\Request;

class Base extends BaseController
{
    protected $baseServer;
    protected $token;
    protected $userInfo;
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

    /**
     * 重写基础控制器的初始化方法
     * @return void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function initialize(){
        $this->checkTokenAuth();
    }

    /**
     * 校验是否登录，有token
     */
    protected function checkTokenAuth() {

        $token = Request::param('token');

        if(empty($token)) {
            abort(403,'token不存在');
        }

        $this->userInfo = Cache::get($token);
        if(empty($this->userInfo)){
            abort(403,'token不存在');
        }

        if(!is_array($this->userInfo)){
            $this->userInfo = json_decode($this->userInfo,true);
        }


        $this->token = $token;
    }
}