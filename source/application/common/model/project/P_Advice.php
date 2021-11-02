<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/17
 * Time: 11:35
 */

namespace app\common\model\project;


class P_Advice extends P_Base
{

    protected $name = 'p_advice';

    protected $updateTime = false;

    public function annex()
    {
        return $this->belongsToMany('app\common\model\UploadFile','p_advice_annex','file_id','advice_id');
    }

}