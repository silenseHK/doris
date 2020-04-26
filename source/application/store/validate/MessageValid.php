<?php


namespace app\store\validate;


use think\Validate;

class MessageValid extends Validate
{

    protected $rule = [
        'message_id' => 'require|number|>=:1',
        'title' => 'require|min:4|max:20',
        'content' => 'require|max:100',
        'effect_time' => 'date'
    ];

    protected $scene = [
        'add' => ['title', 'content'],
        'edit' => ['message_id', 'title', 'content'],
        'info' => ['message_id'],
        'del' => ['message_id'],
    ];

}