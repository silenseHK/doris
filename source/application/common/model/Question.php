<?php


namespace app\common\model;


use traits\model\SoftDelete;

class Question extends BaseModel
{

    protected $name = 'questions';

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $type = [
        '10' => [
            'value' => 10,
            'type' => 'input',
            'text' => '输入框'
        ],
        '20' => [
            'value' => 20,
            'type' => 'radio',
            'text' => '单选'
        ],
        '30' => [
            'value' => 30,
            'type' => 'checkbox',
            'text' => '多选'
        ],
        '40' => [
            'value' => 40,
            'type' => 'textarea',
            'text' => '长文'
        ],
    ];

    public function getTypeList(){
        return $this->type;
    }

    public function getTypeAttr($value){
        return $this->type[$value];
    }

    /**
     * 关联选择题选项
     * @return \think\model\relation\HasMany
     */
    public function option(){
        return $this->hasMany('app\common\model\QuestionOptions','question_id','question_id')->order('mark','asc');
    }

}