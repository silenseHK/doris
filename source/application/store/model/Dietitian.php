<?php


namespace app\store\model;

use app\common\model\Dietitian as DietitianModel;
use app\store\validate\DietitianValid;
use think\db\Query;

class Dietitian extends DietitianModel
{

    protected $valid;

    public function __construct($data = [])
    {
        parent::__construct($data);
        $this->valid = new DietitianValid();
    }

    /**
     * 增加营养师
     * @return bool
     */
    public function addDietitian(){
        ##验证
        if(!$this->valid->scene('add')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $data = $this->filterData();
        ##操作
        $res = $this->isUpdate(false)->save($data);
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

    /**
     * 编辑营养师
     * @return bool
     */
    public function editDietitian(){
        ##验证
        if(!$this->valid->scene('edit')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $data = $this->filterData();
        $dietitian_id = input('post.dietitian_id',0,'intval');
        ##操作
        $res = $this->update($data, compact('dietitian_id'));
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

    /**
     * 过滤参数
     * @return array
     */
    protected function filterData(){
        return [
            'name' => input('post.name','','str_filter'),
            'title' => input('post.title','','str_filter'),
            'description' => json_encode(input('post.description/a',[])),
            'image_id' => input('post.image_id',0,'intval')
        ];
    }

    /**
     * 营养师列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function dietitian(){
        $list = $this->with(['image'=>function(Query $query){
            $query->field(['file_id', 'file_url', 'storage', 'file_name']);
        }])->order('sort','asc')->field(['dietitian_id', 'name', 'image_id', 'title', 'description', 'sort'])->select();
        $list = $list->isEmpty()? [] : $list->toArray();
        return $list;
    }

    /**
     * 编辑排序
     * @return bool
     */
    public function editDietitianSort(){
        if(!$this->valid->scene('edit_sort')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $dietitian_id = input('post.dietitian_id',0,'intval');
        $sort = input('post.sort',9999,'intval');
        ##操作
        $res = $this->where(compact('dietitian_id'))->setField('sort',$sort);
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

    /**
     * 删除营养师
     * @return bool
     */
    public function delDietitian(){
        if(!$this->valid->scene('del')->check(input())){
            $this->error = $this->valid->getError();
            return false;
        }
        ##参数
        $dietitian_id = input('post.dietitian_id',0,'intval');
        ##操作
        $res = $this->where(compact('dietitian_id'))->delete();
        if($res === false){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

}