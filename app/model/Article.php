<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Article extends Model
{
    use SoftDelete;

    // 关联分类表
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
     * 监听文章删除事件，删除之前先删除评论、点赞、收藏记录
     *
     * @param $article
     * @return mixed|void
     */
    public static function onBeforeDelete($article)
    {
        Comment::where('article_id',$article->id)->select()->delete();
        Like::where('article_id',$article->id)->select()->delete();
        Star::where('article_id',$article->id)->select()->delete();
    }


    /**
     * 更新文章的数据、评论数量
     * @return void
     * @throws \think\db\exception\DbException
     */
    public function updateStatistics()
    {
        // 统计评论数
        $commentCount = Comment::where('article_id', $this->id)->count();

        // 统计点赞数
        $likeCount = Like::where('article_id', $this->id)->count();

        // 更新文章表中的评论字段
        $this->comment_count = $commentCount;

        // 更新文章表中的点赞字段
        $this->like_count = $likeCount;
        $this->save();
    }


}