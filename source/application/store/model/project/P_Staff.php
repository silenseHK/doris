<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 9:54
 */

namespace app\store\model\project;


use think\Model;
use traits\model\SoftDelete;
use app\common\model\project\P_Staff as Base_P_Staff;

class P_Staff extends Base_P_Staff
{

    protected $updateTime = false;

    protected $insert = ['pwd'];

    public function setPwdAttr($value)
    {
        return md5($value);
    }

    public function add()
    {
        return $this->save(request()->post());
    }

    public function edit()
    {
        $data = request()->post();
        $id = input('post.id',0,'intval');
        $pwd = input('post.pwd','','trim');
        if($pwd){
            $data['pwd'] = md5($pwd);
        }else{
            unset($data['pwd']);
        }
        return $this->where('id',$id)->update($data);
    }

}