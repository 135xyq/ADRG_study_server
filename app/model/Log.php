<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class Log extends Model
{
    use SoftDelete;
}