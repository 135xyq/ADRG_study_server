<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class QuestionCategory extends Model
{
    use SoftDelete;
}