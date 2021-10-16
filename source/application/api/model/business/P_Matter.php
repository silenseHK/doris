<?php


namespace app\api\model\business;


use app\common\model\project\P_Matter as Base_P_Matter;

class P_Matter extends Base_P_Matter
{

    protected $error = '';

    protected $code = 0;

    protected function setError($msg='操作失败', $code=1)
    {
        $this->error = $msg;
        $this->code = $code;
        return false;
    }

    public function add()
    {
        if(!$this->save(request()->post()))
        {
            return $this->setError('创建失败');
        }
        return true;
    }

}