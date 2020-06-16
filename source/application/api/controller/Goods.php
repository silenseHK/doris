<?php

namespace app\api\controller;

use app\api\model\Goods as GoodsModel;
use app\api\model\Cart as CartModel;
use app\api\model\GoodsSku;
use app\api\model\user\Grade;
use app\common\model\GoodsGrade;
use app\common\service\qrcode\Goods as GoodsPoster;
use think\Exception;
use app\api\model\User as UserModel;

/**
 * 商品控制器
 * Class Goods
 * @package app\api\controller
 */
class Goods extends Controller
{
    /**
     * 商品列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        // 整理请求的参数
        $param = array_merge($this->request->param(), [
            'status' => 10
        ]);
        // 获取列表数据
        $model = new GoodsModel;
        $list = $model->getList($param, $this->getUser(false));
        return $this->renderSuccess(compact('list'));
    }

    /**
     * 获取商品详情
     * @param $goods_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($goods_id)
    {
        $spec_sku_id = input('get.spec_sku_id','','str_filter');

        // 用户信息
        $user = $this->getUser(false);
        // 商品详情
        $model = new GoodsModel;
        $goods = $model->getDetails($goods_id, $this->getUser(false));
        if ($goods === false) {
            return $this->renderError($model->getError() ?: '商品信息不存在');
        }
        // 多规格商品sku信息, todo: 已废弃 v1.1.25
        $specData = $goods['spec_type'] == 20 ? $model->getManySpecData($goods['spec_rel'], $goods['sku']) : null;
        ##升级信息
        if($user && $goods['sale_type'] == 1){
            $grade_weight = Grade::getWeightByGradeId($user['grade_id']);
            $next_info = Grade::getNextGradeInfo($grade_weight);
            if($next_info){
                $next_num = ceil(($next_info['upgrade_integral'] - $user['integral']) / $goods['integral_weight']);
                $next_price = GoodsGrade::getGoodsPrice($next_info['grade_id'], $goods_id);
                $next = compact('next_num','next_price');
                $next['grade'] = $next_info['name'];
            }else{
                $next = [];
            }
            $next['cur_grade'] = Grade::getName($user['grade_id']);
        }else{
            $next = [];
        }
        $goods['next'] = $next;

        if($goods['spec_type'] == 20){
            if($spec_sku_id){
                $spec = GoodsSku::getSpec($spec_sku_id);
            }else{
                ##找到默认值
                $spec = GoodsSku::getDefaultSpec($goods_id);
            }
        }else{
            $spec = [];
        }
        $goods['spec'] = $spec;

        $sale_status = 1;
        $cur_time = time();
        if($goods['is_experience']){
            if($cur_time < $goods['start_sale_time'])$sale_status = 2;  ##待开始
            if($cur_time > $goods['end_sale_time'])$sale_status = 3;  ##已结束
        }

        $sale_info = array_merge(compact('sale_status','cur_time'), ['start_sale_time'=>$goods['start_sale_time'], 'end_sale_time'=>$goods['end_sale_time']]);

        return $this->renderSuccess([
            // 商品详情
            'detail' => $goods,
            // 购物车商品总数量
            'cart_total_num' => $user ? (new CartModel($user))->getGoodsNum() : 0,
            // 多规格商品sku信息
            'specData' => $specData,
            // 销售信息
            'sale_info' => $sale_info
        ]);
    }

    /**
     * 生成商品海报
     * @param $goods_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     * @throws \Exception
     */
    public function poster($goods_id)
    {
        // 商品详情
        $detail = GoodsModel::detail($goods_id);
        $Qrcode = new GoodsPoster($detail, $this->getUser(false));
        return $this->renderSuccess([
            'qrcode' => $Qrcode->getImage(),
        ]);
    }

    /**
     * 获取代理商品价格
     * @return array
     */
    public function getAgentGoodsPriceStock(){
        try{
            ##token 验证
            $user = $this->getUser(true);
            ##获取代理商品价格
            $res = UserModel::agentGoodsPriceStock($user);
            if(!is_array($res))throw new Exception($res);
            ##返回
            return $this->renderSuccess($res);
        }catch(Exception $e){
            return $this->renderError($e->getMessage());
        }
    }

}
