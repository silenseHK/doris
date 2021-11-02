<?php


namespace app\store\model\project;


use app\common\model\project\P_Company as Base_P_Company;

class P_Company extends Base_P_Company
{

    protected $updateTime = false;
    protected $createTime = false;

    public function add()
    {
        $pidArr = input('pid/a',[]);
        $pid = $pidArr ? $pidArr[count($pidArr)-1] : 0;
        $level = 0;
        if($pid){
            $level = $this->where('id',$pid)->value('level') + 1;
        }
        return $this->save(array_merge(request()->post(),['pid'=>$pid, 'level'=>$level]));
    }

    public function edit()
    {
        $data = request()->post();
        $id = input('post.id',0,'intval');
        return $this->where('id',$id)->update($data);
    }

}