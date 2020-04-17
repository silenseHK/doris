<?php


namespace app\store\model;

use app\common\enum\user\StockChangeScene;
Use app\common\model\UserGoodsStockLog as UserGoodsStockLogModel;
use think\db\Query;

class UserGoodsStockLog extends UserGoodsStockLogModel
{

    /**
     * 写入地理商品库存变更记录
     * @param $options
     * @return false|int
     */
    public static function addLog($options){
        ##处理库存改变数量
        $options['change_num'] = abs($options['diff_stock']);
        ##处理库存改变方向
        $options['change_direction'] = $options['diff_stock'] > 0 ? self::$CHANGE_DIRECTION['UP'] : self::$CHANGE_DIRECTION['DOWN'];
        ##处理库存改变类型
        $options['change_type'] = isset($options['change_type']) ? self::$CHANGE_TYPE[$options['change_type']] : self::$CHANGE_TYPE['USER'];
        return (new self)->allowField(true)->save($options);
    }

    /**
     * 回填integral_log_id
     * @param $data
     * @return false|int
     */
    public static function editIntegralLogId($data){
        return (new self)->isUpdate()->save($data);
    }

    /**
     * 获取库存变动记录
     * @param $params
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getLog($params){
        $this->setWhere($params);
        $list = $this->with([
            'opposite_user' => function(Query $query){
                $query->field(['user_id', 'nickName', 'grade_id'])->with(['grade'=>function(Query $query){
                    $query->field(['name', 'grade_id']);
                }]);
            },
            'goods' => function(Query $query){
                $query->field(['goods_id', 'goods_name', 'sales_initial', 'sales_actual'])->with(
                    ['image.file']
                );
            }
        ])->order('create_time','desc')->paginate(10,false,['query' => \request()->request()]);
        return $list;
    }

    /**
     * 获取器 --格式化库存变化数量
     * @param $value
     * @param $data
     * @return int
     */
    public function getChangeNumAttr($value, $data){
        return $data['change_direction'] == 20 ? -$value : $value;
    }

    /**
     * 获取器 --格式化库存变化场景
     * @param $value
     * @return mixed
     */
    public function getChangeTypeAttr($value){
        return StockChangeScene::data()[$value];
    }

    /**
     * 获取器 -- 格式化收货人
     * @param $value
     * @param $data
     * @return array
     */
    public function getOppositeUserAttr($value, $data){
        if($data['change_type'] == StockChangeScene::BUY && !$data['opposite_user_id'])return ['nickName'=>'公司','grade'=>['name'=>'平台']];
        return $value;
    }

    /**
     * 设置where查询条件
     * @param $params
     */
    public function setWhere($params){
        $where = [
            'user_id' => intval($params['user_id']),
            'goods_sku_id' => intval($params['goods_sku_id']),
            'goods_id' => intval($params['goods_id'])
        ];
        if(isset($params['scene']) && $params['scene'] != -1){
            $where['change_type'] = $params['scene'];
        }
        if(isset($params['start_time']) && $params['start_time'] && isset($params['end_time']) && $params['end_time']){
            $start_time = strtotime($params['start_time'] . " 00:00:01");
            $end_time = strtotime($params['end_time'] . " 23:59:59");
            $where['create_time'] = ['between', [$start_time, $end_time]];
        }
        if(isset($params['start_time']) && $params['start_time'] && !isset($end_time)){
            $start_time = strtotime($params['start_time'] . " 00:00:01");
            $where['create_time'] = ['EGT', $start_time];
        }
        if(isset($params['end_time']) && $params['end_time'] && !isset($start_time)){
            $end_time = strtotime($params['end_time'] . " 23:59:59");
            $where['create_time'] = ['ELT', $end_time];
        }
        $this->where($where);
    }

}