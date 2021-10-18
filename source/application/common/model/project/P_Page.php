<?php


namespace app\common\model\project;


use think\db\Query;

class P_Page extends P_Base
{

    protected $name = 'p_page';

    public function auths()
    {
        return $this->field('id, title, alias')->with(
            [
                'needle' => function(Query $query)
                {
                    $query->field('id, title, page_id');
                }
            ]
        )->select();
    }

    public function needle()
    {
        return $this->hasMany('app\common\model\project\P_Handle','page_id','id');
    }

}