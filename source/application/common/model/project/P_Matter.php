<?php


namespace app\common\model\project;


use traits\model\SoftDelete;

class P_Matter extends P_Base
{

    protected $name = 'p_matters';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $status = [
        1 => [
            'value' => 1,
            'title' => '进行中',
        ],
        2 => [
            'value' => 2,
            'title' => '已完成',
        ],
        3 => [
            'value' => 3,
            'title' => '未完成',
        ],
    ];

    protected $risk = [
        1 => [
            'value' => 1,
            'title' => '一级',
        ],
        2 => [
            'value' => 2,
            'title' => '二级',
        ],
        3 => [
            'value' => 3,
            'title' => '三级',
        ],
    ];

    public function getStatusAttr($value)
    {
        return $this->status[$value];
    }

    public function getRiskAttr($value)
    {
        return $this->risk[$value];
    }

    public function getReformTimeAttr($value)
    {
        return date('Y-m-d H:i', $value);
    }

    public function contactUserInfo()
    {
        return $this->belongsTo('app\common\model\project\P_Staff','contact_user','id');
    }

    public function annexList()
    {
        return $this->belongsToMany('app\common\model\UploadFile','p_matter_annex','file_id','matter_id')->field('yoshop_upload_file.file_id, yoshop_upload_file.file_name, yoshop_upload_file.wxapp_id, yoshop_upload_file.storage');
    }

    public function departmentList()
    {
        return $this->belongsToMany('app\common\model\project\P_Department','p_matter_department','a_id','matter_id');
    }

}