<?php


namespace app\store\validate;


use think\Validate;

class OnlineQuestionsValid extends Validate
{

    protected $rule = [
        'title' => 'require|max:50',
        'sort' => 'number|between:0,9999',
        'status' => 'number|in:0,1',
        'cate_id' => 'require|number|>=:1',
        'desc' => 'require|max:100',
        'answer' => 'require',
        'question_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'cate_add' => ['title', 'sort', 'status'],
        'cate_edit' => ['cate_id', 'title', 'sort', 'status'],
        'cate_info' => ['cate_id'],
        'cate_change_status' => ['cate_id'],
        'cate_del' => ['cate_id'],
        'add' => ['title', 'cate_id', 'desc', 'answer', 'sort', 'status'],
        'edit' => ['question_id', 'title', 'cate_id', 'desc', 'answer', 'sort', 'status'],
        'info' => ['question_id'],
        'change_status' => ['question_id'],
        'del' => ['question_id'],
    ];

}