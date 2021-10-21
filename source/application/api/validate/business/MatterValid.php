<?php


namespace app\api\validate\business;


use think\Validate;

class MatterValid extends Validate
{

    protected $rule = [
        'id' => 'require|number|>=:1',
        'matter_id|问题' => 'require|number|>=:1',
        'title|问题标题' => 'require|max:255',
        'project_id|项目' => 'require|number|>=:1',
        'type|问题类型' => 'require|number|>=:1',
        'desc|问题描述' => 'require|max:255',
        'risk|风险等级' => 'require|>=:1',
        'amount|涉及金额' => 'require|>=:0',
        'reform_time|整改截至日期' => 'require|number|>=:0',
        'contact_user|问题联系人 ' => 'number|>=:0',
        'status|问题状态 ' => 'require|number|>=:0',
    ];

    protected $scene = [
        'add' => ['title', 'project_id', 'type', 'desc', 'risk', 'amount', 'reform_time', 'a_id', 'contact_user', 'status'],
        'edit' => ['id', 'title', 'project_id', 'type', 'desc', 'risk', 'amount', 'reform_time', 'a_id', 'contact_user', 'status'],
        'detail' => ['id'],
        'del' => ['id'],
        'done' => ['id'],
        'assign' => ['matter_id'],
    ];

}