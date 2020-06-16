<?php


namespace app\store\validate;


use think\Validate;

class CollegeClassValid extends Validate
{

    protected $rule = [
        'title' => 'require|max:50',
        'lesson_id' => 'require|number|>=:1',
        'img_id' => 'require|>=:1',
        'sort' => 'require|>=:1|<=:99999',
        'class_id' => 'require|>=:1',
        'video_url' => 'require|url',
        'field' => 'require',
        'start_time' => 'require|date',
        'expire_time' => 'require|date',
        'can_use_num' => 'require|number|>=:0',
        'code_id' => 'require|number|>=:1'
    ];

    protected $scene = [
        'add' => ['title', 'lesson_id', 'img_id', 'sort', 'video_url'],
        'edit' => ['class_id', 'title', 'lesson_id', 'img_id', 'sort', 'video_url'],
        'edit_info' => ['class_id'],
        'change_field' => ['class_id', 'field'],
        'add_code' => ['class_id', 'start_time', 'expire_time', 'can_use_num'],
        'del_code' => ['code_id'],
        'code_list' => ['class_id'],
        'lesson_code_list' => ['lesson_id'],
        'add_lesson_code' => ['lesson_id', 'start_time', 'expire_time', 'can_use_num'],
    ];

}