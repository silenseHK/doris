<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/17
 * Time: 11:18
 */

namespace app\api\model\business;


use app\common\model\project\P_Reform_Log as Base_P_Reform_Log;
use think\Db;
use think\Exception;

class P_Reform_Log extends Base_P_Reform_Log
{

    public function add()
    {
        ##问题id
        $matter_id = input('post.matter_id/d',0);
        ##项目id
        $matter = Db::name('p_matters')->where('id',$matter_id)->field('id, project_id')->find();
        if(!$matter)
        {
            return $this->setError('问题不存在或已删除');
        }
        $project_id = $matter['project_id'];
        $data = request()->post();
        if(isset($data['annex']) && $data['annex'])
        {
            $annex = explode(',',trim($data['annex'],','));
        }
        unset($data['annex']);
        $this->startTrans();
        try{
            ##创建整改记录
            if(!$this->save(array_merge($data,compact('project_id')))){
                throw new Exception('整改记录创建失败');
            }
            ##保存附件
            if(isset($annex) && $annex)
            {
                $reform_id = $this->getLastInsID();
                $annex_data = [];
                foreach($annex as $an)
                {
                    $annex_data[] = [
                        'reform_id' => $reform_id,
                        'file_id' => $an
                    ];
                }
                if(Db::name('p_reform_annex')->insertAll($annex_data) === false)
                {
                    throw new Exception('附件添加失败');
                }
            }
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $this->setError($e->getMessage());
        }
    }

    public function lists()
    {
        ##问题id
        $matter_id = input('post.matter_id/d',0);
        ##每页条数
        $size = input('post.size/d',15);
        ##数据
        return $this
            ->where('matter_id',$matter_id)
            ->with(
                [
                    'annexList'
                ]
            )
            ->field('id, desc, department, staff, create_time')
            ->order('create_time','desc')
            ->paginate($size);
    }

}