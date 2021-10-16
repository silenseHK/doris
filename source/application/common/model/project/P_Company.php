<?php


namespace app\common\model\project;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class P_Company extends BaseModel
{

    protected $name = 'p_company';

    use SoftDelete;

    protected $delete_time = 'delete_time';

    public function lists()
    {
        return $this->select();
    }

}