<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Like extends Model
{
    use SoftDelete;

    // 获取评论用户
    public function user() {
        return $this->belongsTo(AppletUser::class);
    }

    // 关联视频表
    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    // 关联文章表
    public function article()
    {
        return $this->belongsTo(Article::class);
    }


    /**
     * 监听点赞删除，统计文章和视频的点赞数量
     * @param $like
     * @return void
     */
    public static function onAfterDelete($like)
    {
        $article = $like->article;
        // 更新文章的评论数
        if($article) {
            $article->updateStatistics();
        }

        // 更新视频的评论数
        $video = $like->video;
        if($video) {
            $video->updateStatistics();
        }

    }

    /**
     * 监听点赞新增，统计点赞数量
     * @param $like
     * @return void
     */
    public static function onAfterInsert($like)
    {
        $article = $like->article;
        // 更新文章的点赞数
        if($article) {
            $article->updateStatistics();
        }

        // 更新视频的评点赞数
        $video = $like->video;
        if($video) {
            $video->updateStatistics();
        }
    }
}