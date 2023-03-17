<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Video extends Model
{
    use SoftDelete;

    public function studyCategory()
    {
        return $this->belongsTo(StudyCategory::class);
    }
}