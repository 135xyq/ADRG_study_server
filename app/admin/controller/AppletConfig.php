<?php


// 小程序配置控制器

namespace app\admin\controller;

// 小程序配置信息

use app\model\AppletConfig as AppletConfigModel;
use app\Request;
use app\validate\AppletConfigValidate;
use think\App;
use think\exception\ValidateException;

class AppletConfig extends Base
{

    protected $appletConfigModel;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->appletConfigModel = new AppletConfigModel();
    }

    //获取配置信息
    public function get()
    {
        $config = AppletConfigModel::select();
        // 存在配置
        if (count($config) > 0) {
            return $this->success('success', $config[0]);
        } else {
            return $this->success('success');
        }
    }

    /**
     * 修改小程序的配置信息
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function update(Request $request)
    {
        $id = $request->param('id');
        $is_auto_check_comment = $request->param('is_auto_check_comment', '');
        $is_auto_check_user_name = $request->param('is_auto_check_user_name', '');
        $sensitive_words = $request->param('sensitive_words', []);

        // 验证数据合理性
        try {
            validate(AppletConfigValidate::class)->scene('update')->check([
                'id' => $id,
                'is_auto_check_comment' => $is_auto_check_comment,
                'is_auto_check_user_name' => $is_auto_check_user_name
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }

        if (empty($this->appletConfigModel->where('id', $id)->find())) {
            return $this->error('id传入错误！要修改的配置不存在！');
        }

        if (!empty($request->param('appid'))) {
            $saveData['appid'] = $request->param('appid');
        }

        if (!empty($request->param('secret'))) {
            $saveData['secret'] = $request->param('secret');
        }

        $saveData['is_auto_check_comment'] = $is_auto_check_comment;
        $saveData['is_auto_check_user_name'] = $is_auto_check_user_name;
        $saveData['sensitive_words'] = $sensitive_words;

        // 修改数据
        $config = $this->appletConfigModel->find($id);
        $bool = $config->save($saveData);

        if ($bool) {
            // 写入操作日志
            $this->writeDoLog($request->param());

            return $this->success('修改成功！');
        } else {
            return $this->error('修改失败');
        }
    }

    /**
     * 新增小程序配置
     * @param Request $request
     * @return \think\response\Json
     */
    public function add(Request $request)
    {
        $appid = $request->param('appid');
        $secret = $request->param('secret');
        $is_auto_check_comment = $request->param('is_auto_check_comment', 0);
        $is_auto_check_user_name = $request->param('is_auto_check_user_name', 0);
        $sensitive_words = $request->param('sensitive_words', []);

        // 验证数据合理性
        try {
            validate(AppletConfigValidate::class)->scene('add')->check([
                'appid' => $appid,
                'secret' => $secret,
                'is_auto_check_comment' => $is_auto_check_comment,
                'is_auto_check_user_name' => $is_auto_check_user_name
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $bool = $this->appletConfigModel->save([
            'appid' => $appid,
            'secret' => $secret,
            "is_auto_check_comment" => $is_auto_check_comment,
            "is_auto_check_user_name" => $is_auto_check_user_name,
            "sensitive_words" => $sensitive_words
        ]);
        if ($bool) {
            return $this->success('新增成功！');
        } else {
            return $this->error('新增失败!');
        }
    }
}
