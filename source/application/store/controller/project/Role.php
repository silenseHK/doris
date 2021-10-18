<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/16
 * Time: 9:48
 */

namespace app\store\controller\project;


use app\store\controller\Controller;
use app\store\model\project\P_Page;
use app\store\model\project\P_Role;
use app\store\validate\RoleValid;

class Role extends Controller
{

    protected $roleModel, $pageModel;

    protected $validate;

    public function __construct
    (
        P_Role $p_Role,
        RoleValid $validate,
        P_Page $p_Page
    )
    {
        parent::__construct();
        $this->roleModel = $p_Role;
        $this->pageModel = $p_Page;
        $this->validate = $validate;
    }

    public function lists()
    {
        ##参数
        $obj = $this->roleModel;
        ##分公司列表
        $lists = $obj->paginate(15, false, [
            'query' => \request()->request()
        ]);
        return $this->fetch('lists',compact('lists'));
    }

    public function add()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$data = $this->validate->scene('add')->check(input())){
                return $this->renderError($this->validate->getError());
            }
            ##获取数据
            if(!$this->roleModel->add()){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }else{
            return $this->fetch();
        }
    }

    public function edit()
    {
        $id = input('id',0,'intval');
        if(request()->isPost()){
            ##验证参数
            if(!$data = $this->validate->scene('edit')->check(input())){
                return $this->renderError($this->validate->getError());
            }
            ##获取数据
            if(!$this->roleModel->edit()){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }else{
            ##角色信息
            $info = $this->roleModel->where('id',$id)->field('id, title, desc')->find();
            if(!$info)
                return $this->renderError('角色数据已删除或不存在');
            return $this->fetch('',compact('info'));
        }
    }

    public function delete()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$data = $this->validate->scene('delete')->check(input())){
                return $this->renderError($this->validate->getError());
            }
            $id = request()->post('id');
            ##删除
            if(!$this->roleModel->where('id',$id)->setField('delete_time',time())){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }
        return false;
    }

    public function auth()
    {
        $role_id = input('id/d',0);
        if(request()->isPost()){
            if(!$this->roleModel->auth())
            {
                return $this->renderError($this->roleModel->getError());
            }
            return $this->renderSuccess('操作成功');
        }else{
            return $this->fetch('',['auths' => $this->pageModel->auths(), 'role_id' => $role_id, 'power'=>$this->roleModel->power($role_id)]);
        }
    }

}