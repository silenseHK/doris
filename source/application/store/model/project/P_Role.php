<?php


namespace app\store\model\project;


use app\common\model\project\P_Role as Base_P_Role;

class P_Role extends Base_P_Role
{

    protected $updateTime = false;

    public function add()
    {
        return $this->save(request()->post());
    }

    public function edit()
    {
        $data = request()->post();
        $id = input('post.id',0,'intval');
        return $this->where('id',$id)->update($data);
    }

}