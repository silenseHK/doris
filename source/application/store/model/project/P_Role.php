<?php


namespace app\store\model\project;


use app\common\model\project\P_Role as Base_P_Role;
use think\Db;
use think\Exception;

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

    public function auth()
    {
        ##角色
        $role_id = input('post.role_id/d',0,'intval');
        ##权限
        $power = input('post.power/a',[]);
        ##处理参数
        $access = [];
        foreach($power as $pow)
        {
            foreach($pow as $p)
            {
                $access[] = [
                    'role_id' => $role_id,
                    'handle_id' => $p
                ];
            }
        }
        $this->startTrans();
        try{
            ##删除以前的数据
            if(Db::name('p_access')->where('role_id',$role_id)->delete() === false)
            {
                throw new Exception('配置权限失败');
            }
            ##新增权限
            if($access)
            {
                if(Db::name('p_access')->insertAll($access) === false)
                {
                    throw new Exception('配置权限失败.');
                }
            }
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            $this->setError($e->getMessage());
            return false;
        }
    }

    public function power($role_id)
    {
        $role = $this->where('id',$role_id)->with('access')->field('id, title')->find()->toArray();
        return array_column($role['access'],'id');
    }

}