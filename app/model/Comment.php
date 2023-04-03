<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Comment extends Model
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

    // 关联评论回复表
    public function response()
    {
        return $this->hasMany(CommentResponse::class);
    }

    /**
     * 监听评论删除，统计文章和视频的评论数量
     * @param $comment
     * @return void
     */
    public static function onAfterDelete($comment)
    {
        $article = $comment->article;
        // 更新文章的评论数
        if($article) {
            $article->updateStatistics();
        }

        // 更新视频的评论数
        $video = $comment->video;
        if($video) {
            $video->updateStatistics();
        }

    }

    /**
     * 监听评论新增，统计评论数量
     * @param $comment
     * @return void
     */
    public static function onAfterInsert($comment)
    {
        $article = $comment->article;
        // 更新文章的评论数
        if($article) {
            $article->updateStatistics();
        }

        // 更新视频的评论数
        $video = $comment->video;
        if($video) {
            $video->updateStatistics();
        }
    }
}