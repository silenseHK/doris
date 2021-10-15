<?php


namespace app\store\model;

use app\common\model\Impression as ImpressionModel;

class Impression extends ImpressionModel
{

    /**
     * 添加印象
     * @return bool
     */
    public function addImpression(){
        ##参数
        $data = [
            'author' => input('post.author','','str_filter'),
            'content' => input('post.content','','str_filter'),
            'sort' => input('post.sort',9999,'intval')
        ];
        ##操作
        $res = $this->isUpdate(false)->save($data);
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return $this->getLastInsID();
    }

    /**
     * 删除印象
     * @return bool
     */
    public function delImpression(){
        ##参数
        $impression_id = input('post.impression_id',0,'intval');
        ##操作
        $res = $this->where(compact('impression_id'))->delete();
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

    /**
     * 编辑排序
     * @return bool
     */
    public function editSort(){
        ##参数
        $impression_id = input('post.impression_id',0,'intval');
        $sort = input('post.sort',9999,'intval');
        if($sort <= 0){
            $this->error = '排序错误';
            return false;
        }
        ##操作
        $res = $this->where(compact('impression_id'))->setField('sort', $sort);
        if($res === false){
            $this->error = '操作错误';
            return false;
        }
        return true;
    }

    /**
     * 印象列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function impressList(){
        $list = $this->field(['impression_id', 'author', 'content', 'sort'])->order('impression_id','asc')->select();
        $list = $list->isEmpty() ? [] : $list->toArray();
        return $list;
    }

}