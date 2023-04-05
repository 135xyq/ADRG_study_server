<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class StudyHistory extends Model
{
    use SoftDelete;
    
    // 关联视频表
    public function video(){
        return $this->belongsTo(Video::class);
    }

    // 关联文章表
    public function article() {
        return $this->belongsTo(Article::class);
    }

    // 关联用户表
    public function user() {
        return $this->belongsTo(AppletUser::class);
    }
}