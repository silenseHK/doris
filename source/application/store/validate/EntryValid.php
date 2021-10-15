<?php


namespace app\store\validate;


use think\Validate;

class EntryValid extends Validate
{

    protected $rule = [
        'keywords' => 'require|max:50',
        'content' => 'require|max:255',
        'sort' => 'require|number|>=:1|<=:9999',
        'entry_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'add' => ['keywords', 'content', 'sort'],
        'edit' => ['entry_id', 'keywords', 'content', 'sort'],
        'del' => ['entry_id'],
        'edit_sort' => ['entry_id', 'sort'],
    ];

}