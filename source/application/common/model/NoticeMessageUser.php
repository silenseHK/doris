<?php


namespace app\common\model;


use traits\model\SoftDelete;

class NoticeMessageUser extends BaseModel
{

    protected $name = 'notice_message_user';

    protected $updateTime = false;

    use SoftDelete;
    protected $deleteTime = 'delete_time';

    /**
     * 绑定信息
     * @param $user_id
     * @param $message_id
     * @return false|int
     */
    public static function bindMsg($user_id, $message_id){
        return (new self)->isUpdate(false)->save(compact('user_id','message_id'));
    }

}