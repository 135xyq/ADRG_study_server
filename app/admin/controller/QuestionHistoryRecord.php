<?php

namespace app\admin\controller;

use app\Request;
use Exception;
use think\App;
use app\model\QuestionHistoryRecord as QuestionHistoryRecordModel;
use app\model\QuestionRecord as QuestionRecordModel;
use think\response\Json;

class QuestionHistoryRecord extends Base
{
    protected $questionHistoryRecord;
    protected $questionRecord;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->questionHistoryRecord = new QuestionHistoryRecordModel();
        $this->questionRecord = new QuestionRecordModel();
    }

    /**
     * 人工阅卷
     * @param Request $request
     * @return Json
     * @throws Exception
     */
    public function peopleValidateRecord(Request $request)
    {
        $data = $request->param('validateData', []);
        $record = $request->param('record','');

        if (empty($data)) {
            return $this->error('阅卷结果不能为空!');
        }

        if($record === '' ){
            return  $this->error('试卷id不能为空');
        }

        $recordInfo = $this->questionRecord->find($record);

        if(empty($recordInfo)){
            return  $this->error('获取试卷出错了');
        }

        if($recordInfo->is_submit === 0){
            return  $this->error('用户未完成无法判卷！');
        }


        $res = $this->questionHistoryRecord->saveAll($data);

        $recordInfo->is_submit = 2;
        $recordInfo->save();

        if (!empty($res)) {
            return $this->success('阅卷成功！');
        } else {
            return $this->error('阅卷失败');
        }

    }

}