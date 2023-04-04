<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Video extends Model
{
    use SoftDelete;

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
     * 监听视频删除事件，删除之前先删除评论、收藏、点赞
     * @param $article
     * @return mixed|void
     */
    public static function onBeforeDelete($video)
    {
        Comment::where('video_id',$video->id)->select()->delete();
        Like::where('video_id',$video->id)->select()->delete();
        Star::where('video_id',$video->id)->select()->delete();
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
        $likeCount = Like::where('video_id', $this->id)->count();

        // 统计收藏数
        $starCount = Star::where('video_id', $this->id)->count();

        // 更新视频表中评论数量
        $this->comment_count = $commentCount;

        // 更新视频表中点赞数量
        $this->like_count = $likeCount;

        // 更新视频表中点赞数量
        $this->star_count = $starCount;

        $this->save();
    }

}