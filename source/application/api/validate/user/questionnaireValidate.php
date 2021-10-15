<?php


namespace app\api\validate\user;


use think\Validate;

class questionnaireValidate extends Validate
{

    protected $rule = [
        'questionnaire_id' => 'require|number|>=:1',
        'answer' => 'require|array',
        'page' => 'number|>=:1',
        'size' => 'number|>=:1|<=:15',
        'mobile' => 'mobile',
        'fill_id' => 'require|number|>=:1',
        'referee_id' => 'number|>=:1'
    ];

    protected $scene = [
        'submit' => ['questionnaire_id', 'answer', 'referee_id'],
        'answer_list' => ['page', 'size', 'mobile'],
        'answer_detail' => ['fill_id']
    ];

    /**
     * 验证手机号
     * @param $value
     * @return bool|string
     */
    protected function mobile($value){
        if(preg_match("/^1[345789]\d{9}$/", $value)){
            return true;
        }else{
            return "手机号格式错误";
        }
    }

}