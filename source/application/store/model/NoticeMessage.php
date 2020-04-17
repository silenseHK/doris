<?php


namespace app\store\model;

use app\common\model\NoticeMessage as NoticeMessageModel;

class NoticeMessage extends NoticeMessageModel{

    public function getSystemLists(){
        $this->setWhere([
            'type' => input('type',0,'intval'),
            'start_time' => input('start_time','','str_filter'),
            'end_time' => input('end_time','','str_filter'),
        ]);
        $list = $this->field(['id', 'title', 'content', 'url', 'params', 'create_time', 'effect_time'])->order('create_time','desc')->paginate(10,false,[
            'query' => \request()->request()
        ]);
        return compact('list');
    }

    public function setWhere($params){
        $where = ['type'=>10];
        if($params['type'] > 0){
            if($params['type'] == 1){ //已生效
                $where['effect_time'] = ['ELT', time()];
            }else{ //未生效
                $where['effect_time'] = ['GT', time()];
            }
        }
        if($params['start_time'] && $params['end_time']){
            $where['create_time'] = ['BETWEEN', [strtotime($params['start_time'] . ' 00:00:01'), strtotime($params['end_time'] . ' 23:59:59')]];
        }
        $this->where($where);
    }

}