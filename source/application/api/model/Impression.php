<?php


namespace app\api\model;

use app\common\model\Impression as ImpressionModel;

class Impression extends ImpressionModel
{

    public function impression(){
        $list = $this->field(['impression_id', 'author', 'content', 'sort'])->order('impression_id','asc')->select();
        $list = $list->isEmpty() ? [] : $list->toArray();
        return $list;
    }

}