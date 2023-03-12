<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    // 过滤器  html转义
    protected $filter = ['htmlspecialchars'];
}
