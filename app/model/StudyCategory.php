<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class StudyCategory extends Model
{
    use SoftDelete;

    public function video()
    {
        return $this->hasMany(Video::class,'study_category_id');
    }

    public function article()
    {
        return $this->hasMany(Article::class,'study_category_id');
    }

    /**
     * 监听分类的删除事件,在删除前先删除分类下的文章和视频
     *
     * @param $studyCategory
     * @return mixed|void
     */
    public static function onBeforeDelete($studyCategory)
    {
        Article::where('study_category_id',$studyCategory->id)->select()->delete();
        Video::where('study_category_id',$studyCategory->id)->select()->delete();
    }
}