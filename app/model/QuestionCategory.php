<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class QuestionCategory extends Model
{
    use SoftDelete;

    public function getStatusTextAttr($value,$data)
    {
        $status = [0=>'显示',1=>'不显示'];
        return $status[$data['status']];
    }
}