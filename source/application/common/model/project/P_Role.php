<?php


namespace app\common\model\project;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class P_Role extends BaseModel
{

    protected $table = 'yoshop_p_role';

    use SoftDelete;

    protected $delete_time = 'delete_time';

    public function lists()
    {
        return $this->field('id, title')->select();
    }

}