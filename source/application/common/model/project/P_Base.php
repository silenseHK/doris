<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/17
 * Time: 11:22
 */

namespace app\common\model\project;


use app\common\model\BaseModel;

class P_Base extends BaseModel
{

    protected $error = '';

    protected $code = 0;

    protected function setError($msg='操作失败', $code=1)
    {
        $this->error = $msg;
        $this->code = $code;
        return false;
    }

}