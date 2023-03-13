<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class AdminUser extends Model
{
    use SoftDelete;
}