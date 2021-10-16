<?php


namespace app\store\model\project;


use app\common\model\project\P_Department as Base_P_Department;

class P_Department extends Base_P_Department
{

    protected $updateTime = false;
    protected $createTime = false;

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