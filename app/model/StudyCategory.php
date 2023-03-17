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
}