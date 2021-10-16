<?php


namespace app\api\validate\business;


use think\Validate;

class ProjectValid extends Validate
{

    protected $rule = [
        'id' => 'require|number|>=:1',
        'level|项目层级' => 'require|max:20',
        'type|项目类型' => 'require|max:30',
        'title|项目名称' => 'require|max:255',
        'company_id|迎检单位' => 'require|number',
        'check_time|迎检时间' => 'require|number',
        'manager|检查组组长' => 'require',
        'desc|备注' => 'max:255',
        'status|当前状态' => 'require|number'

    ];


    protected $scene = [
        'add' => ['level', 'type', 'title', 'company_id', 'check_time', 'manager', 'desc', 'status'],
        'detail' => ['id'],
    ];

}