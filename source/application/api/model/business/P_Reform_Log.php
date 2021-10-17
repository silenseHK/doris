<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/17
 * Time: 11:18
 */

namespace app\api\model\business;


use app\common\model\project\P_Reform_Log as Base_P_Reform_Log;

class P_Reform_Log extends Base_P_Reform_Log
{

    public function add()
    {
        if(!$this->save(request()->post()))
        {
            return $this->setError();
        }
        return true;
    }

    public function lists()
    {
        ##问题id
        $matter_id = input('post.matter_id/d',0);
        ##每页条数
        $size = input('post.size/d',15);
        ##数据
        return $this->where('matter_id',$matter_id)->field('id, desc, department, staff, create_time')->order('create_time','desc')->paginate($size);
    }

}