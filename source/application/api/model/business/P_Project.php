<?php


namespace app\api\model\business;


use app\common\model\project\P_Project as Base_P_Project;
use think\Db;
use think\db\Query;
use think\Exception;

class P_Project extends Base_P_Project
{

    protected $error = '';

    protected $code = 0;

    protected function setError($msg='操作失败', $code=1)
    {
        $this->error = $msg;
        $this->code = $code;
        return false;
    }

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
        unset($data['member']);
        $this->startTrans();
        try{
            $res = $this->save($data);
            if(!$res){
                throw new Exception('创建项目失败');
            }
            if($member)
            {
                $project_id = $this->getLastInsID();
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
            Db::commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $this->setError($e->getMessage());
        }

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
            ->field('id, title, type, desc, company_id, manager, create_time, status, check_time, level')
            ->find();
        if(!$data){
            $this->setError('项目不存在或已删除');
        }
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