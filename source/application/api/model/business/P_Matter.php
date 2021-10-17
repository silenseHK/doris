<?php


namespace app\api\model\business;


use app\common\model\project\P_Matter as Base_P_Matter;
use think\Db;
use think\db\Query;
use think\Exception;

class P_Matter extends Base_P_Matter
{

    protected $error = '';

    protected $code = 0;

    protected function setError($msg='操作失败', $code=1)
    {
        $this->error = $msg;
        $this->code = $code;
        return false;
    }

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
                    }
                ]
            )
            ->field('id, project_id, type, desc, risk, amount, reform_time, contact_user, status, create_time, annex, a_id')
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
        ##获取列表
        $list = $this
            ->where('project_id',$project_id)
            ->with(
                [
                    'annex_list',
                    'department_list',
                    'contact_user_info' => function(Query $query)
                    {
                        $query->field('id, title');
                    }
                ]
            )
            ->field('id, project_id, type, desc, risk, amount, reform_time, contact_user, status, create_time, annex, a_id')
            ->paginate($size);
        return $list;
    }

    public function done()
    {
        ##问题id
        $id = input('matter_id/d',0);
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
            $where[] = ['type', '=', $matter_type];
        }
        ##创建时间
        $start_time = input('post.start_time/d',0);
        $end_time = input('post.end_time/d',0);
        if($start_time && $end_time)
        {
            $where[] = ['create_time', 'between', [$start_time, $end_time]];
        }
        ##项目id
        $project_id = input('post.project_id/d',0);
        if($project_id > 0)
        {
            $where[] = ['project_id', '=', $project_id];
        }
        ##问题等级
        $risk = input('post.risk/d',0);
        if($risk > 0)
        {
            $where[] = ['risk', '=', $risk];
        }
        ##关键字
        $keywords = input('post.keywords/s','');
        if($keywords)
        {
            $where[] = ['desc', 'like', "%{$keywords}%"];
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
                    'contact_user_info' => function(Query $query)
                    {
                        $query->field('id, title');
                    }
                ]
            )
            ->field('id, project_id, type, desc, risk, amount, reform_time, contact_user, status, create_time, annex, a_id')
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
        $a_id = input('post.a_id/d','');
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
                        'a_id' => $a
                    ];
                }
                if(Db::name('p_matter_department')->insertAll($data))
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

}