<?php


namespace app\store\model;

use app\common\model\Qrcode as QrcodeModel;

class Qrcode extends QrcodeModel
{

    /**
     * 新增
     * @return bool|false|int
     */
    public function add(){
        ##参数
        $data = $this->filterData();
        if($data['status']){
            $this->where(['qrcode_type'=>$data['qrcode_type']])->setField('status',0);
        }
        return $this->isUpdate(false)->save($data);
    }

    /**
     * 编辑
     * @return Qrcode
     */
    public function edit(){
        ##参数
        $data = $this->filterData();
        $qrcode_id = input('post.qrcode_id',0,'intval');
        if($data['status']){
            $this->where(['qrcode_type'=>$data['qrcode_type']])->setField('status',0);
        }
        return $this->update($data,['qrcode_id'=>$qrcode_id]);
    }

    /**
     * 过滤参数
     * @return array
     */
    public function filterData(){
        $data = [
            'title' => input('post.title','','str_filter'),
            'qrcode_type' => input('post.qrcode_type',10,'intval'),
            'image_id' => input('post.img_id',0,'intval'),
            'status' => input('post.status',0,'intval'),
        ];
        return $data;
    }

    /**
     * 二维码信息
     * @return array
     * @throws \think\exception\DbException
     */
    public function getInfo(){
        $qrcode_id = input('qrcode_id',0,'intval');
        $info = self::get($qrcode_id, ['image']);
        $info['img'] = $info['image']['file_path'];
        $info['img_id'] = $info['image_id'];
        $info['img_list'] = [$info['image']['file_path']];
        $info['status'] = "{$info['status']}";
        return compact('info');
    }

    /**
     * 二维码列表
     * @return array
     * @throws \think\exception\DbException
     */
    public function getLists(){
        $list = $this->with(['image'])->order('create_time','desc')->paginate(15,false,['type' => 'Bootstrap',
            'var_page' => 'page',
            'path' => 'javascript:getList([PAGE]);']);
        $page = $list->render();
        $total = $list->total();
        $list = $list->toArray()['data'];
        return compact('page','list','total');
    }

    /**
     * 删除二维码
     * @return int
     */
    public function del(){
        $qrcode_id = input('post.qrcode_id',0,'intval');
        return self::destroy($qrcode_id);
    }

    /**
     * 编辑属性
     * @return bool|int
     * @throws \think\exception\DbException
     */
    public function editField(){
        $qrcode_id = input('post.qrcode_id',0,'intval');
        $info = self::get($qrcode_id);
        if(!$info)return $this->setError('二维码数据不存在');
        $field = input('post.field','','str_filter');
        if(!$field)return $this->setError('参数缺失');
        $value = ($info[$field] + 1) % 2;
        if($field == 'status' && $value == 1){
            $this->where(['qrcode_type'=>$info['qrcode_type']])->setField('status',0);
        }
        $res = $this->where(['qrcode_id'=>$qrcode_id])->setField($field,$value);
        if($res === false)return $this->setError('操作失败');
        return $value;
    }

}