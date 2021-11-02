<?php
/**
 * Created by PhpStorm.
 * User: 27989
 * Date: 2021/10/15
 * Time: 9:49
 */

namespace app\store\controller\project;


use app\store\controller\Controller;
use app\store\model\project\P_Company;
use app\store\model\project\P_Department;
use app\store\model\project\P_Role;
use app\store\model\project\P_Staff;
use app\store\validate\StaffValid;

class Staff extends Controller
{

    protected $staffModel, $companyModel, $departmentModel, $roleModel;

    protected $validate;

    public function __construct
    (
        P_Staff $p_Staff,
        P_Company $p_company,
        P_Department $p_department,
        P_Role $p_Role,
        StaffValid $validate
    )
    {
        parent::__construct();
        $this->staffModel = $p_Staff;
        $this->companyModel = $p_company;
        $this->departmentModel = $p_department;
        $this->roleModel = $p_Role;
        $this->validate = $validate;
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
        $lists = $obj
            ->with(
                [
                    'company',
                    'department',
                    'role'
                ]
            )
            ->paginate(15, false, [
            'query' => \request()->request()
        ]);
        ##分公司列表
        $company_list = $this->companyModel->levelCate();
        return $this->fetch('lists',compact('lists','company_list'));
    }

    public function add()
    {
        if(request()->isPost()){
            ##验证参数
            if(!$data = $this->validate->scene('add')->check(input())){
                return $this->renderError($this->validate->getError());
            }
            ##获取数据
            if(!$this->staffModel->add()){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }else{
            ##分公司列表
            $company_ist = $this->companyModel->adminLists();
            ##角色列表
            $role_list = $this->roleModel->lists();
            ##部门列表
            $department_list = $this->departmentModel->listsGroupByCompany();
            return $this->fetch('',compact('company_ist','role_list','department_list'));
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
            if(!$this->staffModel->edit()){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }else{
            ##员工信息
            $info = $this->staffModel->where('id',$id)->field('id, title, pwd, account, a_id, c_id, role_id, status, is_expert')->find();
            if(!$info)
                return $this->renderError('员工数据已删除或不存在');
            $info = $info->getData();
            $info['pwd'] = '';
            $companies = [];
            $this->companyModel->getParents($info['c_id'],$companies);
            $info['c_id'] = $companies;
            ##分公司列表
            $company_ist = $this->companyModel->adminLists();
            ##角色列表
            $role_list = $this->roleModel->lists();
            ##部门列表
            $department_list = $this->departmentModel->listsGroupByCompany();
            return $this->fetch('',compact('company_ist','role_list','department_list','info'));
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
            if(!$this->staffModel->where('id',$id)->setField('delete_time',time())){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }
    }

}