<?php


namespace app\api\model;

use app\common\model\Dietitian as DietitianModel;
use think\db\Query;

class Dietitian extends DietitianModel
{

    /**
     * 营养师
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function dietitian(){
        $list = $this
            ->with([
                    'image'=>function(Query $query){
                        $query->field(['file_id', 'file_name', 'storage', 'file_url']);
                    }
                ])
            ->field(['dietitian_id', 'name', 'title', 'description', 'image_id'])
            ->order('sort','asc')
            ->select();
        return $list;
    }

}