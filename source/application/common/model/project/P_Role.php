<?php


namespace app\common\model\project;


use traits\model\SoftDelete;

class P_Role extends P_Base
{

    protected $name = 'p_role';

    use SoftDelete;

    protected $delete_time = 'delete_time';

    public function lists()
    {
        return $this->field('id, title')->select();
    }

}