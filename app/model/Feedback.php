<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Feedback extends Model
{
    use SoftDelete;
    // 关联用户表
    public function appletUser(){
        return $this->belongsTo(AppletUser::class);
    }
}