<?php


namespace app\common\model\project;


use traits\model\SoftDelete;

class P_Project extends P_Base
{

    protected $name = 'p_project';

    use SoftDelete;

    protected $delete_time = 'delete_time';

    protected $status = [
        0 => [
            'value' => 0,
            'title' => '进行中'
        ],
        1 => [
            'value' => 1,
            'title' => '已完成'
        ],
        2 => [
            'value' => 2,
            'title' => '未完成'
        ],
    ];

    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d', $value);
    }

    public function getStatusAttr($value)
    {
        return $this->status[$value];
    }

    public function company()
    {
        return $this->belongsTo('app\common\model\project\P_Company','company_id','id');
    }

    public function managerStaff()
    {
        return $this->belongsTo('app\common\model\project\P_Staff','manager','id');
    }

    public function members()
    {
        return $this->belongsToMany('app\common\model\project\P_Staff','p_project_staff','staff_id','project_id')->field('yoshop_p_staff.id, yoshop_p_staff.title');
    }

    public function managers()
    {
        return $this->belongsToMany('app\common\model\project\P_Staff','p_project_manager','staff_id','project_id')->field('yoshop_p_staff.id, yoshop_p_staff.title');
    }

}