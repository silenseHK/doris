<?php


namespace app\common\model;

use app\common\model\user\Grade;
use traits\model\SoftDelete;

class GoodsGrade extends BaseModel
{

    use SoftDelete;

    protected $name = 'goods_grade_info';

    protected $deleteTime = "delete_time";

    /**
     * 获取代理商品价格
     * @param $gradeId
     * @param $goodsId
     * @return mixed
     */
    public static function getGoodsPrice($gradeId, $goodsId){
        return self::where(['goods_id' => $goodsId, 'grade_id' => $gradeId])->value('price');
    }

    /**
     * 获取多级代理商品最低等级会员价格
     * @param $goodsId
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getLowestGradeGoodsPrice($goodsId){
        $lowestGrade = Grade::getLowestGrade();
        return GoodsGrade::getGoodsPrice($lowestGrade['grade_id'], $goodsId);
    }

    /**
     * 获取代理商品返利金额
     * @param $gradeId
     * @param $goodsId
     * @return mixed
     */
    public static function getGoodsRebate($gradeId, $goodsId){
        return self::where(['goods_id'=>$goodsId, 'grade_id'=>$gradeId])->value('rebate');
    }

}