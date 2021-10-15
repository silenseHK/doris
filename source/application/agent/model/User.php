<?php


namespace app\agent\model;

use app\agent\model\user\GoodsStock;
use app\common\model\User as UserModel;
use think\Exception;

class User extends UserModel
{

    public function getMobileHideAttr($value, $data){
        if(!isset($data['mobile']) || !$data['mobile'])return '--';
        return mobile_hide($data['mobile']);
    }

    public function getInvitationUserAttr($value, $data){
        if(!isset($data['invitation_user_id']) || !$data['invitation_user_id'])return [];
        return self::getUserInfo($data['invitation_user_id']);
    }

    /**
     * 用户168库存
     * @param $value
     * @return bool|float|mixed|string|null
     */
    public function getStockAttr($value){
        return (int)(GoodsStock::where(['user_id'=>$value, 'goods_sku_id'=>$this->main_goods_sku_id])->value('stock'));
    }

    /**
     * 获取器 -- 下级人数
     * @param $user_id
     * @return int|string
     * @throws Exception
     */
    public function getMemberNumAttr($user_id){
        return self::where(['relation'=>['LIKE', "%-{$user_id}-%"]])->count('user_id');
    }

}