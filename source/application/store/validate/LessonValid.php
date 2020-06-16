<?php


namespace app\store\validate;


use think\Validate;

class LessonValid extends Validate
{

    protected $rule = [
        'title' => 'require',
        'pid' => 'require|number|>=:0',
        'is_show' => 'require|in:0,1',
        'sort' => 'require|number|>=:1|<=:99999',
        'lesson_cate_id' => 'require|number|>=:1',
        'cover' => 'require|number|>=:1',
        'lecturer_id' => 'require|number|>=:1',
        'cate_id' => 'require|number|>=:1',
        'is_public' => 'require|number|in:0,1',
        'is_private' => 'require|number|in:0,1',
        'is_grade' => 'require|number|in:0,1',
        'lesson_type' => 'require|number|in:10,20',
        'lesson_size' => 'require|number|in:10,20',
        'status' => 'require|number|in:0,1',
        'is_recom' => 'require|number|in:0,1',
        'total_size' => 'require|number|>=:1',
        'lesson_id' => 'require|number|>=:1',
        'field' => 'require',
    ];

    protected $scene = [
        'cate_add' => ['title', 'pid', 'is_show', 'sort'],
        'cate_edit' => ['lesson_cate_id', 'title', 'pid', 'is_show', 'sort'],
        'add' => ['title', 'cover', 'lecturer_id', 'cate_id', 'is_public', 'is_private', 'is_grade', 'lesson_type', 'lesson_size', 'status', 'is_recom', 'total_size'],
        'edit' => ['lesson_id', 'title', 'cover', 'lecturer_id', 'cate_id', 'is_public', 'is_private', 'is_grade', 'lesson_type', 'lesson_size', 'status', 'is_recom', 'total_size'],
        'change_field' => ['lesson_id', 'field']
    ];

}