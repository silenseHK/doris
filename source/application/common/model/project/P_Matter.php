<?php


namespace app\common\model\project;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class P_Matter extends BaseModel
{

    protected $name = 'p_matters';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

}