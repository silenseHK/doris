<?php


namespace app\store\model;

use app\common\model\FoodGroup as FoodGroupModel;
use app\store\validate\FoodGroupValid;
use think\db\Query;
use think\Exception;

class FoodGroup extends FoodGroupModel
{

    public function getIndexData(){
        $list = $this
            ->with(
                [
                    'image' => function(Query $query){
                        $query->field(['file_id', 'storage', 'file_name']);
                    }
                ]
            )
            ->paginate(15,false,['query'=>\request()->request()]);
        return compact('list');
    }

    public function add(){
        ##验证
        $validate = new FoodGroupValid();
        if(!$validate->scene('add')->check(input()))throw new Exception($validate->getError());
        $data = $this->filterData();
        $res = $this->isUpdate(false)->save($data);
        if($res === false)throw new Exception('操作失败');
    }

    public function edit(){
        ##验证
        $validate = new FoodGroupValid();
        if(!$validate->scene('edit')->check(input()))throw new Exception($validate->getError());
        $data = $this->filterData();
        $id = input('post.id',0,'intval');
        $res = $this->isUpdate(true)->save($data,compact('id'));
        if($res === false)throw new Exception('操作失败');
    }

    public function filterData(){
        return [
            'img_id' => input('post.img_id',0,'intval'),
            'max_bmi' => input('post.max_bmi',0,'floatval'),
            'min_bmi' => input('post.min_bmi',0,'floatval'),
        ];
    }

    public function del(){
        ##验证
        $validate = new FoodGroupValid();
        if(!$validate->scene('del')->check(input()))throw new Exception($validate->getError());
        ##参数
        $id = input('post.id',0,'intval');
        $res = $this->where(compact('id'))->delete();
        if($res === false)throw new Exception('操作失败');
    }

    public function info(){
        ##验证
        $validate = new FoodGroupValid();
        if(!$validate->scene('info')->check(input()))throw new Exception($validate->getError());
        ##参数
        $id = input('id',0,'intval');
        $data = self::get(['id'=>$id], ['image']);
        return compact('data');
    }

}