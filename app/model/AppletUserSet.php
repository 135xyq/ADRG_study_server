<?php

namespace app\model;

use think\Model;

class AppletUserSet extends Model
{
    // 关联用户表
    public function user()
    {
        return $this->hasOne(AppletUser::class);
    }
}