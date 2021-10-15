<?php


namespace app\api\model;

use app\common\model\GoodsGrade;
use app\common\model\GoodsSuggestion as GoodsSuggestionModel;
use think\db\Query;
use app\api\model\User as UserModel;

class GoodsSuggestion extends GoodsSuggestionModel
{

    /**
     * 推荐套餐
     * @param $user
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function suggestionList($user){
        $list = $this
            ->where(['status'=>1])
            ->with(
                [
                    'spec' => function(Query $query){
                        $query->field(['goods_sku_id', 'spec_sku_id', 'image_id', 'goods_price'])->with(['image'=>function(Query $query){
                            $query->field(['file_id', 'file_name', 'storage']);
                        }]);
                    },
                    'goods' => function(Query $query){
                        $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual']);
                    },
                    'image' => function(Query $query){
                        $query->field(['file_id', 'storage', 'file_name', 'file_url']);
                    }
                ]
            )
            ->field(['suggestion_id', 'title', 'goods_id', 'goods_sku_id', 'num', 'image_id', 'description', 'min_cycle', 'max_cycle', 'min_bmi', 'max_bmi'])
            ->order('sort','asc')
            ->select();
        foreach($list as $key => $val){
            if($user)
                $list[$key]['goods_price'] = UserModel::getAgentGoodsPrice($user['user_id'], $val['goods_id'], $val['num']);
            else
                $list[$key]['goods_price'] = $val['spec']['goods_price'];
        }

        return compact('list');
    }

}