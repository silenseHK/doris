<?php


namespace app\store\controller\project;


use app\store\controller\Controller;
use app\store\model\project\P_Company;
use app\store\model\project\P_Department;
use app\store\model\project\P_Role;
use app\store\model\project\P_Staff;
use app\store\validate\CompanyValid;

class Company extends Controller
{

    protected $companyModel;

    protected $validate;

    public function __construct
    (
        P_Company $p_company,
        CompanyValid $validate
    )
    {
        parent::__construct();
        $this->companyModel = $p_company;
        $this->validate = $validate;
    }

    public function lists()
    {
        $obj = $this->companyModel;
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
            if(!$this->companyModel->add()){
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
            if(!$this->companyModel->edit()){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }else{
            ##公司信息
            $info = $this->companyModel->where('id',$id)->field('id, title')->find();
            if(!$info)
                return $this->renderError('分公司数据已删除或不存在');
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
            if(!$this->companyModel->where('id',$id)->setField('delete_time',time())){
                return $this->renderError('操作失败');
            }
            return $this->renderSuccess('操作成功');
        }
    }

}