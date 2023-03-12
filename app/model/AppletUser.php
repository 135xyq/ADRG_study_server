<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class AppletUser extends Model
{
    use SoftDelete;
}