<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 9:49
 */

namespace app\store\controller\project;


use app\store\controller\Controller;
use app\store\model\project\P_Staff;

class Staff extends Controller
{

    protected $staffModel;

    public function __construct(P_Staff $p_Staff)
    {
        parent::__construct();
        $this->staffModel = $p_Staff;
    }

    public function lists()
    {
        ##参数
        $title = input('title','');
        $obj = $this->staffModel;
        if($title){
            $obj->whereLike('title',"%{$title}%");
        }
        $c_id = input('c_id',0);
        if($c_id){
            $obj->where('c_id', $c_id);
        }
        ##员工列表
        $lists = $obj->paginate(15, false, [
            'query' => \request()->request()
        ]);
        return $this->fetch('lists',compact('lists'));
    }

}