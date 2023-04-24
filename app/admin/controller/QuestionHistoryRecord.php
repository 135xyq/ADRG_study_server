<?php

namespace app\admin\controller;

use app\Request;
use Exception;
use think\App;
use app\model\QuestionHistoryRecord as QuestionHistoryRecordModel;
use think\response\Json;

class QuestionHistoryRecord extends Base
{
    protected $questionHistoryRecord;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->questionHistoryRecord = new QuestionHistoryRecordModel();
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

        if (empty($data)) {
            return $this->error('阅卷结果不能为空!');
        }

        $res = $this->questionHistoryRecord->saveAll($data);

        if (!empty($res)) {
            return $this->success('阅卷成功！');
        } else {
            return $this->error('阅卷失败');
        }

    }

}