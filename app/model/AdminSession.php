<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class AdminSession extends Model
{
    use SoftDelete;

}