<?php


namespace app\common\model\user;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class BankCard extends BaseModel
{

    protected $name = 'user_bank_card';

    protected $deleteTime = 'delete_time';

    protected $insert = ['wxapp_id'];

    use SoftDelete;

    /**
     * 修改器 -- 设置 wxapp_id
     * @return mixed
     */
    public function setWxappIdAttr(){
        return self::$wxapp_id;
    }

}