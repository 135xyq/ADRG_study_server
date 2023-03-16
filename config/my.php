<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 自定义配置
// +----------------------------------------------------------------------
return [
    'token_expire_time' => '120', // 登录过期时间单位秒
    'upload_subdir'		=> 'Ym',				//文件上传二级目录 标准的日期格式
    'nocheck'			=> ['admin/Login/login'],	//不需要验证权限的url
    'dump_extension'	=> 'xlsx',				//默认导出格式
    'filetype'	=> 'jpg,jpeg,png,gif,mp4,3gp,m3u8,doc,docx,xls,xlsx,pem',  //上传文件文件类型
    'filesize'	=> 50,				            //上传文件最大限制(M)
    'check_file_status'	=> true,			//上传图片是否检测图片存在
];
