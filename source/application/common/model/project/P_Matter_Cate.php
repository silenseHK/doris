<?php


namespace app\common\model\project;


use traits\model\SoftDelete;

class P_Matter_Cate extends P_Base
{

    protected $name = 'p_matter_cate';

    use SoftDelete;

    protected $deleteTime = "delete_time";

    protected $statusArr = [
        1 => [
            'value' => 1,
            'title' => '使用'
        ],
        2 => [
            'value' => 2,
            'title' => '禁用'
        ],
    ];

    public function getStatusAttr($status)
    {
        return $this->statusArr[$status];
    }

    public function lists()
    {
        return $this->where('status',1)->field('id, title')->select();
    }

}