<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/17
 * Time: 11:16
 */

namespace app\common\model\project;



class P_Reform_Log extends P_Base
{

    protected $name = 'p_reform_log';

    protected $updateTime = false;

    public function annexList()
    {
        return $this->belongsToMany('app\common\model\UploadFile','p_reform_annex','file_id','reform_id');
    }

}