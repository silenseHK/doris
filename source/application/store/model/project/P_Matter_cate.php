<?php


namespace app\store\model\project;

use app\common\model\project\P_Matter_Cate as Base_P_Matter_Cate;
use think\db\Query;

class P_Matter_cate extends Base_P_Matter_Cate
{

    public function add()
    {
        return $this->save(request()->post());
    }

    public function edit()
    {
        $data = request()->post();
        $id = $data['id'];
        unset($data['id']);
        return $this->where('id',$id)->update($data);
    }

    public function info()
    {
        return $this->where('id',input('id/d',0))
        ->with(
            [
                'parent' => function(Query $query)
                {
                    $query
                        ->with(
                            [
                                'parent' => function(Query $query)
                                {
                                    $query->field('id, title, status, level, pid');
                                }
                            ]
                        )
                        ->field('id, title, status, level, pid');
                }
            ]
        )
        ->field('id, title, status, level, pid')
        ->find();
    }

}