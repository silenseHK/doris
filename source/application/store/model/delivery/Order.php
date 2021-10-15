<?php


namespace app\store\model\delivery;

use app\common\model\delivery\Order as OrderModel;
use app\common\service\kuaidi100\Energy;
use app\store\model\Express;
use app\store\model\OrderDelivery;
use think\Db;
use think\Exception;
use app\store\model\Order as BaseOrder;

class Order extends OrderModel
{

    /**
     * 物流列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function expressList(){
        $this->setWhere();
        $list = $this
            ->with(['image', 'express'])
            ->order('create_time','desc')
            ->paginate(15,false,[
            'type' => 'Bootstrap',
            'var_page' => 'page',
            'path' => 'javascript:ajax_go([PAGE]);'
        ]);
        $page = $list->render();
        $total = $list->total();
        $list = $list->isEmpty()?[]:$list->toArray()['data'];
        return compact('page','total','list');
    }

    /**
     * 设置查询条件
     */
    public function setWhere(){
        $start_time = input('start_time','','str_filter');
        $end_time = input('end_time','','str_filter');
        $order_no = input('order_no','','search_filter');
        $status = input('status',0,'intval');
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [strtotime($start_time), strtotime($end_time)]];
        }
        if($order_no){
            $where['order_no|express_no'] = ['LIKE', "%{$order_no}%"];
        }
        if($status){
            $where['delivery_status'] = $status;
        }
        isset($where) && $this->where($where);
    }

    /**
     * 生成快递面单
     * @param int $id
     * @param int $express_id
     * @param string $remark
     * @return array|bool|float|int|mixed|object|\stdClass
     * @throws \think\exception\DbException
     */
    public function expressImage($id=0, $express_id=0, $remark=''){
        if(!$id)
            $id = input('post.id',0,'intval');
        if(!$express_id)
            $express_id = input('post.express_id',0,'intval');
        if(!$express_id){
            $this->error = '请选择物流';
            return false;
        }
        $info = self::get($id, ['express']);
        if(!$info){
            $this->error = '订单数据不存在';
            return false;
        }
        if($info['delivery_status']['value'] != 10){
            $this->error = '订单状态不支持此操作';
            return false;
        }
        if($info['express_html']){
            return $info['express_html'];
        }
        $express = Express::get($express_id);
        if(!$remark)
            $remark = input('post.remark','','str_filter');
        ##生成express_html
        $data = $this->curlExpressHtml($info, $remark, $express);
        if(!$data){
            return false;
        }
        ##更新订单数据
        $update = [
            'express_id' => $express_id,
            'express_no' => $data['kuaidinum'],
            'delivery_status' => 20,
            'wait_delivery_time' => time(),
            'express_html' => htmlspecialchars($data['template']),
        ];
        $request_data = [
            'delivery_order_id' => $id,
            'kuaidicom' => $express['express_code'],
            'kuaidinum' => $data['kuaidinum'],
            'express_html' => htmlspecialchars($data['template'])
        ];
        Db::startTrans();
        try{
            ##更新数据
            $res = $this->update($update, ['id'=>$id]);
            if($res === false)throw new Exception('物流信息更新失败');
            ##增加请求记录
            $requestModel = new OrderRequest();
            $res = $requestModel->isUpdate(false)->save($request_data);
            if($res === false)throw new Exception('请求记录插入失败');
            Db::commit();
            return $data['template'];
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 批量生成面单
     * @return array|bool|float|int|mixed|object|\stdClass
     * @throws \think\exception\DbException
     */
    public function batchExpressImage(){
        $ids = input('post.ids/a', []);
        if(empty($ids)){
            $this->error = '请选择需要生成面单的订单';
            return false;
        }
        $express_id = input('post.express_id',0,'intval');
        if(!$express_id){
            $this->error = '请选择物流';
            return false;
        }
        $remark = input('post.remark','','str_filter');
        foreach($ids as $id){
            $result = $this->expressImage($id, $express_id, $remark);
            if(!$result){
                return $result;
            }
        }
        return true;
    }

    /**
     * 请求面单
     * @param $info
     * @param $remark
     * @param $express
     * @return bool|mixed
     */
    protected function curlExpressHtml($info, $remark, $express){
        $order = [
            'receive_user' => $info['receive_user'],
            'receive_mobile' => $info['receive_mobile'],
            'receive_address' => $info['receive_address'],
            'send_user' => '168太空素食仓库',
            'send_mobile' => '15983587793',
            'send_address' => '四川省成都市武侯区天府金融大厦A座1701',
            'express_code' => $express['express_code'],
            'goods_full_name' => $info['goods_name'] . $info['goods_attr'],
            'goods_num' => $info['goods_num'],
            'remark' => $remark,
        ];
        $expressEnergy = new Energy('getElecOrder', $order);
        $res = $expressEnergy->task();
        if(!$res){
            $this->error = $expressEnergy->getError();
            return false;
        }
        return $res;
    }

    /**
     * 电子面单云打印
     * @return bool
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function printOrder(){
        $id = input('post.id',0,'intval');
        $info = self::get($id, ['express']);

        if($info['print_num'] > 0){ ##重复打印
            if($info['print_num'] >= 10){
                $this->error = '打印次数超过限制,最多打印10次';
                return false;
            }
            $data = $this->curlPrintOld($info['task_id']);
            if(!$data){
                return false;
            }
            ##增加请求次数
            $this->where(['id'=>$id])->setInc('print_num',1);
            return true;
        }else{ ##首次打印
            $express_id = input('post.express_id',0,'intval');
            if(!$express_id){
                $this->error = '请选择物流';
                return false;
            }
            if(!$info){
                $this->error = '订单数据不存在';
                return false;
            }
            if($info['delivery_status']['value'] != 10){
                $this->error = '订单状态不支持此操作';
                return false;
            }
            $express = Express::get($express_id);
            $remark = input('post.remark','','str_filter');
            $data = $this->curlPrintOrder($info, $express, $remark);
            if(!$data){
                return false;
            }
            ##更新订单数据
            $update = [
                'express_id' => $express_id,
                'express_no' => $data['kuaidinum'],
                'delivery_status' => 20,
                'wait_delivery_time' => time(),
//                'express_html' => htmlspecialchars($data['template']),
                'task_id' => $data['taskId'],
                'print_num' => 1
            ];
            $request_data = [
                'delivery_order_id' => $id,
                'kuaidicom' => $express['express_code'],
                'kuaidinum' => $data['kuaidinum'],
//                'express_html' => htmlspecialchars($data['template'])
                'task_id' => $data['taskId']
            ];
            Db::startTrans();
            try{
                ##更新数据
                $res = $this->update($update, ['id'=>$id]);
                if($res === false)throw new Exception('物流信息更新失败');
                ##增加请求记录
                $requestModel = new OrderRequest();
                $res = $requestModel->isUpdate(false)->save($request_data);
                if($res === false)throw new Exception('请求记录插入失败');
                Db::commit();
                return true;
            }catch(Exception $e){
                Db::rollback();
                $this->error = $e->getMessage();
                return false;
            }
        }
    }

    protected function curlPrintOrder($info, $express, $remark){
        $order = [
            'order_no' => $info['order_no'],
            'receive_user' => $info['receive_user'],
            'receive_mobile' => $info['receive_mobile'],
            'receive_address' => $info['receive_address'],
            'send_user' => '168太空素食仓库',
            'send_mobile' => '15983587793',
            'send_address' => '四川省成都市武侯区天府金融大厦A座1701',
            'express_code' => $express['express_code'],
            'goods_full_name' => $info['goods_name'] . $info['goods_attr'],
            'goods_num' => $info['goods_num'],
            'remark' => $remark,
        ];
        $expressEnergy = new Energy('eOrder', $order);
        $res = $expressEnergy->task();
        if(!$res){
            $this->error = $expressEnergy->getError();
            return false;
        }
        return $res;
    }

    /**
     * 调起重复打印接口
     * @param $task_id
     * @return bool|mixed
     */
    protected function curlPrintOld($task_id){
        $expressEnergy = new Energy('printOld', ['task_id'=>$task_id]);
        $res = $expressEnergy->task();
        if(!$res){
            $this->error = $expressEnergy->getError();
            return false;
        }
        return $res;
    }

    /**
     * 单个确认发货
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function confirmDelivery(){
        $id = input('post.id',0,'intval');
        $remark = input('post.remark','','str_filter');
        $info = $this->where(compact('id'))->field(['id', 'order_no', 'order_id', 'order_type', 'express_id', 'express_no', 'delivery_status'])->select();
        if($info->isEmpty()){
            $this->error = '订单不存在';
            return false;
        }
        if($info[0]['delivery_status']['value'] != 20){
            $this->error = '订单不支持此操作';
            return false;
        }
        return $this->doConfirmDelivery($info->toArray(), $remark);
    }

    /**
     * 批量确认发货
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function batchConfirmDelivery(){
        $ids = input('post.ids/a',[]);
        if(empty($ids) || !$ids){
            $this->error = '请选择订单';
            return false;
        }
        $remark = input('post.remark','','str_filter');
        $list = $this->where(['id'=>['IN', $ids],'delivery_status'=>20])->field(['id', 'order_no', 'order_id', 'order_type', 'express_id', 'express_no'])->select();
        if($list->isEmpty()){
            $this->error = '无可操作订单';
            return false;
        }
        return $this->doConfirmDelivery($list->toArray(), $remark);
    }

    /**
     * 确认发货
     * @param $list
     * @param $remark
     * @return bool
     */
    protected function doConfirmDelivery($list, $remark){
        $ids = array_column($list, 'id');
        Db::startTrans();
        try{
            $time = time();
            ##更新物流状态
            $res = $this->update(['delivery_status'=>30, 'delivery_time'=>$time, 'remark'=>$remark], ['id'=>['IN', $ids]]);
            if($res === false)throw new Exception('物流状态更新失败');
            ##更新订单信息
            $orderModel = new BaseOrder();
            $orderDeliveryModel = new OrderDelivery();
            foreach($list as $item){
                $update = [
                    'express_id' => $item['express_id'],
                    'express_no' => $item['express_no'],
                ];
                if($item['order_type'] == 1){
                    $update['delivery_status'] = 20;
                    $update['delivery_time'] = $time;
                    $res = $orderModel->update($update, ['order_id'=>$item['order_id']]);
                }else{
                    $update['deliver_status'] = 20;
                    $update['deliver_time'] = $time;
                    $res = $orderDeliveryModel->update($update, ['deliver_id'=>$item['order_id']]);
                }
                if($res === false)throw new Exception("订单{$item['order_no']}更新失败");
            }
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 统计总数
     * @return array
     * @throws Exception
     */
    public function statisticsTotal(){
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        ##待发货
        $wait_send = $this->statisticsData(10, $start_time, $end_time);
        ##备货中
        $prepare = $this->statisticsData(20, $start_time, $end_time);
        ##已发货
        $did_send = $this->statisticsData(30, $start_time, $end_time);
        ##已取消
        $cancel = $this->statisticsData(40, $start_time, $end_time);
        return compact('wait_send','prepare','did_send','cancel');
    }

    /**
     * 统计不同状态下的总数
     * @param $type
     * @param $start_time
     * @param $end_time
     * @return int|string
     * @throws Exception
     */
    protected function statisticsData($type, $start_time, $end_time){
        $where['delivery_status'] = $type;
        if($start_time && $end_time){
            $where['create_time'] = ['BETWEEN', [strtotime($start_time), strtotime($end_time)]];
        }
        return $this->where($where)->count();
    }

    /**
     * 待生成面单列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function waitExpressImageList(){
        $list = $this
            ->where(
                [
                    'delivery_status' => 10,
                    'express_html' => ''
                ]
            )
            ->with(['image'])
            ->order('create_time','desc')
            ->field(['id', 'order_no', 'goods_image', 'goods_num', 'goods_name', 'goods_attr', 'receive_user', 'receive_mobile', 'receive_address', 'remark', 'create_time'])
            ->select();
        return compact('list');
    }

    /**
     * 待打印面单列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function waitPrintList(){
        $list = $this
            ->where([
                'delivery_status' => 20
            ])
            ->with(['image'])
            ->order('create_time','desc')
            ->field(['id', 'order_no', 'goods_image', 'goods_num', 'goods_name', 'goods_attr', 'receive_user', 'receive_mobile', 'receive_address', 'remark', 'create_time', 'express_html'])
            ->select();
        return compact('list');
    }

    /**
     * 待确认发货面单列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function waitConfirmDeliveryList(){
        $list = $this
            ->where([
                'delivery_status' => 20
            ])
            ->with(['image'])
            ->order('create_time','desc')
            ->field(['id', 'order_no', 'goods_image', 'goods_num', 'goods_name', 'goods_attr', 'receive_user', 'receive_mobile', 'receive_address', 'remark', 'create_time', 'express_html'])
            ->select();
        return compact('list');
    }

    /**
     * 取消发货
     * @return bool
     */
    public function cancelDelivery(){
        $id = input('post.id',0,'intval');
        $cancel_remark = input('post.remark','','str_filter');
        $res = $this->update(['cancel_remark'=>$cancel_remark, 'cancel_time'=>time(), 'delivery_status'=>40], compact('id'));
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

}