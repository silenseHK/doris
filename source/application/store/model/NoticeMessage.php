<?php


namespace app\store\model;

use app\common\model\NoticeMessage as NoticeMessageModel;
use app\store\validate\MessageValid;
use think\Exception;

class NoticeMessage extends NoticeMessageModel{

    public function getSystemLists(){
        $params = [
            'type' => input('type',0,'intval'),
            'start_time' => input('start_time','','str_filter'),
            'end_time' => input('end_time','','str_filter'),
        ];
        $this->setWhere($params);
        $list = $this->field(['id', 'title', 'content', 'url', 'params', 'create_time', 'effect_time'])->order('create_time','desc')->paginate(10,false,[
            'query' => \request()->request()
        ]);

        return array_merge(compact('list'), $params);
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

    /**
     * 添加操作
     * @throws Exception
     */
    public function add(){
        ##验证
        $messageValid = new MessageValid();
        if(!$messageValid->scene('add')->check(input()))throw new Exception($messageValid->getError());
        ##接收参数
        $data = $this->filterData();
        $res = $this->isUpdate(false)->save($data);
        if($res === false)throw new Exception('操作失败');
    }

    /**
     * 修改操作
     * @throws Exception
     */
    public function edit(){
        ##验证
        $messageValid = new MessageValid();
        if(!$messageValid->scene('edit')->check(input()))throw new Exception($messageValid->getError());
        ##接收参数
        $id = input('post.message_id',0,'intval');
        $data = $this->filterData();
        $res = $this->isUpdate(true)->save($data, compact('id'));
        if($res === false)throw new Exception('操作失败');
    }

    public function filterData(){
        $data = [
            'title' => input('post.title','','str_filter'),
            'content' => input('post.content','','str_filter'),
            'url' => input('post.url','','str_filter'),
            'params' => input('post.params','','str_filter'),
            'effect_time' => input('post.effect_time','','str_filter'),
            'detail' => input('post.detail','','htmlspecialchars')
        ];
        $data['effect_time'] = strtotime($data['effect_time'] . " 08:00:00");
        return $data;
    }

    public function setParamsAttr($value){
        if(!$value)return '';
        $value = explode('&',$value);
        $params = [];
        foreach($value as $val){
            $item = explode('=',$val);
            $params[$item[0]] = $item[1];
        }
        return json_encode($params);
    }

    /**
     * 详情信息
     * @return array
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function info(){
        ##验证
        $messageValid = new MessageValid();
        if(!$messageValid->scene('info')->check(input()))throw new Exception($messageValid->getError());
        ##参数
        $id = input('message_id',0,'intval');
        $info = $this->where(compact('id'))->find();
        return compact('info');
    }

    public function getParamsAttr($value){
        if(!$value)return '';
        $params = '';
        $value = json_decode($value);
        foreach($value as $key => $val){
            $params .= "&{$key}={$val}";
        }
        return trim($params,'&');
    }

    /**
     * 删除操作
     * @throws Exception
     */
    public function del(){
        ##验证
        $messageValid = new MessageValid();
        if(!$messageValid->scene('del')->check(input()))throw new Exception($messageValid->getError());
        $id = input('post.message_id',0,'intval');
        $res = self::destroy($id);
        if($res === false)throw new Exception('操作失败');
    }

}