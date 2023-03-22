<?php

namespace app\applet\controller;

use app\common\BaseServer;
use app\Request;
use app\model\Video as VideoModel;

class Video extends BaseServer
{
    private $video;
    public function __construct() {
        $this->video = new VideoModel();
    }

    /**
     * 根据分类获取视频列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function list(Request $request) {
        // 获取分类id
        $id = $request->param('category_id');

        if(empty($id)) {
            return $this->error('请选择分类!');
        }

        $query = $this->video->where([['status','=',1],['study_category_id','=',$id]]);

        $total = $query->count();
        $res = $query->select();
        $data = ['total'=>$total,'data'=>$res];
        return $this->success('success',$data);
    }
}