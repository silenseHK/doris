<?php


namespace app\common\model\project;


use traits\model\SoftDelete;

class P_Role extends P_Base
{

    protected $name = 'p_role';

    use SoftDelete;

    protected $delete_time = 'delete_time';

    public function lists()
    {
        return $this->field('id, title')->select();
    }

    public function access()
    {
        return $this->belongsToMany('app\common\model\project\P_Handle','p_access','handle_id','role_id')->field('yoshop_p_handle.id, yoshop_p_handle.title, page_id, alias');
    }

}