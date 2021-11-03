<?php


namespace app\api\model\business;


use app\common\model\project\P_Project as Base_P_Project;
use think\Db;
use think\db\Query;
use think\Exception;
use app\api\model\business\P_Matter;

class P_Project extends Base_P_Project
{

    public function lists()
    {
        $size = input('post.size',15);
        ##查询条件
        $title = input('post.title/s','');
        $start_time = input('post.start_time/d',0);
        $end_time = input('post.end_time/d',0);
        $manager_id = input('post.manager_id/d',0);
        $c_id = input('post.c_id/d',0);
        if($title)
        {
            $this->whereLike('title',"%{$title}%");
        }
        if($start_time > 0  && $end_time > 0)
        {
            $this->whereBetween('create_time', [$start_time, $end_time]);
        }
        if($manager_id > 0)
        {
            $this->where('manager', $manager_id);
        }
        if($c_id > 0)
        {
            $this->where('company_id', $c_id);
        }
        return $this
                ->with(
                    [
                        'members' => function(Query $query)
                        {
                            $query->field('id, title');
                        },
                        'manager_staff' => function(Query $query)
                        {
                            $query->field('id, title');
                        },
                        'company' => function(Query $query)
                        {
                            $query->field('id, title');
                        }
                    ]
                )
                ->field('id, title, type, desc, company_id, manager, create_time, status, check_time, level, id as total_matter, id as deal_matter')->paginate($size);
    }

    public function add()
    {
        $data = request()->post();
        ##检查组组员
        $member = isset($data['member']) ? $data['member'] : '';
        if($member)
        {
            $member = explode(',',trim($member,','));
        }
        ##检查组组长
        $manager = isset($data['manager']) ? $data['manager'] : '';
        if($manager)
        {
            $manager = explode(',',trim($manager,','));
        }
        unset($data['member']);
        unset($data['manager']);
        $this->startTrans();
        try{
            $res = $this->save($data);
            if(!$res){
                throw new Exception('创建项目失败');
            }
            $project_id = $this->getLastInsID();
            if($member)
            {
                $member_data = [];
                foreach($member as $mem)
                {
                    $member_data[] = [
                        'project_id' => $project_id,
                        'staff_id' => intval($mem)
                    ];
                }
                $res = Db::name('p_project_staff')->insertAll($member_data);
                if(!$res)
                {
                    throw new Exception('检查组组员增加失败');
                }
            }
            if($manager)
            {
                $manager_data = [];
                foreach($manager as $man)
                {
                    $manager_data[] = [
                        'project_id' => $project_id,
                        'staff_id' => intval($man)
                    ];
                }
                $res = Db::name('p_project_manager')->insertAll($manager_data);
                if(!$res)
                {
                    throw new Exception('检查组组长增加失败');
                }
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $this->setError($e->getMessage());
        }
    }

    public function edit()
    {
        $data = request()->post();
        $id = $data['id'];
        unset($data['id']);
        ##检查组组员
        $member = isset($data['member']) ? $data['member'] : '';
        if($member)
        {
            $member = explode(',',trim($member,','));
        }
        ##检查组组长
        $manager = isset($data['manager']) ? $data['manager'] : '';
        if($manager)
        {
            $manager = explode(',',trim($manager,','));
        }
        unset($data['member']);
        unset($data['manager']);
        $this->startTrans();
        try{
            $res = $this->where('id', $id)->update($data);
            if($res === false){
                throw new Exception('编辑项目失败');
            }
            ##删除以前的组员
            Db::name('p_project_staff')->where('project_id',$id)->delete();
            ##删除以前的组长
            Db::name('p_project_manager')->where('project_id',$id)->delete();
            if($member)
            {
                $member_data = [];
                foreach($member as $mem)
                {
                    $member_data[] = [
                        'project_id' => $id,
                        'staff_id' => intval($mem)
                    ];
                }
                $res = Db::name('p_project_staff')->insertAll($member_data);
                if(!$res)
                {
                    throw new Exception('检查组组员编辑失败');
                }
            }
            if($manager)
            {
                $manager_data = [];
                foreach($manager as $man)
                {
                    $manager_data[] = [
                        'project_id' => $id,
                        'staff_id' => intval($man)
                    ];
                }
                $res = Db::name('p_project_manager')->insertAll($manager_data);
                if(!$res)
                {
                    throw new Exception('检查组组长编辑失败');
                }
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $this->setError($e->getMessage());
        }
    }

    public function del()
    {
        $id = input('post.id/d',0);
        if(!$this->destroy($id))
        {
            return $this->setError('操作失败');
        }
        return true;
    }

    public function detail()
    {
        $id = input('id/d');
        ##项目信息
        $data = $this
            ->where('id',$id)
            ->with(
                [
                    'members' => function(Query $query)
                    {
                        $query->field('id, title');
                    },
                    'managers' => function(Query $query)
                    {
                        $query->field('id, title');
                    },
                    'company' => function(Query $query)
                    {
                        $query->field('id, title');
                    }
                ]
            )
            ->field('id, title, type, desc, company_id, create_time, status, check_time, level')
            ->find();
        if(empty($data)){
            return $this->setError('项目不存在或已删除');
        }
        ##项目相关的问题
        $data->all = P_Matter::where('project_id',$id)->count();
        $data->reform = P_Reform_Log::where('project_id',$id)->group('matter_id')->count();
        $data->wait = $data->all - $data->reform;

        return $data;
    }

    public function getTotalMatterAttr($value)
    {
        return Db::name('p_matters')->where('project_id',$value)->whereNull('delete_time')->count();
    }

    public function getDealMatterAttr($value)
    {
        return Db::name('p_matters')->where('project_id',$value)->where('status',2)->whereNull('delete_time')->count();
    }

}