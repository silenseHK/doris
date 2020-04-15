<?php

namespace app\common\model\user;

use app\common\model\BaseModel;
use app\common\enum\user\grade\log\ChangeType as ChangeTypeEnum;

/**
 * 用户会员等级变更记录模型
 * Class GradeLog
 * @package app\common\model\user
 */
class GradeLog extends BaseModel
{
    protected $name = 'user_grade_log';
    protected $updateTime = false;

    /**
     * 新增变更记录 (批量)
     * @param $data
     * @return array|false
     * @throws \Exception
     */
    public function records($data)
    {
        $saveData = [];
        foreach ($data as $item) {
            $saveData[] = array_merge([
                'change_type' => ChangeTypeEnum::ADMIN_USER,
                'wxapp_id' => static::$wxapp_id
            ], $item);
        }
        return $this->isUpdate(false)->saveAll($saveData);
    }

    /**
     * 新增变更记录(单条)
     * @param $data
     * @return false|int
     */
    public function recordsOne($data){
        $data['wxapp_id'] = static::$wxapp_id ? : 10001;
        return $this->isUpdate(false)->save($data);
    }

}