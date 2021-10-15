<?php


namespace app\store\model;

use app\common\model\PlatformIncomeLog as PlatformIncomeLogModel;

class PlatformIncomeLog extends PlatformIncomeLogModel
{

    /**
     * 收入列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function incomeList(){
        $this->setWhere();
        $list = $this
            ->order('create_time','desc')->order('income_id','desc')
            ->paginate(15,false,['type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:getUserFillList([PAGE]);']);
        $total = $list->total();
        $page = $list->render();
        $list = $list->toArray()['data'];
        $count = $this->countIncome();
        return compact('list','page','total','count');
    }

    /**
     * 设置筛选条件
     */
    protected function setWhere(){
        $type = input('post.type', 0,'intval');
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        if($type > 0){
            $where['type'] = $type;
        }
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [strtotime($start_time), strtotime($end_time)]];
        }
        isset($where) && $this->where($where);
    }

    /**
     * 计算收入
     * @return array
     */
    protected function countIncome(){
        $this->setWhere();
        $in = $this->where(['direction'=>10])->sum('money');
        $this->setWhere();
        $out = $this->where(['direction'=>20])->sum('money');
        return compact('in','out');
    }

    public function orderInfo(){
        $order_no = input('post.order_no','','str_filter');
        $order_type = input('post.order_type','','intval');
        if($order_type == 10){
            $order_info = Order::get(['order_no'=>$order_no], ['goods', 'user']);
        }else{
            $order_info =  (new OrderDelivery)->where(['order_no'=>$order_no])->with(['goods', 'user'])->find();
        }
        return $order_info;
    }

}