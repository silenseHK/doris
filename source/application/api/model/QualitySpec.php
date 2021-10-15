<?php


namespace app\api\model;

use app\common\model\QualitySpec as QualitySpecModel;
use think\db\Query;

class QualitySpec extends QualitySpecModel
{

    public function quality(){
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
        return compact('list','product','attr');
    }

}