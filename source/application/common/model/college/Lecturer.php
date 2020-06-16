<?php


namespace app\common\model\college;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class Lecturer extends BaseModel
{

    protected $name = 'college_lecturer';

    protected $pk = 'lecturer_id';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $append = ['label_list'];

    /**
     * 一对多 讲师头像
     * @return \think\model\relation\BelongsTo
     */
    public function image(){
        return $this->belongsTo('app\common\model\UploadFile','avatar','file_id');
    }

    /**
     * 获取标签列表
     * @param $value
     * @param $data
     * @return array
     */
    public function getLabelListAttr($value, $data){
        if(!isset($data['label']) || !$data['label'])return [];
        return explode(',',$data['label']);
    }

}