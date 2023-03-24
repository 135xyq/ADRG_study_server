<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Video extends Model
{
    use SoftDelete;

    /**
     * 监听视频删除事件，删除之前先删除评论
     * @param $article
     * @return mixed|void
     */
    public static function onBeforeDelete($video)
    {
        Comment::where('video_id',$video->id)->select()->delete();
    }
    // 关联评论表
    public function studyCategory()
    {
        return $this->belongsTo(StudyCategory::class);
    }

    // 关联评论表
    public function comment()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 更新视频的数据、评论数量
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function updateStatistics()
    {
        // 统计评论数
        $commentCount = Comment::where('video_id', $this->id)->count();

        // 统计点赞数
        $likeCount = Like::where('article_id', $this->id)->count();

        // 更新文章表中评论数量
        $this->comment_count = $commentCount;

        // 更新文章表中点赞数量
        $this->like_count = $likeCount;
        $this->save();
    }

}