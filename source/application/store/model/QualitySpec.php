<?php


namespace app\store\model;

use app\common\model\QualitySpec as QualitySpecModel;
use think\db\Query;

class QualitySpec extends QualitySpecModel
{

    /**
     * 规格信息
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function spec(){
        $list = $this
            ->with([
                'specList' => function(Query $query){
                    $query->field(['spec_value_id', 'spec_value', 'spec_id', 'content'])->order('sort','asc');
                }
            ])
            ->order('sort','asc')
            ->select();
        if(!$list->isEmpty())
            $list = $list->toArray();
        else
            $list = [];
        $product = array_column($list,'spec_name');
        $attr = empty($list) ? [] : array_column($list[0]['spec_list'],'spec_value');
        $table = [];
        foreach($list as $key => $item){
            $table[$key] = [];
            foreach($item['spec_list'] as $k => $v){
                $table[$key][$k] = $v['content'];
            }
        }
        return compact('product','attr','table');
    }

}