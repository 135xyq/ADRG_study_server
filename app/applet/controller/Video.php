<?php

namespace app\applet\controller;

use app\common\BaseServer;
use app\Request;
use app\model\Video as VideoModel;
use think\App;

class Video extends Base
{
    private $video;
    public function __construct(App $app)
    {
        parent::__construct($app);
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


    /**
     * 根据分类获取视频分页列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function page(Request $request) {
        // 获取分类id
        $id = $request->param('category_id');
        $page = $request->param('page',1,'intval');
        $limit = $request->param('limit',10,'intval');

        if(empty($id)) {
            return $this->error('请选择分类!');
        }

        $query = $this->video->where([['status','=',1],['study_category_id','=',$id]]);

        $total = $query->count();
        $res = $query->page($page,$limit)->select();
        $data = ['total'=>$total,'data'=>$res];
        return $this->success('success',$data);
    }


    public function detail(Request $request)
    {
        $videoId = $request->param('id');

        if(empty($videoId)) {
            return $this->error('选择视频不能为空！');
        }

        $res = $this->video->find($videoId);

        if(empty($res)) {
            return $this->error('视频不存在！');
        }else{
            return $this->success('success',$res);
        }
    }
}