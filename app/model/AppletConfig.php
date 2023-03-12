<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

class AppletConfig extends Model
{
    use SoftDelete;
}