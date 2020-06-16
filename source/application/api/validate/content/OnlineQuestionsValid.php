<?php


namespace app\api\validate\content;


use think\Validate;

class OnlineQuestionsValid extends Validate
{

    protected $rule = [
        'cate_id' => 'require|number|>=:0',
        'page' => 'number|>=:1',
        'size' => 'number|>=:1',
        'question_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'answer_list' => ['cate_id', 'page', 'size'],
        'info' => ['question_id'],
    ];

}