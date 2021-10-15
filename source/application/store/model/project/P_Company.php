<?php


namespace app\store\model\project;


use app\common\model\project\P_Company as Base_P_Company;

class P_Company extends Base_P_Company
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