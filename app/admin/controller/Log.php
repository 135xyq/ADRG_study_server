<?php

// 日志记录

namespace app\admin\controller;

use app\Request;
use app\model\Log as LogModel;

class Log extends Base
{
    /**
     * 分页查询数据
     * @param Request $request 请求信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function page(Request $request) {
        $page = $request->param('page',1,'intval');
        $limit = $request->param('limit',20,'intval');
        $type = $request->param('type','');
        $create_time  = $request->param('create_time','');
        $username = $request->param('username','');

        // 查找的数据
        $where = [];
        if (!empty($username)) {
            $where['username'] = $username;
        }

        if (!empty($type)) {
            $where['type'] = $type;
        }

        $field = 'id,username,url,ip,useragent,content,error_info,create_time,type';

        // 先筛选用户名和type
        $query = LogModel::where($where);

        // 如果有时间查询则再筛选时间
        if(!empty($create_time)){
            $query->where('create_time','between',[$create_time[0],$create_time[1]]);
        }

        // 统计总数据量
        $count = $query->count();

        $res = $query->field($field)->order('id','desc')->page($page, $limit)->select()->toArray();

        $data = [
            'total' => $count,
            'data' => $res
        ];
        return $this->success('success', $data);
    }


    /**
     * 删除日志信息
     * @param Request $request
     * @return \think\response\Json
     */
    public function delete(Request $request) {
        $id = $request->param('id');
        if(!$id){
            return $this->error('参数错误！');
        }

        $ids = ['id'=>explode(',',$id)];
        $bool = LogModel::destroy($ids);
        if($bool){

            // 写入操作日志
            $this->writeDoLog($request->param());

            return $this->success('删除成功！');
        }else{
            return $this->error('删除失败');
        }
    }

    public function detail(Request $request) {
        $id= $request->param('id');
        if(!$id) {
            return $this->error('参数错误！');
        }
        $res = LogModel::find($id);
        if(empty($res)) {
            return $this->error('日志记录不存在！');
        } else {
            return $this->success('success',$res);
        }
    }
}
