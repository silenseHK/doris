<?php


namespace app\api\model\business;


use app\common\model\project\P_Matter as Base_P_Matter;
use think\Db;
use think\db\Query;
use think\Exception;

class P_Matter extends Base_P_Matter
{

    public function add()
    {
        ##指派部门
        $a_id = input('post.a_id/s','');
        ##附件
        $annex = input('post.annex/s','');
        $this->startTrans();
        try{
            if(!$this->save(request()->post()))
            {
                throw new Exception('问题创建失败');
            }
            $matter_id = $this->getLastInsID();
            if($a_id)
            {
                $a_arr = explode(',',trim($a_id,','));
                $a_data = [];
                foreach($a_arr as $a)
                {
                    $a_data[] = [
                        'matter_id' => $matter_id,
                        'a_id' => $a
                    ];
                }
                $res = Db::name('p_matter_department')->insertAll($a_data);
                if(!$res)throw new Exception('指派部门失败');
            }
            if($annex)
            {
                $a_arr = explode(',',trim($annex,','));
                $a_data = [];
                foreach($a_arr as $a)
                {
                    $a_data[] = [
                        'matter_id' => $matter_id,
                        'file_id' => $a
                    ];
                }
                $res = Db::name('p_matter_annex')->insertAll($a_data);
                if(!$res)throw new Exception('绑定附件失败');
            }
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $this->setError($e->getMessage());
        }
    }

    public function edit()
    {
        ##问题id
        $id = input('post.id/d',0);
        ##指派部门
        $a_id = input('post.a_id/s','');
        ##附件
        $annex = input('post.annex/s','');
        $this->startTrans();
        try{
            ##修改信息
            if($this->where('id',$id)->update(request()->post()) === false)
            {
                throw new Exception('操作失败');
            }
            ##修改附件
            ###删除以前的附件
            Db::name('p_matter_annex')->where('matter_id',$id)->delete();
            ###删除以前的指派
            Db::name('p_matter_department')->where('matter_id',$id)->delete();

            if($a_id)
            {
                $a_arr = explode(',',trim($a_id,','));
                $a_data = [];
                foreach($a_arr as $a)
                {
                    $a_data[] = [
                        'matter_id' => $id,
                        'a_id' => $a
                    ];
                }
                $res = Db::name('p_matter_department')->insertAll($a_data);
                if(!$res)throw new Exception('指派部门失败');
            }
            if($annex)
            {
                $a_arr = explode(',',trim($annex,','));
                $a_data = [];
                foreach($a_arr as $a)
                {
                    $a_data[] = [
                        'matter_id' => $id,
                        'file_id' => $a
                    ];
                }
                $res = Db::name('p_matter_annex')->insertAll($a_data);
                if(!$res)throw new Exception('绑定附件失败');
            }
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $this->setError($e->getMessage());
        }
    }

    public function detail()
    {
        $id = input('post.id/d',0);
        ##信息
        $info = $this
            ->where('id',$id)
            ->with(
                [
                    'annex_list',
                    'department_list',
                    'contact_user_info' => function(Query $query)
                    {
                        $query->field('id, title');
                    },
                    'cate' => function(Query $query)
                    {
                        $query->field('id, title');
                    }
                ]
            )
            ->field('id, title, project_id, type, desc, risk, amount, reform_time, contact_user, status, create_time, annex, a_id')
            ->find();
        if(!$info)
        {
            return $this->setError('问题数据不存在或已删除');
        }
        return $info;
    }

    public function projectMatters()
    {
        ##项目id
        $project_id = input('post.id/d',0);
        ##每页条数
        $size = input('post.size/d',15);
        ##获取类型
        $type = input('post.type/d',0);
        $model = $this->where('project_id',$project_id);
        if($type != 0){
            $matter_ids = P_Reform_Log::where('project_id', $project_id)->group('matter_id')->column('matter_id');
            if($type == 1){//已反馈
                $model = $model->whereIn('id',$matter_ids);
            }else{//未反馈
                $model = $model->whereNotIn('id',$matter_ids);
            }
        }
        ##获取列表
        $list = $model
            ->with(
                [
                    'annex_list',
                    'department_list',
                    'contact_user_info' => function(Query $query)
                    {
                        $query->field('id, title');
                    },
                    'cate' => function(Query $query)
                    {
                        $query->field('id, title');
                    }
                ]
            )
            ->field('id, title, project_id, type, desc, risk, amount, reform_time, contact_user, status, create_time, annex, a_id')
            ->paginate($size);
        return $list;
    }

    public function done()
    {
        ##问题id
        $id = input('id/d',0);
        ##完成整改
        if(!$this->where('id', $id)->setField('status',2))
        {
            return $this->setError('操作失败');
        }
        return true;
    }

    /**
     * 问题库
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        $where = [];
        ##查询条件
        ##问题类型
        $matter_type = input('post.matter_type/d',0);
        if($matter_type > 0)
        {
            $where['type'] = ['=', $matter_type];
        }
        ##创建时间
        $start_time = input('post.start_time/d',0);
        $end_time = input('post.end_time/d',0);
        if($start_time && $end_time)
        {
            $where['create_time'] = ['between', [$start_time, $end_time]];
        }
        ##项目id
        $project_id = input('post.project_id/d',0);
        if($project_id > 0)
        {
            $where['project_id'] = ['=', $project_id];
        }
        ##问题等级
        $risk = input('post.risk/d',0);
        if($risk > 0)
        {
            $where['risk'] = ['=', $risk];
        }
        ##关键字
        $keywords = input('post.keywords/s','');
        if($keywords)
        {
            $where['title'] = ['like', "%{$keywords}%"];
        }
        ##每页条数
        $size = input('post.size/d',0);
        ##查询列表
        return $this
            ->where($where)
            ->with(
                [
                    'annex_list',
                    'department_list',
                    'cate' => function(Query $query)
                    {
                        $query->field('id, title');
                    },
                    'contact_user_info' => function(Query $query)
                    {
                        $query->field('id, title');
                    }
                ]
            )
            ->field('id, title, project_id, type, desc, risk, amount, reform_time, contact_user, status, create_time, annex, a_id')
            ->paginate($size);
    }

    /**
     * 删除问题
     * @return bool
     */
    public function del()
    {
        if(!$this->destroy(input('post.id/d',0)))
        {
            return $this->setError('操作失败s');
        }
        return true;
    }

    /**
     * 指派
     * @return bool
     * @throws \think\exception\PDOException
     */
    public function assign()
    {
        ##问题id
        $matter_id = input('post.matter_id/d',0);
        ##部门id
        $a_id = input('post.a_id/s','');
        if($a_id)
        {
            $a_arr = explode(',',trim($a_id,','));
        }
        $this->startTrans();
        try{
            ##删除以前的指派信息
            if(Db::name('p_matter_department')->where('matter_id', $matter_id)->delete() === false)
            {
                throw new Exception('操作失败');
            }
            ##增加新的指派
            if(isset($a_arr) && $a_arr)
            {
                $data = [];
                foreach($a_arr as $a)
                {
                    $data[] = [
                        'matter_id' => $matter_id,
                        'a_id' => $a,
                        'create_time' => time()
                    ];
                }
                if(!Db::name('p_matter_department')->insertAll($data))
                {
                    throw new Exception('操作失败.');
                }
            }
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $this->setError($e->getMessage());
        }
    }

    /**
     * 个人中心--问题列表
     * @param $user_id
     * @return bool|\think\Paginator
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function assignMatters($user_id)
    {
        ## 更新问题超时
        $this->where('status',1)->where('reform_time','<',time())->setField('status',3);

        ##用户所在部门
        $user = Db::name('p_staff')->where('id', $user_id)->field('id, a_id, is_expert')->find();
        if(!$user['is_expert'] || !$user['a_id'])
        {
            return $this->setError('专责员工才能查看部门的指派问题');
        }
        if(!$user['a_id'])
        {
            return $this->setError('请联系管理员为您分配部门');
        }
        $where = [];
        $where['ma.a_id'] = ['=', $user['a_id']];
        $status = input('post.status/d',0);  //1未处理 2已处理
        if($status > 0)
        {
            $where['m.status'] = ['=', $status];
        }
        $size = input('post.size/d',15);
        $list = Db::name('p_matter_department')->alias('ma')
            ->join('p_matters m','m.id = ma.matter_id','left')
            ->join('p_project p','m.project_id = p.id','left')
            ->where($where)
            ->field('m.id, m.title, m.project_id, m.desc, m.risk, m.reform_time, m.create_time, m.project_id, m.complete_time, ma.create_time assign_time, p.title as project_title, m.status')
            ->paginate($size)->toArray();
        foreach($list['data'] as $ke => $da)
        {
            $list['data'][$ke]['create_time'] = date('Y-m-d H:i', $da['create_time']);
            $list['data'][$ke]['assign_time'] = date('Y-m-d H:i', $da['assign_time']);
            $list['data'][$ke]['reform_time'] = date('Y-m-d H:i', $da['reform_time']);
            $list['data'][$ke]['risk'] = $this->getRisk($da['risk']);
            $list['data'][$ke]['complete_time'] = ceil(($da['reform_time'] - $da['create_time']) / (60 * 60 * 24));
        }
        return $list;
    }

    /**
     * 员工收藏问题列表
     * @param $user_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function collectMatters($user_id)
    {
        $where = [];
        $where['mc.staff_id'] = ['=', $user_id];
        $size = input('post.size/d',15);
        $list = Db::name('p_matter_collect')->alias('mc')
            ->join('p_matters m','m.id = mc.matter_id','left')
            ->join('p_project p','m.project_id = p.id','left')
            ->where($where)
            ->field('m.id, m.title, m.project_id, m.desc, m.risk, m.create_time, mc.create_time assign_time, p.title as project_title')
            ->paginate($size)->toArray();
        foreach($list['data'] as $ke => $da)
        {
            $list['data'][$ke]['create_time'] = date('Y-m-d H:i', $da['create_time']);
            $list['data'][$ke]['assign_time'] = date('Y-m-d H:i', $da['assign_time']);
            $list['data'][$ke]['risk'] = $this->getRisk($da['risk']);
        }
        return $list;
    }

}