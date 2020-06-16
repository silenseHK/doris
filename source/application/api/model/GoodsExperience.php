<?php


namespace app\api\model;

use app\common\model\GoodsExperience as GoodsExperienceModel;
use think\Cache;
use think\db\Query;

class GoodsExperience extends GoodsExperienceModel
{

    /**
     * 排行榜
     * @return mixed|null
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getExperienceRankList(){
        $list = Cache::get('experience_rank_list');
        if(!$list){
            ##获取体验装商品id
            $list = $this->alias('ge')
                ->join('order o','ge.order_id = o.order_id','LEFT')
                ->where(
                    [
                        'o.pay_status' => 20,
                        'o.order_status' => ['IN', [10, 30]]
                    ]
                )
                ->group('ge.first_user_id')
                ->field(['ge.first_user_id', 'count(ge.user_id) as num'])
                ->order('num','desc')
                ->with(
                    [
                        'first_user' => function(Query $query){
                            $query->field(['user_id', 'nickName', 'avatarUrl']);
                        }
                    ]
                )
                ->limit(30)
                ->select()->toArray();
            $userModel = new User();
            foreach($list as &$item){
                $item['member_num'] = $userModel->getMemberNumAttr($item['first_user_id']);
            }
            $list = sortArrByManyField($list, 'num', SORT_DESC, 'member_num', SORT_DESC);
            Cache::set('experience_rank_list',$list,600);
        }
        return $list;
    }

}