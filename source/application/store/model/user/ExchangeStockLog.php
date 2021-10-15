<?php


namespace app\store\model\user;

use app\common\model\user\ExchangeStockLog as ExchangeStockLogModel;
use app\store\model\User as UserModel;
use think\db\Query;

class ExchangeStockLog extends ExchangeStockLogModel
{

    public function getExchangeList(){
        $this->filterData();
        $list = $this
            ->field(['log_id', 'user_id', 'receive_user_id', 'goods_id', 'goods_sku_id', 'stock', 'remark', 'create_time', 'transfer_stock'])
            ->with(
                [
                    'user' => function(Query $query){
                        $query->field(['user_id', 'nickName', 'mobile']);
                    },
                    'receive_user' => function(Query $query){
                        $query->field(['user_id', 'nickName', 'mobile']);
                    },
                    'goods' => function(Query $query){
                        $query->with(['specRel', 'image']);
                    },
                    'spec.image'
                ]
            )
            ->order('create_time','desc')
            ->paginate(15,false,['type' => 'Bootstrap',
                'var_page' => 'page',
                'path' => 'javascript:getExchangeList([PAGE]);']);
        $page = $list->render();
        $total = $list->total();
        $list = $list->toArray()['data'];
        foreach($list as &$item){
            if($item['spec']['sku_list']){
                $item['spec']['attr'] = $item['spec']['sku_list'][0]['spec_name'] . ':' . $item['spec']['sku_list'][0]['spec_value'];
            }else{
                $item['spec']['attr'] = '单规格';
            }
        }
        return compact('page','total','list');
    }

    public function filterData(){
        $params = [
            'start_time' => input('post.start_time','','str_filter'),
            'end_time' => input('post.end_time','','str_filter'),
            'keywords' => input('post.keywords','','search_filter'),
        ];
        if($params['start_time'] && $params['end_time']){
            $start_time = strtotime($params['start_time']);
            $end_time = strtotime($params['end_time']);
            $where['create_time'] = ['BETWEEN', [$start_time, $end_time]];
        }
        if($params['keywords']){
            $userModel = new UserModel();
            $user_ids = $userModel->where(['nickName'=>['LIKE', "%{$params['keywords']}%"]])->column('user_id');
            $where['user_id'] = ['IN', $user_ids];
        }
        if(isset($where))$this->where($where);
    }

}