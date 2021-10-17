<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/17
 * Time: 11:37
 */

namespace app\api\model\business;


use app\common\model\project\P_Advice as Base_P_Advice;
use think\Db;
use think\Exception;

class P_Advice extends Base_P_Advice
{

    public function add()
    {
        ##数据
        $data = request()->post();
        if(isset($data['annex']) && $data['annex'])
        {
            $annex = explode(',',trim($data['annex'],','));
        }
        unset($data['annex']);
        ##添加数据
        $this->startTrans();
        try{
            ##添加意见
            if(!$this->save($data))
            {
                throw new Exception('添加意见失败');
            }
            ##添加附件
            if($annex)
            {
                $advice_id = $this->getLastInsID();
                $annex_data = [];
                foreach($annex as $an)
                {
                    $annex_data[] = [
                        'advice_id' => $advice_id,
                        'file_id' => $an
                    ];
                }
                if(!Db::name('p_advice_annex')->insertAll($annex_data))
                {
                    throw new Exception('附件保存失败');
                }
            }
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->rollback();
            return $this->setError($e->getMessage());
        }
    }

    public function adviceList()
    {
        $where = [];
        ##查询条件
        ##问题类型
        $matter_type = input('post.matter_type/d',0);
        if($matter_type > 0)
        {
            $where[] = ['m.type', '=', $matter_type];
        }
        ##创建时间
        $start_time = input('post.start_time/d',0);
        $end_time = input('post.end_time/d',0);
        if($start_time && $end_time)
        {
            $where[] = ['a.create_time', 'between', [$start_time, $end_time]];
        }
        ##关键字
        $keywords = input('post.keywords/s','');
        if($keywords)
        {
            $where[] = ['a.advice', 'like', "%{$keywords}%"];
        }
        ##问题等级
        $risk = input('post.risk/d',0);
        if($risk > 0)
        {
            $where[] = ['m.risk', '=', $risk];
        }
        ##每页条数
        $size = input('post.size/d',0);
        ##查询列表
        return $this->alias('a')
            ->join('p_matters m','m.id = a.matter_id','left')
            ->where($where)
            ->field('m.type, m.risk, m.desc, a.desc as advice_desc, a.advice, a.create_time, a.id')
            ->order('a.create_time','desc')
            ->paginate($size);
    }

}