<?php


namespace app\api\validate\college;


use think\Validate;

class LessonValid extends Validate
{

    protected $rule = [
        'cate_pid' => 'require|number|>=:0',
        'cate_id' => 'number|>=:0',
        'page' => 'number|>=:1',
        'size' => 'number|>=:1',
        'lesson_id' => 'require|number|>=:1',
        'class_id' => 'require|number|>=:1',
        'type' => 'require|number|in:1,2',
        'lecturer_id' => 'require|number|>=:1',
        'lesson_size' => 'require|number|in:10,20',
        'keywords' => 'require'
    ];

    protected $scene = [
        'lesson_list' => ['cate_id', 'cate_pid', 'page', 'size'],
        'lesson_detail' => ['lesson_id'],
        'check_access' => ['class_id'],
        'collect' => ['lesson_id', 'type'],
        'lecturer_collect' => ['lecturer_id', 'type'],
        'lesson_collect_list' => ['page', 'size'],
        'lecturer_collect_list' => ['page', 'size'],
        'lecturer_lesson_list' => ['lecturer_id', 'lesson_size', 'page', 'size'],
        'watch_record' => ['page', 'size'],
        'search' => ['keywords']
    ];

}