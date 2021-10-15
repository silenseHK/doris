<?php


namespace app\store\model;

use app\common\model\QualitySpecValue as QualitySpecValueModel;
use think\Db;
use think\Exception;

class QualitySpecValue extends QualitySpecValueModel
{

    /**
     * 编辑
     * @return bool
     * @throws \Exception
     */
    public function edit(){
        ##参数
        $product = input('post.product/a',[]);
        $attr = input('post.attr/a',[]);
        $table = input('post.table/a',[]);
        $specModel = new QualitySpec();
        $sort = 1;
        $product_arr = [];
        Db::startTrans();
        try{
            $specModel->where("1=1")->delete();
            $this->where("1=1")->delete();
            foreach($product as $item){
                $product_arr[] = $specModel->insertGetId(['spec_name'=>$item, 'sort'=>$sort]);
            }
            $data = [];
            foreach($table as $k => $it){
                foreach($it as $kk => $v){
                    $data[] = [
                        'spec_value' => $attr[$kk],
                        'spec_id' => $product_arr[$k],
                        'sort' => $kk + 1,
                        'content' => $v
                    ];
                }
            }
            $this->isUpdate(false)->saveAll($data);
            Db::commit();
            return true;
        }catch(Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

}