<?php


namespace app\common\model\project;


use think\db\Query;
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

    public function cateLists()
    {
        return $this
            ->where('status',1)
            ->where('level',1)
            ->with(
                [
                    'children' => function(Query $query)
                    {
                        $query
                            ->with(
                                [
                                    'children' => function(Query $query)
                                    {
                                        $query->field('id, title, pid,  level, status');
                                    }
                                ]
                            )
                            ->field('id, title, pid,  level, status');
                    }
                ]
            )
            ->field('id, title, pid,  level, status')->select();
    }

    public function children()
    {
        return $this->hasMany('app\common\model\project\P_Matter_Cate','pid','id');
    }

    public function parent()
    {
        return $this->belongsTo('app\common\model\project\P_Matter_Cate','pid','id');
    }

}