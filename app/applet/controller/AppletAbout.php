<?php

namespace app\applet\controller;

use app\model\AppletAbout as AppletAboutModel;
use think\App;

class AppletAbout extends Base
{
    protected $appletAbout;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->appletAbout = new AppletAboutModel();
    }

    /**
     * 获取信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAppletAbout()
    {
        $res = $this->appletAbout->select();
        if(count($res) > 0) {
            return $this->success('success', $res[0]);
        }else{
            return $this->success('success', []);
        }
    }
}