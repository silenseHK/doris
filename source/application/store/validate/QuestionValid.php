<?php


namespace app\store\validate;


use think\Validate;

class QuestionValid extends Validate
{

    protected $rule = [
        'name' => 'require',
        'label' => 'require',
        'type' => 'require|in:10,20,30,40',
        'is_require' => 'require|in:0,1',
        'question_id' => 'require|number|>=:1',
        'title' => 'require',
        'status' => 'require|number|in:1,2',
        'question_ids' => 'require|array',
        'questionnaire_id' => 'require|number|>=:1',
        'questionnaire_no' => 'require|unique:questionnaire,questionnaire_no',
        'choose_limit' => 'number|>=:0',
        'questions' => 'require|array'
    ];

    protected $scene = [
        'add' => ['name', 'label', 'type', 'is_require', 'choose_limit'],
        'edit' => ['question_id', 'name', 'label', 'type', 'is_require', 'choose_limit'],
        'del' => ['question_id'],
        'questionnaire_add' => ['title', 'questionnaire_no', 'status', 'questions'],
        'questionnaire_edit' => ['questionnaire_id', 'title', 'questionnaire_no', 'status', 'questions'],
        'questionnaire_info' => ['questionnaire_id'],
        'questionnaire_del' => ['questionnaire_id']
    ];

}