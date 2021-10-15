<?php


namespace app\store\validate;


use think\Validate;

class SalespersonValid extends Validate
{

    protected $rule = [
        'user_id|用户' => 'require|number|>=:1|unique:salesperson,user_id',
        'name|真实姓名' => 'require|max:20',
        'salesperson_id' => 'require|number|>=:1',
        'group_id|部门' => 'require|number|>=:1',
        'type|职位' => 'require|number|in:10,20',
        'start_time' => 'require|date',
        'end_time' => 'require|date',
    ];

    protected $scene = [
        'add' => ['user_id', 'name', 'group_id', 'type'],
        'edit' => ['salesperson_id', 'user_id', 'name', 'group_id', 'type'],
        'del' => ['salesperson_id'],
        'edit_status' => ['salesperson_id'],
        'export_sale_data' => ['start_time', 'end_time']
    ];

}