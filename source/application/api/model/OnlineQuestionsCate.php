<?php


namespace app\api\model;

use app\common\model\OnlineQuestionsCate as OnlineQuestionsCateModel;
use think\db\Query;

class OnlineQuestionsCate extends OnlineQuestionsCateModel
{

    /**
     * 百问百答推荐展示的分类
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(){
        return $this->where(
                [
                    'is_recom' => 1,
                    'status' => 1
                ]
            )
            ->with(
                [
                    'icon' => function(Query $query){
                        $query->field(['file_id', 'storage', 'file_name']);
                    }
                ]
            )
            ->field(['cate_id', 'icon_id', 'title'])
            ->order('sort','asc')
            ->select();
    }

}