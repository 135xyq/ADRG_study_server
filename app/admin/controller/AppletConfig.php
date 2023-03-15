<?php

namespace app\admin\controller;

// 小程序配置信息
use app\common\BaseServer;
use app\model\AppletConfig as AppletConfigModel;
use app\Request;
use app\validate\AppletConfigValidate;
use think\facade\Session;
use think\exception\ValidateException;

class AppletConfig extends BaseServer
{

    protected $appletConfigModel;
    public function __construct(){
        $this->appletConfigModel = new AppletConfigModel();
    }
        //获取配置信息
    public function get(){
        $config = AppletConfigModel::select();
        // 存在配置
        if(count($config) >0){
            $this->result = $config[0];
        }else{
            $this->result = [];
        }
        return $this->success();
    }

     // 修改小程序配置信息
     public function update(Request $request){
         $id = $request->param('id');
         // 验证数据合理性
         try {
             validate(AppletConfigValidate::class)->scene('update')->check([
                 'id' => $id
             ]);
         } catch (ValidateException $e) {
             return $this->error($e->getError());
         }
         if(empty($this->appletConfigModel->where('id',$id)->find())){
             return $this->error('id传入错误！要修改的配置不存在！');
         }
         if(!empty($request->param('appid'))){
             $saveData['appid'] = $request->param('appid');
         }
         if(!empty($request->param('secret'))){
             $saveData['secret'] = $request->param('secret');
         }
         $bool = $this->appletConfigModel->where('id',$id)->update($saveData);
         if($bool){
             // 写入操作日志
             $this->writeDoLog($request->param());

             return $this->success('修改成功！');
         }else{
             return $this->error('修改失败');
         }
     }

    // 新增小程序配置
    public function add(Request $request){
        $appid = $request->param('appid');
        $secret = $request->param('secret');
        // 验证数据合理性
        try {
            validate(AppletConfigValidate::class)->scene('add')->check([
                'appid' => $appid,
                'secret' => $secret
            ]);
        } catch (ValidateException $e) {
            return $this->error($e->getError());
        }
        $bool = $this->appletConfigModel->save([
            'appid' => $appid,
            'secret' => $secret
        ]);
        if($bool){
            return $this->success('新增成功！');
        }else{
            return $this->error('新增失败!');
        }
    }
}
