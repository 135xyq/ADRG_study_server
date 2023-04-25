<?php

namespace app\admin\controller;

use app\model\AppletUserSet as AppletUserSetModel;
use app\model\Article as ArticleModel;
use app\model\Video as VideoModel;
use app\model\Question as QuestionModel;
use app\model\Star as StarModel;
use app\model\Comment as CommentModel;
use app\model\Like as LikeModel;
use app\model\Feedback as FeedbackModel;
use think\App;

class Dashboard extends Base
{
    protected $user;
    protected $article;
    protected $video;
    protected $question;
    protected $star;
    protected $like;
    protected $comment;
    protected $feedback;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->user = new AppletUserSetModel();
        $this->article = new ArticleModel();
        $this->video = new VideoModel();
        $this->question = new QuestionModel();
        $this->star = new StarModel();
        $this->like = new LikeModel();
        $this->comment = new CommentModel();
        $this->feedback = new FeedbackModel();
    }

    /**
     * 数据统计
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function index()
    {

        // 统计用户、视频、文章、题目的个数
        $statistical['userCount'] = $this->user->count();
        $statistical['videoCount'] = $this->video->count();
        $statistical['articleCount'] = $this->article->count();
        $statistical['questionCount'] = $this->question->count();

        // 统计最近10天新增的评论、点赞、收藏、注册用户、用户反馈
        $newAddCount['comment'] = $this->getLastTenDayCount($this->comment,10);
        $newAddCount['like'] = $this->getLastTenDayCount($this->like,10);
        $newAddCount['star'] = $this->getLastTenDayCount($this->star,10);
        $newAddCount['user'] = $this->getLastTenDayCount($this->user,10);
        $newAddCount['feedback'] = $this->getLastTenDayCount($this->feedback,10);

        // 统计排名前十的文章、视频、题目作答情况
        $topCount['article'] = $this->article->order('view_count','desc')->limit(10)->field(['id','title','view_count'])->select();
        $topCount['video'] = $this->video->order('view_count','desc')->limit(10)->field(['id','title','view_count'])->select();
        $topCount['question'] = $this->question->order('test_count','desc')->limit(10)->field(['id','title','test_count'])->select();

        $data = [
            'statistical' => $statistical,
            'newAddCount' => $newAddCount,
            'topCount' => $topCount
        ];

        return $this->success('success',$data);
    }

    /**
     * 获取过去天数新增的数量
     * @param $query
     * @param $dayCount
     * @return array
     */
    protected function getLastTenDayCount($query, $dayCount)
    {
        $count = [];
        for ($i = 0; $i < $dayCount; $i++) {
            // 开始时间
            $startDate = date("Y-m-d H:i:s", strtotime("-" . ($dayCount - $i) . "day"));
            // 结束时间
            $endDate = date("Y-m-d H:i:s", strtotime("-" . ($dayCount - $i - 1) . "day"));

            $count[] = $query->whereBetweenTime('create_time',$startDate,$endDate)->count();
        }

        return $count;
    }
}