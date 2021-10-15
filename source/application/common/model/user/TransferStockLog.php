<?php


namespace app\common\model\user;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class TransferStockLog extends BaseModel
{

    protected $name = 'user_transfer_stock_log';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

}