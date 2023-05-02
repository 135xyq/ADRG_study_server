<?php

namespace app\applet\controller;

use app\Request;
use think\App;

use app\model\Article as ArticleModel;
use app\model\Video as VideoModel;
use app\model\Question as QuestionModel;

class Search extends Base
{
    protected $article;
    protected $video;
    protected $question;
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->question = new QuestionModel();
        $this->article = new ArticleModel();
        $this->video = new VideoModel();
    }

    /**
     * 根据关键词查询
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index(Request $request) {
        // 查询的分类
        $type = $request->param('type');
        // 要查询的内容
        $content = $request->param('content');
        $page = $request->param('page',1,'intval');
        $limit = $request->param('limit',20,'intval');

        $query = null;

        // 查询文章
        if($type === 'article') {
            $query = $this->article->where('title','like','%'.$content.'%')->where('status','=',1);
        }

        // 查询视频
        if($type === 'video') {
            $query = $this->video->where('title','like','%'.$content.'%')->where('status','=',1);
        }

        // 查询文章
        if($type === 'question') {
            $query = $this->question->where('title','like','%'.$content.'%')->where('status','=',1);
        }

        $total = $query->count();
        $res = $query->field(['id','title'])->page($page,$limit)->select();

        $data = [
            'total' => $total,
            'data' => $res
        ];

        return $this->success('success',$data);
    }
}