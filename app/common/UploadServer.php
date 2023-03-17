<?php


namespace app\common;

use think\exception\ValidateException;
use think\facade\Filesystem;
use think\facade\Validate;

class UploadServer extends BaseServer
{
    private $request;

    public function __construct()
    {
        $this->request = request();
    }

    function upload()
    {
        return $this->checkUpload();
    }

    public function checkUpload()
    {
        $file = $this->request->file('file');

        if(empty($file)) {
            return $this->error('请选择文件！');
        }
        $file_type = config('my.filetype'); //允许上传的文件类型
        // 验证文件上传类型
        if (!Validate::fileExt($file, $file_type)) {
            return $this->error('文件类型不允许上传！');
        }
        // 验证文件上传尺寸
        if (!Validate::fileSize($file, config('my.filesize') * 1024 * 1024)) {
            return $this->error('文件过大！');
        }
        if ($url = $this->up($file)) {
            return $this->success('文件上传成功！', ['url' => $url]);
        }
        return $this->error("上传失败");
    }

    //开始上传
    protected function up($file)
    {

        $filename = Filesystem::disk('public')->putFile($this->getFileName(), $file, 'uniqid');

        return  config('filesystem.disks.public.url') . '/' . $filename;
    }

    //获取上传的文件完整路径
    private function getFileName()
    {
        return app('http')->getName() . '/' . date(config('my.upload_subdir'));
    }


}