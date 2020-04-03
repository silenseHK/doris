<?php


namespace app\store\model;

use app\common\model\GoodsGrade as GoodsGradeModel;
use app\store\model\user\Grade;
use think\Exception;
use traits\model\SoftDelete;

class GoodsGrade extends GoodsGradeModel
{

    use SoftDelete;

    protected $deleteTime = "delete_time";

    protected $autoWriteTimestamp = false;

    protected $insert = ['create_time'];

    /**
     * 修改器 :设置创建时间
     * @return int
     */
    public function setCreateTimeAttr(){
        return time();
    }

    /**
     * 添加商品会员信息[价格、返利]
     * @param $grades
     * @param $goods_id
     * @param $is_update
     * @return bool|string
     * @throws \Exception
     */
    public function addGoodsGradeInfo($grades, $goods_id, $is_update=false)
    {
        try{
            $data = [];
            foreach($grades as $k => $grade){
                $data[] = [
                    'grade_id' => $k,
                    'goods_id' => $goods_id,
                    'rebate' => 0,
                    'price' => floatval($grade['price'])
                ];
            }
            if($is_update){
                $add_data = [];
                $delete_ids = [];
                $rules = $this->getGoodsGradeInfo($goods_id);
                foreach($rules as $rule){
                    $count = 0;
                    foreach($data as $da){
                        if($rule['grade_id'] != $da['grade_id'] || $rule['price'] != $da['price'] || $rule['rebate'] != $da['rebate']){
                            $count ++;
                        }
                    }
                    if($count >= count($data))$delete_ids[] = $rule['id'];
                }
                foreach($data as $da){
                    $count = 0;
                    foreach($rules as $rule){
                        if($rule['grade_id'] != $da['grade_id'] || $rule['price'] != $da['price'] || $rule['rebate'] != $da['rebate']){
                            $count ++;
                        }
                    }
                    if($count >= count($rules))$add_data[] = $da;
                }
                $data = $add_data;
                ##软删除无用的对应关系
                $this->where(['id'=>['in', $delete_ids]])->setField('delete_time', time());
            }
            if(!empty($data)){
                $res = $this->saveAll($data);
                if($res === false)throw new Exception('会员价格、会员返利设置失败');
            }
            return true;
        }catch(Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * 获取商品对应会员等级价格及返利信息
     * @param $goods_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGoodsGradeInfo($goods_id){
        return $this->where(['goods_id'=>$goods_id])->field(['id', 'grade_id', 'rebate', 'price'])->select()->toArray();
    }

    /**
     * 获取商品会员价格和返利
     * @param $goods_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getGoodsGradeList($goods_id){
        return (new self())->alias('gg')
            ->join('user_grade ug','gg.grade_id = ug.grade_id','LEFT')
            ->where([
                'gg.goods_id' => $goods_id,
                'ug.is_delete' => 0
            ])
            ->field(['gg.id', 'gg.price', 'gg.rebate', 'ug.name'])
            ->order('ug.weight','asc')
            ->select()
            ->toArray();
    }

    /**
     * 获取代理商品的游客价
     * @param $goods_id
     * @return mixed
     */
    public static function getTouristPrice($goods_id){
        ##获取游客等级id
        $grade_id = Grade::where(['is_delete'=>0, 'status'=>1])->order('weight','asc')->value('grade_id');
        return self::where(compact('goods_id','grade_id'))->value('price');
    }

}