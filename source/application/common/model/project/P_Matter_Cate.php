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

    public function cateLists($strict=false)
    {
        $model = $this->where('level',1);
        if($strict){
            $model = $model->where('status',1);
        }
        return $model
            ->with(
                [
                    'children' => function(Query $query) use ($strict)
                    {
                        if($strict){
                            $query
                                ->where('status',1)
                                ->with(
                                    [
                                        'children' => function(Query $query) use ($strict)
                                        {
                                            if($strict){
                                                $query->where('status',1)->field('id, title, pid,  level, status');
                                            }else{
                                                $query->field('id, title, pid,  level, status');
                                            }

                                        }
                                    ]
                                )
                                ->field('id, title, pid,  level, status');
                        }else{
                            $query
                                ->with(
                                    [
                                        'children' => function(Query $query) use ($strict)
                                        {
                                            if($strict){
                                                $query->where('status',1)->field('id, title, pid,  level, status');
                                            }else{
                                                $query->field('id, title, pid,  level, status');
                                            }

                                        }
                                    ]
                                )
                                ->field('id, title, pid,  level, status');
                        }
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