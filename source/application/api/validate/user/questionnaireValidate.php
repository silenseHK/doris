<?php


namespace app\api\validate\user;


use think\Validate;

class questionnaireValidate extends Validate
{

    protected $rule = [
        'questionnaire_id' => 'require|number|>=:1',
        'answer' => 'require|array',
    ];

    protected $scene = [
        'submit' => ['questionnaire_id', 'answer']
    ];

}