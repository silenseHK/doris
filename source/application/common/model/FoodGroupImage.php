<?php


namespace app\common\model;


use think\db\Query;
use think\model\Pivot;

class FoodGroupImage extends Pivot
{

    protected $name= 'food_group_image';

    protected $autoWriteTimestamp = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return 10001;
    }

    public static function getImages($id){
        return self::where(['food_group_id'=>$id])->with(['images'=>function(Query $query){$query->field(['file_id', 'file_name', 'storage', 'file_url']);}])->order('sort','asc')->select();
    }

    /**
     * 图片
     * @return \think\model\relation\BelongsTo
     */
    public function images(){
        return $this->belongsTo('app\common\model\UploadFile','image_id','file_id');
    }

}