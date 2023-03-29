<?php

namespace app\applet\controller;

use app\common\UploadServer;

class Upload extends UploadServer
{
    private $uploadServer;
    public function __construct()
    {
        parent::__construct();
        $this->uploadServer = new UploadServer();
    }

    public function upload(){
        return $this->uploadServer->upload();
    }
}