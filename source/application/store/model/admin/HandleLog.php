<?php


namespace app\store\model\admin;

use app\common\model\admin\HandleLog as HandleLogCommon;
use think\Db;
use think\db\Query;
use think\Session;

class HandleLog extends HandleLogCommon
{

    public static function addLog($access_id){
        $admin = Session::get('yoshop_store.user');
        $params = request()->param();
        $data = [
            'admin_id' => $admin['store_user_id'],
            'access_id' => $access_id,
            'params' => json_encode($params)
        ];
        (new self)->isUpdate(false)->save($data);
    }

    public static function lists(){
        $model = Db::name('admin_handle_log');
        $start_time = input('post.start_time','','str_filter');
        $end_time = input('post.end_time','','str_filter');
        if($start_time && $end_time){
            $where['ahl.create_time'] = ['between', [strtotime($start_time), strtotime($end_time)]];
        }
        $keywords = input('post.keywords','','search_filter');
        if($keywords){
            $where['sa.name'] = ['LIKE', "%{$keywords}%"];
        }
        isset($where) && $model->where($where);
        $size = input('post.size',15,'intval');
        $list = $model->alias('ahl')
            ->join('store_access sa','sa.access_id = ahl.access_id','LEFT')
            ->join('store_user su','su.store_user_id = ahl.admin_id','LEFT')
            ->field(['ahl.log_id', 'ahl.admin_id', 'ahl.access_id', 'ahl.create_time', 'ahl.request_type', 'su.user_name', 'sa.name'])
            ->order('ahl.create_time','desc')
            ->paginate($size,false);
        $list = $list->toArray();
        $list['data'] = self::formatData($list['data']);
        return $list;
    }

    public static function formatData($data){
        $model = new self;
        foreach($data as &$item){
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['request_type'] = $model->getRequestTypeAttr($item['request_type']);
        }
        return $data;
    }

}