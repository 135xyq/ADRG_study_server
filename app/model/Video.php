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
}