<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Star extends Model
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
     * 监听收藏删除，统计文章和视频的收藏数量
     * @param $star
     * @return void
     */
    public static function onAfterDelete($star)
    {
        $article = $star->article;
        // 更新文章的评论数
        if($article) {
            $article->updateStatistics();
        }

        // 更新视频的评论数
        $video = $star->video;
        if($video) {
            $video->updateStatistics();
        }

    }

    /**
     * 监听收藏新增，统计收藏数量
     * @param $like
     * @return void
     */
    public static function onAfterInsert($star)
    {
        $article = $star->article;
        // 更新文章的收藏数
        if($article) {
            $article->updateStatistics();
        }

        // 更新视频的评收藏数
        $video = $star->video;
        if($video) {
            $video->updateStatistics();
        }
    }
}