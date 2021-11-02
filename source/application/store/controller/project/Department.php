<?php


namespace app\store\controller\project;


use app\store\controller\Controller;
use app\store\model\project\P_Company;
use app\store\model\project\P_Department;
use app\store\validate\DepartmentValid;
use think\db\Query;

class Department extends Controller
{

    protected $companyModel, $departmentModel;

    protected $validate;

    public function __construct
    (
        P_Company $p_company,
        P_Department $p_department,
        DepartmentValid $validate
    )
    {
        parent::__construct();
        $this->companyModel = $p_company;
        $this->departmentModel = $p_department;
        $this->validate = $validate;
    }

    public function lists()
    {
        ##参数
        $title = input('title','');
        $obj = $this->departmentModel;
        if($title){
            $obj->whereLike('title',"%{$title}%");
        }
        $c_id = input('c_id',0);
        if($c_id){
            $obj->where('c_id', $c_id);
        }
        ##部门列表
        $lists = $obj
            ->with(
                [
                    'company' => function(Query $query)
                    {
                        $query->field('id, title');
                    }
                ]
            )
            ->order('c_id','asc')
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
            if(!$this->departmentModel->add()){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }else{
            ##分公司列表
            $company_ist = $this->companyModel->adminLists();
            return $this->fetch('',compact('company_ist'));
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
            if(!$this->departmentModel->edit()){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }else{
            ##员工信息
            $info = $this->departmentModel->where('id',$id)->field('id, title, c_id')->find();
            if(!$info)
                return $this->renderError('部门数据已删除或不存在');
            $info = $info->getData();
            $companies = [];
            $this->companyModel->getParents($info['c_id'],$companies);
            $info['c_id'] = $companies;
            ##分公司列表
            $company_ist = $this->companyModel->adminLists();
            return $this->fetch('',compact('company_ist','info'));
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
            if(!$this->departmentModel->where('id',$id)->setField('delete_time',time())){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }
    }

}