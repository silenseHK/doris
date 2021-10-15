<?php


namespace app\common\model;


class Salesperson extends BaseModel
{

    protected $name = 'salesperson';

    protected $pk = 'salesperson_id';

    protected $updateTime = false;

    protected $insert = ['wxapp_id'];

    public function setWxappIdAttr(){
        return self::$wxapp_id?:10001;
    }

    public function user(){
        return $this->belongsTo('app\common\model\User','user_id','user_id');
    }

    protected $type = [
        10 => [
            'text' => '总监',
            'value' => 10,
        ],
        20 => [
            'text' => '经理',
            'value' => 20,
        ],
    ];

    public function typeList(){
        return $this->type;
    }

    public function getTypeAttr($type){
        return $this->type[$type];
    }

    protected $group = [
        '1' => [
            'text' => '招商一部',
            'value' => 1
        ],
        '2' => [
            'text' => '招商二部',
            'value' => 2
        ],
        '3' => [
            'text' => '招商三部',
            'value' => 3
        ]
    ];

    public function groupList(){
        return $this->group;
    }

    public function getGroupIdAttr($value){
        return $this->group[$value];
    }

}