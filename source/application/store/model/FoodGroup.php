<?php


namespace app\store\model;

use app\common\model\FoodGroup as FoodGroupModel;
use app\store\validate\FoodGroupValid;
use think\Db;
use think\db\Query;
use think\Exception;

class FoodGroup extends FoodGroupModel
{

    public function getIndexData(){
        $list = $this->paginate(15,false,['query'=>\request()->request()]);
        foreach($list as $key => $val){
            $list[$key]['images'] = FoodGroupImage::getImages($val['id']);
        }
        return compact('list');
    }

    public function add(){
        ##验证
        $validate = new FoodGroupValid();
        if(!$validate->scene('add')->check(input()))throw new Exception($validate->getError());
        $data = $this->filterData();
        Db::startTrans();
        try{
            $res = $this->isUpdate(false)->allowField(true)->save($data);
            if($res === false)throw new Exception('操作失败');
            ##增加配餐与图片关联
            $ins_data = [];
            $food_group_id = $this->getLastInsID();
            foreach($data['imgs'] as $key => $val){
                $ins_data[] = [
                    'food_group_id' => $food_group_id,
                    'image_id' => $val['file_id'],
                    'sort' => $key
                ];
            }
            $imageModel = new FoodGroupImage();
            $res = $imageModel->isUpdate(false)->saveAll($ins_data);
            if($res === false)throw new Exception('操作失败.');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }

    }

    public function edit(){
        ##验证
        $validate = new FoodGroupValid();
        if(!$validate->scene('edit')->check(input()))throw new Exception($validate->getError());
        $data = $this->filterData();
        $id = input('post.id',0,'intval');
        Db::startTrans();
        try{
            $res = $this->isUpdate(true)->allowField(true)->save($data,compact('id'));
            if($res === false)throw new Exception('操作失败');
            ##删除原有的关联
            $imageModel = new FoodGroupImage();
            $imageModel->where(['food_group_id'=>$id])->delete();
            ##增加配餐与图片关联
            $ins_data = [];
            $food_group_id = $id;
            foreach($data['imgs'] as $key => $val){
                $ins_data[] = [
                    'food_group_id' => $food_group_id,
                    'image_id' => $val['file_id'],
                    'sort' => $key
                ];
            }

            $res = $imageModel->isUpdate(false)->saveAll($ins_data);
            if($res === false)throw new Exception('操作失败.');
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function filterData(){
        return [
            'imgs' => input('post.imgs/a',[]),
            'max_bmi' => input('post.max_bmi',0,'floatval'),
            'min_bmi' => input('post.min_bmi',0,'floatval'),
            'is_special' => input('post.is_special',0,'intval'),
            'type' => input('post.type_',1,'intval'),
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
        $data = self::get(['id'=>$id], ['images']);
        $images = [];
        foreach($data['images'] as $val){
            $images[] = [
                'file_id' => $val['file_id'],
                'file_path' => $val['file_path']
            ];
        }
        $data['images'] = $images;
        $typeList = $this->getTypeList();
        return compact('data','typeList');
    }

}