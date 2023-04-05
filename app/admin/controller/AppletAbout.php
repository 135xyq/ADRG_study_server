<?php

namespace app\admin\controller;

use app\model\AppletAbout as AppletAboutModel;
use app\Request;
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

    /**
     * 新增或修改
     * @param Request $request
     * @return \think\response\Json
     */
    public function addAppletAbout(Request $request)
    {
        $content = $request->param('content','');
        $id = $request->param('id','');

        if($content === ''){
            return $this->error('内容不能为空');
        }

        // 新增
        if($id === '') {
            AppletAboutModel::create([
                'content' => $content
            ]);

            return $this->success('新增成功！');
        }else{
            // 修改
            $about = $this->appletAbout->find($id);
            if(empty($about)) {
                return $this->error('id无效');
            }else{
                $about->content = $content;
                $about->save();

                return $this->success('修改成功！');
            }
        }

    }

}