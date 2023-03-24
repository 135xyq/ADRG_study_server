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

    // 获取评论的父评论信息
    public function parentComment()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    // 获取评论的子评论信息
    public function childComments()
    {
        return $this->hasMany(Comment::class, 'parent_id');
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