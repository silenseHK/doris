<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/16
 * Time: 9:50
 */

namespace app\store\validate;


use think\Validate;

class RoleValid extends Validate
{

    protected $rule = [
        'id' => 'require|>=:1',
        'title' => 'require|max:20',
        'desc' => 'max:255',
    ];

    protected $scene = [
        'add' => ['title', 'desc'],
        'edit' => ['id', 'title', 'desc'],
        'delete' => ['id']
    ];

}