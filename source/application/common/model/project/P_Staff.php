<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 10:10
 */

namespace app\common\model\project;


use app\common\model\BaseModel;
use traits\model\SoftDelete;

class P_Staff extends BaseModel
{

    protected $table = 'yoshop_p_staff';

    use SoftDelete;

    protected $delete_time = 'delete_time';

}