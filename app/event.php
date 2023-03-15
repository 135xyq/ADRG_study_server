<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'ExceptionLog'=>['app\listener\ExceptionLog'],	//异常日志
        'LoginLog'=>['app\listener\LoginLog'],	//登录日志
        'DoLog'=>['app\listener\DoLog'],	//操作日志
    ],

    'subscribe' => [
    ],
];
