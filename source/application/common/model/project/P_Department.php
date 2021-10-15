<?php


namespace app\common\model\project;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class P_Department extends BaseModel
{

    protected $table = 'yoshop_p_department';

    use SoftDelete;

    protected $delete_time = 'delete_time';

    public function listsByCompany($c_id)
    {
        return $this->where('c_id',$c_id)->select();
    }

    /**
     * 根据分公司对用户部门进行分组,形成二维数组
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listsGroupByCompany()
    {
        $list = $this->field('id, title, c_id')->select();
        $group = [];
        foreach($list as $li)
        {
            if(!isset($group[$li['c_id']]))$group[$li['c_id']] = [];
            $group[$li['c_id']][] = $li;
        }
        return $group;
    }

    public function company()
    {
        return $this->belongsTo('app\common\model\project\P_Company','c_id','id');
    }

}