<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 9:13
 */

namespace app\api\validate\project;


use think\Validate;

class Project extends Validate
{

    protected $rule = [
        'level|项目层级' => 'require|max:20',
        'type|项目类型' => 'require|max:30',
        'title|项目名称' => 'require|max:255',
        'company_id|迎检单位' => 'require',
        'check_time|迎检单位' => 'require',
        'manager|检查组组长' => 'require',
        'remark|备注' => 'max:255',
        'status|当前状态' => 'require'

    ];


    protected $scene = [
        'add' => ['level', 'type', 'title', 'company_id', 'check_time', 'manager', 'remark', 'status'],
    ];

}